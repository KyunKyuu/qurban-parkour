<?php

namespace Tests\Unit;

use App\Models\Claim;
use App\Models\Pic;
use App\Services\QurbanExportService;
use App\Services\QurbanPricingService;
use App\Services\QurbanSettingsService;
use Tests\TestCase;

class QurbanExportServiceTest extends TestCase
{
    public function test_claim_export_row_supports_direct_web_claim_without_voucher(): void
    {
        $settingsService = new class extends QurbanSettingsService
        {
            public function current(): array
            {
                return $this->defaults();
            }
        };

        $claim = new Claim([
            'id' => 7,
            'name' => 'Peserta Web',
            'email' => 'web@example.com',
            'phone' => '08123456789',
            'category_type' => 'DOMBA',
            'category_label' => 'Domba',
            'unit_price_snapshot' => 3000000,
            'contribution_amount' => 3000000,
            'payment_method' => 'cash',
            'public_token' => 'token-web-123',
        ]);
        $claim->setRelation('pic', new Pic(['name' => 'Komunitas Web']));

        $service = new QurbanExportService(new QurbanPricingService($settingsService));
        $row = array_combine($service->claimHeadings(), $service->claimRow($claim));

        $this->assertSame('Direct Web', $row['Source']);
        $this->assertSame('', $row['Contribution Code']);
        $this->assertSame('Komunitas Web', $row['PIC']);
        $this->assertSame('SELARAS', $row['Settings Drift']);
    }

    public function test_claim_export_row_marks_drift_when_runtime_settings_change(): void
    {
        $settingsService = new class extends QurbanSettingsService
        {
            public function current(): array
            {
                $settings = $this->defaults();
                $settings['categories']['DOMBA']['price'] = 3600000;
                $settings['categories']['DOMBA']['commission'] = 400000;
                $settings['bank_account_label'] = 'Rekening Baru';

                return $settings;
            }
        };

        $claim = new Claim([
            'id' => 8,
            'name' => 'Peserta Drift',
            'email' => 'drift@example.com',
            'category_type' => 'DOMBA',
            'category_label' => 'Domba',
            'unit_price_snapshot' => 3000000,
            'contribution_amount' => 3000000,
            'payment_method' => 'transfer',
            'transfer_destination' => 'Rekening Lama',
            'public_token' => 'token-drift-123',
        ]);

        $service = new QurbanExportService(new QurbanPricingService($settingsService));
        $row = array_combine($service->claimHeadings(), $service->claimRow($claim));

        $this->assertSame('BERUBAH', $row['Settings Drift']);
        $this->assertStringContainsString('Basis harga kategori sudah berubah', $row['Drift Notes']);
        $this->assertStringContainsString('Tujuan transfer aktif sudah berubah', $row['Drift Notes']);
        $this->assertSame(3600000.0, $row['Current Unit Price']);
    }
}
