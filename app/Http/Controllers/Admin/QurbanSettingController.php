<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\QurbanSettingsService;
use Illuminate\Http\Request;

class QurbanSettingController extends Controller
{
    public function __construct(
        protected QurbanSettingsService $settingsService
    ) {
    }

    public function edit()
    {
        $settings = $this->settingsService->formData();
        $categoryKeys = array_keys($settings['categories'] ?? []);
        $patunganTargetOptions = array_values(array_filter(
            $categoryKeys,
            fn (string $key) => $key !== 'PATUNGAN'
        ));

        return view('admin.settings.edit', [
            'settings' => $settings,
            'patunganTargetOptions' => $patunganTargetOptions,
        ]);
    }

    public function update(Request $request)
    {
        $defaults = $this->settingsService->defaults();
        $categoryKeys = array_keys($defaults['categories'] ?? []);
        $patunganTargetOptions = array_values(array_filter(
            $categoryKeys,
            fn (string $key) => $key !== 'PATUNGAN'
        ));

        $validated = $request->validate($this->rules($categoryKeys, $patunganTargetOptions));
        $validated['claim_open'] = $request->boolean('claim_open');

        $this->settingsService->save($validated);

        return redirect()
            ->route('admin.settings.qurban.edit')
            ->with('success', 'Settings kurban berhasil diperbarui. Nilai baru akan dipakai untuk kontribusi berikutnya.');
    }

    protected function rules(array $categoryKeys, array $patunganTargetOptions): array
    {
        $rules = [
            'campaign_name' => 'required|string|max:255',
            'campaign_subtitle' => 'required|string|max:1000',
            'campaign_tagline' => 'required|string|max:1000',
            'closing_at' => 'nullable|date',
            'closing_label' => 'required|string|max:255',
            'default_pic_name' => 'required|string|max:255',
            'default_pic_label' => 'required|string|max:255',
            'default_pic_email' => 'required|email|max:255',
            'bank_account_label' => 'required|string|max:255',
            'certificate_title' => 'required|string|max:255',
            'certificate_subtitle' => 'required|string|max:1000',
            'patungan_targets' => 'required|array|min:1',
        ];

        if (!empty($patunganTargetOptions)) {
            $rules['patungan_targets.*'] = 'in:' . implode(',', $patunganTargetOptions);
        }

        foreach ($categoryKeys as $key) {
            $rules["categories.{$key}.label"] = 'required|string|max:100';
            $rules["categories.{$key}.description"] = 'required|string|max:500';
            $rules["categories.{$key}.price"] = 'required|numeric|min:0';
            $rules["categories.{$key}.commission"] = 'required|numeric|min:0';
        }

        return $rules;
    }
}
