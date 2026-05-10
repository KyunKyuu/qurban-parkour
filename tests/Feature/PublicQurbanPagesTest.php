<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\Pic;
use App\Services\ClaimService;
use App\Services\QurbanPricingService;
use Tests\TestCase;

class PublicQurbanPagesTest extends TestCase
{
    public function test_landing_page_renders_qurban_copy(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Qurban lebih terarah', false);
    }

    public function test_public_contribution_form_is_available(): void
    {
        $response = $this->get('/kurban');

        $response->assertStatus(200);
        $response->assertSee('Form Partisipasi', false);
        $response->assertSee('Pilih Kategori Kurban', false);
    }

    public function test_claim_closed_page_uses_qurban_copy(): void
    {
        $response = $this->get('/claim-closed');

        $response->assertStatus(200);
        $response->assertSee('Periode kontribusi qurban telah berakhir.', false);
    }

    public function test_public_certificate_page_shows_snapshot_audit_context(): void
    {
        $claim = new Claim([
            'name' => 'Peserta Audit',
            'email' => 'audit@example.com',
            'category_type' => 'DOMBA',
            'category_label' => 'Domba',
            'unit_price_snapshot' => 3000000,
            'contribution_amount' => 3000000,
            'public_token' => 'token-audit',
            'initial_voucher_id' => null,
            'certificate_generated_at' => now(),
        ]);
        $claim->setRelation('pic', new Pic(['name' => 'Komunitas Web']));

        $this->app->instance(ClaimService::class, new class($claim) extends ClaimService
        {
            public function __construct(protected Claim $claim)
            {
            }

            public function getClaimByToken(string $token): ?Claim
            {
                return $token === 'token-audit' ? $this->claim : null;
            }
        });

        $this->app->instance(QurbanPricingService::class, new class extends QurbanPricingService
        {
            public function auditClaim(Claim $claim): array
            {
                return [
                    'has_changes' => true,
                    'changes' => [
                        'Basis harga kategori sudah berubah',
                        'Tujuan transfer aktif sudah berubah',
                    ],
                    'snapshot' => [
                        'category_label' => 'Domba',
                        'pricing_basis_label' => 'Domba',
                        'unit_price' => 3000000,
                        'contribution_amount' => 3000000,
                        'transfer_destination' => 'Rekening Lama',
                    ],
                    'current' => [
                        'campaign_name' => 'Kurban 1447 H',
                        'claim_open' => false,
                        'pricing_basis_label' => 'Domba',
                        'unit_price' => 3600000,
                        'closing_label' => 'Sabtu, 20 Juni 2026 / 13 Dzulhijah 1447 H',
                        'transfer_destination' => 'Rekening Baru',
                    ],
                ];
            }
        });

        $response = $this->get('/sertifikat/token-audit');

        $response->assertStatus(200);
        $response->assertSee('Snapshot transaksi Anda tetap disimpan sebagai acuan histori.', false);
        $response->assertSee('Setting kampanye aktif sekarang sudah berubah dari snapshot transaksi Anda.', false);
        $response->assertSee('Direct web', false);
    }
}
