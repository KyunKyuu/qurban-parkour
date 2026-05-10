<?php

return [
    'campaign_name' => env('QURBAN_CAMPAIGN_NAME', 'Kurban Berdaya 1447 H'),
    'campaign_subtitle' => env(
        'QURBAN_CAMPAIGN_SUBTITLE',
        'Penyaluran hewan kurban, pembelian hewan kurban, dan patungan komunitas dengan sertifikat apresiasi digital.'
    ),
    'campaign_tagline' => env(
        'QURBAN_CAMPAIGN_TAGLINE',
        'Soft selling ala lembaga sosial, dengan dashboard yang fokus pada kontribusi, sertifikat, dan komisi komunitas.'
    ),
    'claim_open' => env('QURBAN_CLAIM_OPEN', true),
    'closing_at' => env('QURBAN_CLOSING_AT', '2026-06-20 23:59:59'),
    'closing_label' => env('QURBAN_CLOSING_LABEL', 'Sabtu, 20 Juni 2026 / 13 Dzulhijah 1447 H'),
    'default_pic_name' => env('QURBAN_DEFAULT_PIC_NAME', 'Ahmad Bustan'),
    'default_pic_label' => env('QURBAN_DEFAULT_PIC_LABEL', 'Ahmad Bustan'),
    'default_pic_email' => env('QURBAN_DEFAULT_PIC_EMAIL', 'ahmad.bustan@kurban.local'),
    'bank_account_label' => env(
        'QURBAN_BANK_ACCOUNT_LABEL',
        'Blu 090109627811 a.n Ahmad Bustan Djatmadipura'
    ),
    'certificate_title' => env('QURBAN_CERTIFICATE_TITLE', 'Sertifikat Apresiasi Qurban'),
    'certificate_subtitle' => env(
        'QURBAN_CERTIFICATE_SUBTITLE',
        'Sebagai penghargaan atas ikhtiar dan partisipasi Anda dalam program qurban tahun ini.'
    ),
    'patungan_targets' => ['DOMBA', 'SAPI'],
    'categories' => [
        'DOMBA' => [
            'label' => 'Domba',
            'description' => 'Pembelian 1 domba dengan harga tetap dari admin.',
            'price' => 3000000,
            'commission' => 300000,
        ],
        'SAPI' => [
            'label' => 'Sapi',
            'description' => 'Pembelian 1 sapi utuh dengan harga tetap dari admin.',
            'price' => 23000000,
            'commission' => 1500000,
        ],
        'SAPI_1_7' => [
            'label' => '1/7 Sapi',
            'description' => 'Pembelian jatah 1/7 sapi dengan harga tetap.',
            'price' => 3285714,
            'commission' => 214286,
        ],
        'PATUNGAN' => [
            'label' => 'Patungan Seikhlasnya',
            'description' => 'Nominal bebas untuk target domba atau sapi.',
            'price' => 0,
            'commission' => 0,
        ],
    ],
];
