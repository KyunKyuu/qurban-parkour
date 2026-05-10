<?php

namespace Database\Seeders;

use App\Models\Claim;
use App\Models\Community;
use App\Models\InitialVoucher;
use App\Models\Pic;
use App\Models\QurbanSetting;
use App\Models\VoucherBatch;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class QurbanSeeder extends Seeder
{
    private array $categories;

    public function run(): void
    {
        $this->categories = config('qurban.categories', []);

        $this->seedSettings();

        $admin = \App\Models\User::where('role', 'SUPERADMIN')->first()
            ?? \App\Models\User::create([
                'name'     => 'Admin Qurban',
                'email'    => 'admin_qurban@example.com',
                'password' => bcrypt('password'),
                'role'     => 'SUPERADMIN',
            ]);

        $batch = VoucherBatch::firstOrCreate(
            ['name' => 'Batch Qurban 1447H'],
            [
                'created_by_admin_id' => $admin->id,
                'generated_count'     => 500,
            ]
        );

        ['pics' => $pics, 'communities' => $communities] = $this->seedPicsAndCommunities();

        $this->seedClaims($batch, $pics, $communities);

        $this->command->info('Qurban data seeded successfully!');
    }

    private function seedSettings(): void
    {
        $defaults = config('qurban', []);

        QurbanSetting::updateOrCreate(
            ['id' => 1],
            [
                'campaign_name'        => $defaults['campaign_name']        ?? 'Kurban Berdaya 1447 H',
                'campaign_subtitle'    => $defaults['campaign_subtitle']    ?? null,
                'campaign_tagline'     => $defaults['campaign_tagline']     ?? null,
                'claim_open'           => $defaults['claim_open']           ?? true,
                'closing_at'           => $defaults['closing_at']           ?? '2026-06-20 23:59:59',
                'closing_label'        => $defaults['closing_label']        ?? null,
                'default_pic_name'     => $defaults['default_pic_name']     ?? null,
                'default_pic_label'    => $defaults['default_pic_label']    ?? null,
                'default_pic_email'    => $defaults['default_pic_email']    ?? null,
                'bank_account_label'   => $defaults['bank_account_label']   ?? null,
                'certificate_title'    => $defaults['certificate_title']    ?? null,
                'certificate_subtitle' => $defaults['certificate_subtitle'] ?? null,
                'patungan_targets'     => $defaults['patungan_targets']     ?? ['DOMBA', 'SAPI'],
                'categories'           => $defaults['categories']           ?? [],
            ]
        );
    }

    /**
     * Seed 5 PJ:
     *   - Ahmad Bustan (default) → tidak punya komunitas, voucher community_id = null
     *   - 4 PJ lain → masing-masing 2 komunitas, komisi masuk ke komunitas
     *
     * @return array{pics: Pic[], communities: Community[]}
     */
    private function seedPicsAndCommunities(): array
    {
        $picData = [
            // Default PJ – tidak ada komunitas
            [
                'name'  => 'Ahmad Bustan',
                'email' => 'ahmad.bustan@kurban.local',
                'code'  => 'QB-001',
                'communities' => [],
            ],
            [
                'name'  => 'Siti Maryam',
                'email' => 'siti.maryam@kurban.local',
                'code'  => 'QB-002',
                'communities' => [
                    ['name' => 'Komunitas Masjid Al-Ikhlas',   'code' => 'KOM-001'],
                    ['name' => 'Komunitas Mushola Ar-Rahman',  'code' => 'KOM-002'],
                ],
            ],
            [
                'name'  => 'Hasan Arifin',
                'email' => 'hasan.arifin@kurban.local',
                'code'  => 'QB-003',
                'communities' => [
                    ['name' => 'Komunitas RW 05 Sukamaju',      'code' => 'KOM-003'],
                    ['name' => 'Komunitas Karang Taruna Bersatu', 'code' => 'KOM-004'],
                ],
            ],
            [
                'name'  => 'Rizal Mubarok',
                'email' => 'rizal.mubarok@kurban.local',
                'code'  => 'QB-004',
                'communities' => [
                    ['name' => 'Komunitas Majelis Taklim An-Nur', 'code' => 'KOM-005'],
                    ['name' => 'Komunitas RT 12 Harapan Jaya',    'code' => 'KOM-006'],
                ],
            ],
            [
                'name'  => 'Dewi Kartika',
                'email' => 'dewi.kartika@kurban.local',
                'code'  => 'QB-005',
                'communities' => [
                    ['name' => 'Komunitas Pemuda Hijrah',       'code' => 'KOM-007'],
                    ['name' => 'Komunitas Pengajian Al-Hikmah', 'code' => 'KOM-008'],
                ],
            ],
        ];

        $pics        = [];
        $communities = [];

        foreach ($picData as $data) {
            $pic = Pic::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'      => $data['name'],
                    'password'  => bcrypt('password'),
                    'is_active' => true,
                    'code'      => $data['code'],
                ]
            );

            \App\Models\User::firstOrCreate(
                ['email' => $pic->email],
                [
                    'name'     => $pic->name,
                    'password' => $pic->password,
                    'role'     => 'PIC',
                    'pic_id'   => $pic->id,
                ]
            );

            $pics[$data['code']] = $pic;

            foreach ($data['communities'] as $commData) {
                $community = Community::firstOrCreate(
                    ['code' => $commData['code']],
                    [
                        'pic_id'    => $pic->id,
                        'name'      => $commData['name'],
                        'is_active' => true,
                    ]
                );

                $communities[$commData['code']] = $community;
            }
        }

        return ['pics' => $pics, 'communities' => $communities];
    }

    private function seedClaims(VoucherBatch $batch, array $pics, array $communities): void
    {
        /**
         * Patungan commission rule (rebuild.md):
         * - Total kontribusi komunitas >= 51% dari harga target → commission_eligible = true
         * - < 51% → commission_eligible = false, selisih di-subsidi pusat (subsidy_amount)
         * - commission_amount per claim PATUNGAN = 0 (komisi adalah milik komunitas, bukan individu)
         *
         * DOMBA patungan total: 500K+750K+300K+1.2M = 2.75M
         * Threshold 51% dari 3M = 1.53M → ELIGIBLE
         *
         * SAPI patungan total: 2M+1.5M+3M = 6.5M
         * Threshold 51% dari 23M = 11.73M → NOT eligible
         * Subsidi per claim = (23M - 6.5M) / 3 = 5.5M
         */
        $dombaTotalPatungan    = 500000 + 750000 + 300000 + 1200000;
        $dombaTarget           = (float) ($this->categories['DOMBA']['price'] ?? 3000000);
        $dombaPatunganEligible = $dombaTotalPatungan >= ($dombaTarget * 0.51);

        $sapiTotalPatungan    = 2000000 + 1500000 + 3000000;
        $sapiTarget           = (float) ($this->categories['SAPI']['price'] ?? 23000000);
        $sapiPatunganEligible = $sapiTotalPatungan >= ($sapiTarget * 0.51);
        $sapiSubsidiPerClaim  = $sapiPatunganEligible ? 0 : round(($sapiTarget - $sapiTotalPatungan) / 3);

        // Setiap claim diberi: pic_code (PJ pemilik voucher) + community_code (null = PJ default)
        $claimSamples = [
            // --- DOMBA – via PJ default Ahmad Bustan (community_id = null) ---
            ['name' => 'Budi Santoso',    'category' => 'DOMBA', 'pic' => 'QB-001', 'community' => null,    'instagram' => 'budi.santoso',  'commission' => true,  'status' => 'VERIFIED', 'has_cert' => true],
            ['name' => 'Dewi Rahayu',     'category' => 'DOMBA', 'pic' => 'QB-001', 'community' => null,    'instagram' => 'dewi_rahayu',   'commission' => true,  'status' => 'VERIFIED', 'has_cert' => true],

            // --- DOMBA – via komunitas ---
            ['name' => 'Fajar Nugroho',   'category' => 'DOMBA', 'pic' => 'QB-002', 'community' => 'KOM-001', 'instagram' => null,            'commission' => false, 'status' => 'VERIFIED', 'has_cert' => true],
            ['name' => 'Rina Kusuma',     'category' => 'DOMBA', 'pic' => 'QB-002', 'community' => 'KOM-002', 'instagram' => 'rina.kusuma22', 'commission' => true,  'status' => 'PENDING',  'has_cert' => false],
            ['name' => 'Agus Firmansyah', 'category' => 'DOMBA', 'pic' => 'QB-003', 'community' => 'KOM-003', 'instagram' => null,            'commission' => false, 'status' => 'PENDING',  'has_cert' => false],

            // --- SAPI – via komunitas ---
            ['name' => 'Hendra Wijaya',   'category' => 'SAPI',  'pic' => 'QB-003', 'community' => 'KOM-004', 'instagram' => 'hendra_w',      'commission' => true,  'status' => 'VERIFIED', 'has_cert' => true],
            ['name' => 'Yuni Astuti',     'category' => 'SAPI',  'pic' => 'QB-004', 'community' => 'KOM-005', 'instagram' => null,            'commission' => false, 'status' => 'ANOMALY',  'has_cert' => false],

            // --- 1/7 SAPI – via komunitas ---
            ['name' => 'Dian Permata',    'category' => 'SAPI_1_7', 'pic' => 'QB-004', 'community' => 'KOM-006', 'instagram' => 'dian.permata', 'commission' => true,  'status' => 'VERIFIED', 'has_cert' => true],
            ['name' => 'Rizky Maulana',   'category' => 'SAPI_1_7', 'pic' => 'QB-005', 'community' => 'KOM-007', 'instagram' => 'rizky_ml',     'commission' => true,  'status' => 'VERIFIED', 'has_cert' => true],
            ['name' => 'Sari Indah',      'category' => 'SAPI_1_7', 'pic' => 'QB-005', 'community' => 'KOM-008', 'instagram' => null,           'commission' => false, 'status' => 'PENDING',  'has_cert' => false],
            ['name' => 'Wahyu Pratama',   'category' => 'SAPI_1_7', 'pic' => 'QB-002', 'community' => 'KOM-001', 'instagram' => 'wahyu.p',      'commission' => true,  'status' => 'VERIFIED', 'has_cert' => true],
            ['name' => 'Lestari Ningrum', 'category' => 'SAPI_1_7', 'pic' => 'QB-003', 'community' => 'KOM-003', 'instagram' => null,           'commission' => false, 'status' => 'ANOMALY',  'has_cert' => false],

            // --- PATUNGAN → DOMBA (total 2.75M >= 51% dari 3M → ELIGIBLE) – via komunitas ---
            ['name' => 'Irfan Hakim',    'category' => 'PATUNGAN', 'patungan_target' => 'DOMBA', 'contribution' => 500000,  'pic' => 'QB-004', 'community' => 'KOM-005', 'instagram' => 'irfan.h',   'commission' => $dombaPatunganEligible, 'status' => 'VERIFIED', 'has_cert' => true],
            ['name' => 'Nadia Sari',     'category' => 'PATUNGAN', 'patungan_target' => 'DOMBA', 'contribution' => 750000,  'pic' => 'QB-004', 'community' => 'KOM-005', 'instagram' => null,        'commission' => $dombaPatunganEligible, 'status' => 'VERIFIED', 'has_cert' => true],
            ['name' => 'Eko Purwanto',   'category' => 'PATUNGAN', 'patungan_target' => 'DOMBA', 'contribution' => 300000,  'pic' => 'QB-004', 'community' => 'KOM-006', 'instagram' => 'eko.p',     'commission' => $dombaPatunganEligible, 'status' => 'PENDING',  'has_cert' => false],
            ['name' => 'Maya Lestari',   'category' => 'PATUNGAN', 'patungan_target' => 'DOMBA', 'contribution' => 1200000, 'pic' => 'QB-004', 'community' => 'KOM-006', 'instagram' => 'maya.lstr', 'commission' => $dombaPatunganEligible, 'status' => 'PENDING',  'has_cert' => false],

            // --- PATUNGAN → SAPI (total 6.5M < 51% dari 23M → NOT eligible, subsidi pusat) – via komunitas ---
            ['name' => 'Tono Hartono',     'category' => 'PATUNGAN', 'patungan_target' => 'SAPI', 'contribution' => 2000000, 'subsidi' => $sapiSubsidiPerClaim, 'pic' => 'QB-005', 'community' => 'KOM-007', 'instagram' => 'tono.h', 'commission' => $sapiPatunganEligible, 'status' => 'VERIFIED', 'has_cert' => true],
            ['name' => 'Fitri Handayani',  'category' => 'PATUNGAN', 'patungan_target' => 'SAPI', 'contribution' => 1500000, 'subsidi' => $sapiSubsidiPerClaim, 'pic' => 'QB-005', 'community' => 'KOM-008', 'instagram' => null,     'commission' => $sapiPatunganEligible, 'status' => 'PENDING',  'has_cert' => false],
            ['name' => 'Bambang Sutrisno', 'category' => 'PATUNGAN', 'patungan_target' => 'SAPI', 'contribution' => 3000000, 'subsidi' => $sapiSubsidiPerClaim, 'pic' => 'QB-005', 'community' => 'KOM-008', 'instagram' => 'bambs',  'commission' => $sapiPatunganEligible, 'status' => 'PENDING',  'has_cert' => false],
        ];

        $baseDate = Carbon::create(2026, 5, 1);

        foreach ($claimSamples as $i => $sample) {
            $categoryKey  = $sample['category'];
            $categoryConf = $this->categories[$categoryKey] ?? [];
            $pic          = $pics[$sample['pic']];
            $community    = $sample['community'] ? ($communities[$sample['community']] ?? null) : null;

            $unitPrice = (float) ($categoryConf['price'] ?? 0);

            if ($categoryKey === 'PATUNGAN') {
                $contribution   = (float) ($sample['contribution'] ?? 0);
                $patunganTarget = $sample['patungan_target'] ?? 'DOMBA';
                $targetConf     = $this->categories[$patunganTarget] ?? [];
                $unitPrice      = (float) ($targetConf['price'] ?? 0);
                // Komisi PATUNGAN adalah milik komunitas (bukan individu) → per claim = 0
                $commissionAmt  = 0;
            } else {
                $contribution   = $unitPrice;
                $patunganTarget = null;
                $commissionAmt  = ($sample['commission'] ?? false)
                    ? (float) ($categoryConf['commission'] ?? 0)
                    : 0;
            }

            $subsidyAmount = (float) ($sample['subsidi'] ?? 0);
            $claimDate     = $baseDate->copy()->addDays($i)->setTime(rand(8, 20), rand(0, 59));
            $status        = $sample['status'];

            $voucher = InitialVoucher::create([
                'code'            => 'QB-' . strtoupper(Str::random(8)),
                'batch_id'        => $batch->id,
                'assigned_pic_id' => $pic->id,
                'community_id'    => $community?->id,
                'status'          => 'CLAIMED',
                'claimed_at'      => $claimDate,
            ]);

            $certPath  = null;
            $certGenAt = null;
            if ($sample['has_cert'] ?? false) {
                $safeName  = Str::slug($sample['name'], '-');
                $certPath  = 'certificates/sertifikat-apresiasi-' . $safeName . '-' . Str::random(6) . '.pdf';
                $certGenAt = $claimDate->copy()->addMinutes(rand(1, 30));
            }

            Claim::create([
                'initial_voucher_id'       => $voucher->id,
                'pic_id'                   => $pic->id,
                'name'                     => $sample['name'],
                'email'                    => Str::slug($sample['name'], '.') . '@example.com',
                'phone'                    => '0812' . rand(10000000, 99999999),
                'category_type'            => $categoryKey,
                'category_label'           => $categoryConf['label'] ?? $categoryKey,
                'patungan_target'          => $patunganTarget,
                'unit_price_snapshot'      => $unitPrice > 0 ? $unitPrice : null,
                'contribution_amount'      => $contribution > 0 ? $contribution : null,
                'instagram_username'       => $sample['instagram'] ?? null,
                'certificate_path'         => $certPath,
                'certificate_generated_at' => $certGenAt,
                'subsidy_amount'           => $subsidyAmount,
                'payment_method'           => rand(0, 1) ? 'transfer' : 'cash',
                'public_token'             => Str::random(32),
                'verification_status'      => $status,
                'verified_at'              => $status === 'VERIFIED' ? $claimDate->copy()->addHour() : null,
                'created_at'               => $claimDate,
                'updated_at'               => $claimDate,
            ]);
        }
    }
}
