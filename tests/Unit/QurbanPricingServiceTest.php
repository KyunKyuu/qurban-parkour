<?php

namespace Tests\Unit;

use App\Models\Claim;
use App\Services\QurbanSettingsService;
use App\Services\QurbanPricingService;
use Tests\TestCase;

class QurbanPricingServiceTest extends TestCase
{
    public function test_fixed_category_uses_configured_price(): void
    {
        $service = new QurbanPricingService();

        $selection = $service->resolveSelection('DOMBA');

        $this->assertSame('DOMBA', $selection['category_type']);
        $this->assertEquals(3000000, $selection['contribution_amount']);
        $this->assertArrayNotHasKey('commission_eligible', $selection);
        $this->assertArrayNotHasKey('commission_amount', $selection);
    }

    public function test_patungan_resolves_progress_and_subsidy(): void
    {
        $service = new QurbanPricingService();

        $eligibleSelection = $service->resolveSelection('PATUNGAN', 1600000, 'DOMBA');
        $ineligibleSelection = $service->resolveSelection('PATUNGAN', 1000000, 'DOMBA');

        $this->assertEquals(53, $eligibleSelection['progress_percent']);
        $this->assertEquals(1400000, $eligibleSelection['subsidy_amount']);

        $this->assertEquals(33, $ineligibleSelection['progress_percent']);
        $this->assertEquals(2000000, $ineligibleSelection['subsidy_amount']);
    }

    public function test_pricing_service_can_use_database_backed_settings_source(): void
    {
        $settingsService = new class extends QurbanSettingsService
        {
            public function current(): array
            {
                $settings = $this->defaults();
                $settings['categories']['DOMBA']['price'] = 3500000;

                return $settings;
            }
        };

        $service = new QurbanPricingService($settingsService);
        $selection = $service->resolveSelection('DOMBA');

        $this->assertEquals(3500000, $selection['contribution_amount']);
    }

    public function test_claim_audit_reports_difference_when_active_price_changes(): void
    {
        $settingsService = new class extends QurbanSettingsService
        {
            public function current(): array
            {
                $settings = $this->defaults();
                $settings['categories']['DOMBA']['price'] = 3600000;
                $settings['bank_account_label'] = 'Rekening Baru';

                return $settings;
            }
        };

        $claim = new Claim([
            'name' => 'Peserta Uji',
            'category_type' => 'DOMBA',
            'category_label' => 'Domba',
            'unit_price_snapshot' => 3000000,
            'contribution_amount' => 3000000,
            'payment_method' => 'transfer',
            'transfer_destination' => 'Rekening Lama',
        ]);

        $service = new QurbanPricingService($settingsService);
        $audit = $service->auditClaim($claim);

        $this->assertTrue($audit['has_changes']);
        $this->assertEquals(3000000, $audit['snapshot']['unit_price']);
        $this->assertEquals(3600000, $audit['current']['unit_price']);
        $this->assertContains('Basis harga kategori sudah berubah', $audit['changes']);
        $this->assertContains('Tujuan transfer aktif sudah berubah', $audit['changes']);
    }
}
