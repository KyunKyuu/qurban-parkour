<?php

namespace App\Services;

use App\Models\Claim;
use Illuminate\Validation\ValidationException;

class QurbanPricingService
{
    public function __construct(
        protected ?QurbanSettingsService $settingsService = null
    ) {
    }

    public function categories(): array
    {
        return $this->settings()['categories'] ?? [];
    }

    public function patunganTargets(): array
    {
        return $this->settings()['patungan_targets'] ?? [];
    }

    public function options(): array
    {
        return array_map(function (array $category, string $key) {
            return [
                'key' => $key,
                ...$category,
            ];
        }, $this->categories(), array_keys($this->categories()));
    }

    public function getCategory(string $categoryType): array
    {
        $categories = $this->categories();

        if (!isset($categories[$categoryType])) {
            throw ValidationException::withMessages([
                'category_type' => 'Kategori kurban tidak dikenali.',
            ]);
        }

        return $categories[$categoryType];
    }

    public function resolveSelection(string $categoryType, ?float $customAmount = null, ?string $patunganTarget = null): array
    {
        $category = $this->getCategory($categoryType);

        if ($categoryType !== 'PATUNGAN') {
            $amount = (float) $category['price'];

            return [
                'category_type' => $categoryType,
                'category_label' => $category['label'],
                'unit_price_snapshot' => (float) $category['price'],
                'contribution_amount' => $amount,
                'patungan_target' => null,
                'subsidy_amount' => 0,
                'progress_percent' => 100,
            ];
        }

        if ($customAmount === null || $customAmount <= 0) {
            throw ValidationException::withMessages([
                'contribution_amount' => 'Nominal patungan wajib diisi dan harus lebih besar dari nol.',
            ]);
        }

        $allowedTargets = $this->patunganTargets();
        if (!$patunganTarget || !in_array($patunganTarget, $allowedTargets, true)) {
            throw ValidationException::withMessages([
                'patungan_target' => 'Target patungan harus dipilih antara domba atau sapi.',
            ]);
        }

        $targetCategory = $this->getCategory($patunganTarget);
        $targetPrice = (float) $targetCategory['price'];
        $progress = $targetPrice > 0 ? min(100, (int) round(($customAmount / $targetPrice) * 100)) : 0;
        return [
            'category_type' => $categoryType,
            'category_label' => $category['label'],
            'unit_price_snapshot' => $targetPrice,
            'contribution_amount' => $customAmount,
            'patungan_target' => $patunganTarget,
            'subsidy_amount' => max(0, $targetPrice - $customAmount),
            'progress_percent' => $progress,
        ];
    }

    public function auditClaim(Claim $claim): array
    {
        $category = $this->findCategory($claim->category_type);
        $pricingBasisCategory = $claim->category_type === 'PATUNGAN'
            ? $this->findCategory($claim->patungan_target)
            : $category;

        $snapshotUnitPrice = (float) ($claim->unit_price_snapshot ?? 0);
        $currentUnitPrice = (float) ($pricingBasisCategory['price'] ?? 0);
        $currentTransferDestination = (string) config('qurban.bank_account_label');

        $changes = [];

        if (($category['label'] ?? $claim->display_category_label) !== $claim->display_category_label) {
            $changes[] = 'Label kategori aktif sudah berubah';
        }

        if (round($snapshotUnitPrice, 2) !== round($currentUnitPrice, 2)) {
            $changes[] = 'Basis harga kategori sudah berubah';
        }

        if (
            ($claim->payment_method ?? 'cash') === 'transfer' &&
            !empty($claim->transfer_destination) &&
            $claim->transfer_destination !== $currentTransferDestination
        ) {
            $changes[] = 'Tujuan transfer aktif sudah berubah';
        }

        return [
            'has_changes' => !empty($changes),
            'changes' => $changes,
            'snapshot' => [
                'category_label' => $claim->display_category_label,
                'pricing_basis_label' => $claim->category_type === 'PATUNGAN'
                    ? 'Target ' . ($pricingBasisCategory['label'] ?? $claim->patungan_target ?? '-')
                    : $claim->display_category_label,
                'unit_price' => $snapshotUnitPrice,
                'contribution_amount' => $claim->total_donation_amount,
                'transfer_destination' => $claim->transfer_destination,
            ],
            'current' => [
                'category_label' => $category['label'] ?? $claim->display_category_label,
                'pricing_basis_label' => $claim->category_type === 'PATUNGAN'
                    ? 'Target ' . ($pricingBasisCategory['label'] ?? $claim->patungan_target ?? '-')
                    : ($category['label'] ?? $claim->display_category_label),
                'unit_price' => $currentUnitPrice,
                'transfer_destination' => $currentTransferDestination,
                'claim_open' => (bool) config('app.claim_open', true) && (bool) config('qurban.claim_open', true),
                'closing_label' => (string) config('qurban.closing_label'),
                'campaign_name' => (string) config('qurban.campaign_name'),
            ],
        ];
    }

    protected function settings(): array
    {
        return $this->settingsService?->current()
            ?? app(QurbanSettingsService::class)->current();
    }

    protected function findCategory(?string $categoryType): ?array
    {
        if (!$categoryType) {
            return null;
        }

        $categories = $this->categories();

        return $categories[$categoryType] ?? null;
    }
}
