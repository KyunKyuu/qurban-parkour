<?php

namespace App\Services;

use App\Models\QurbanSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class QurbanSettingsService
{
    protected bool $settingsLoaded = false;

    protected ?QurbanSetting $storedSettings = null;

    protected ?array $resolvedSettings = null;

    public function defaults(): array
    {
        return require config_path('qurban.php');
    }

    public function current(): array
    {
        if ($this->resolvedSettings !== null) {
            return $this->resolvedSettings;
        }

        $defaults = $this->defaults();
        $stored = $this->stored();

        if (!$stored) {
            return $this->resolvedSettings = $defaults;
        }

        $settings = array_filter([
            'campaign_name' => $stored->campaign_name,
            'campaign_subtitle' => $stored->campaign_subtitle,
            'campaign_tagline' => $stored->campaign_tagline,
            'claim_open' => $stored->claim_open,
            'closing_at' => optional($stored->closing_at)->format('Y-m-d H:i:s'),
            'closing_label' => $stored->closing_label,
            'default_pic_name' => $stored->default_pic_name,
            'default_pic_label' => $stored->default_pic_label,
            'default_pic_email' => $stored->default_pic_email,
            'bank_account_label' => $stored->bank_account_label,
            'certificate_title' => $stored->certificate_title,
            'certificate_subtitle' => $stored->certificate_subtitle,
        ], fn ($value) => $value !== null);

        $settings += [
            'patungan_targets' => $stored->patungan_targets ?: ($defaults['patungan_targets'] ?? []),
            'categories' => $this->mergeCategories(
                $defaults['categories'] ?? [],
                $stored->categories ?? []
            ),
        ];

        return $this->resolvedSettings = array_replace_recursive($defaults, $settings);
    }

    public function categories(): array
    {
        return $this->current()['categories'] ?? [];
    }

    public function patunganTargets(): array
    {
        return $this->current()['patungan_targets'] ?? [];
    }

    public function apply(): void
    {
        Config::set('qurban', $this->current());
    }

    public function formData(): array
    {
        $current = $this->current();

        return [
            ...$current,
            'closing_at' => !empty($current['closing_at'])
                ? Carbon::parse($current['closing_at'])->format('Y-m-d\TH:i')
                : null,
        ];
    }

    public function save(array $payload): QurbanSetting
    {
        $defaults = $this->defaults();
        $settings = $this->stored() ?? new QurbanSetting();

        $settings->fill([
            'campaign_name' => $payload['campaign_name'],
            'campaign_subtitle' => $payload['campaign_subtitle'],
            'campaign_tagline' => $payload['campaign_tagline'],
            'claim_open' => (bool) ($payload['claim_open'] ?? false),
            'closing_at' => $payload['closing_at'],
            'closing_label' => $payload['closing_label'],
            'default_pic_name' => $payload['default_pic_name'],
            'default_pic_label' => $payload['default_pic_label'],
            'default_pic_email' => $payload['default_pic_email'],
            'bank_account_label' => $payload['bank_account_label'],
            'certificate_title' => $payload['certificate_title'],
            'certificate_subtitle' => $payload['certificate_subtitle'],
            'patungan_targets' => array_values($payload['patungan_targets'] ?? ($defaults['patungan_targets'] ?? [])),
            'categories' => $this->normalizeCategories($payload['categories'] ?? [], $defaults['categories'] ?? []),
        ]);

        $settings->save();

        $this->settingsLoaded = false;
        $this->storedSettings = null;
        $this->resolvedSettings = null;
        $this->apply();

        return $settings;
    }

    protected function stored(): ?QurbanSetting
    {
        if ($this->settingsLoaded) {
            return $this->storedSettings;
        }

        $this->settingsLoaded = true;

        if (!$this->isTableAvailable()) {
            return $this->storedSettings = null;
        }

        return $this->storedSettings = QurbanSetting::query()->find(1) ?? QurbanSetting::query()->first();
    }

    protected function isTableAvailable(): bool
    {
        try {
            return Schema::hasTable('qurban_settings');
        } catch (\Throwable) {
            return false;
        }
    }

    protected function mergeCategories(array $defaults, array $stored): array
    {
        $categories = [];

        foreach ($defaults as $key => $defaultCategory) {
            $categories[$key] = array_replace($defaultCategory, $stored[$key] ?? []);
        }

        return $categories;
    }

    protected function normalizeCategories(array $categories, array $defaults): array
    {
        $normalized = [];

        foreach ($defaults as $key => $defaultCategory) {
            $input = $categories[$key] ?? [];

            $normalized[$key] = [
                'label' => (string) ($input['label'] ?? $defaultCategory['label']),
                'description' => (string) ($input['description'] ?? $defaultCategory['description']),
                'price' => (float) ($input['price'] ?? $defaultCategory['price']),
                'commission' => (float) ($input['commission'] ?? $defaultCategory['commission']),
            ];
        }

        return $normalized;
    }
}
