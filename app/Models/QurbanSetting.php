<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QurbanSetting extends Model
{
    protected $fillable = [
        'campaign_name',
        'campaign_subtitle',
        'campaign_tagline',
        'claim_open',
        'closing_at',
        'closing_label',
        'default_pic_name',
        'default_pic_label',
        'default_pic_email',
        'bank_account_label',
        'certificate_title',
        'certificate_subtitle',
        'patungan_targets',
        'categories',
    ];

    protected $casts = [
        'claim_open' => 'boolean',
        'closing_at' => 'datetime',
        'patungan_targets' => 'array',
        'categories' => 'array',
    ];
}
