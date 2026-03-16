<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Claim extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'initial_voucher_id',
        'pic_id', // Added to support manual schema change
        'name',
        'email',
        'phone',
        'zakat_fitrah_amount',
        'zakat_mal_amount',
        'infaq_amount',
        'sodaqoh_amount',
        'payment_method',
        'transfer_destination',
        'transfer_proof_path',
        'public_token',
        'verification_status',
        'verification_note',
        'verified_at',
    ];

    protected $casts = [
        'zakat_fitrah_amount' => 'decimal:2',
        'zakat_mal_amount' => 'decimal:2',
        'infaq_amount' => 'decimal:2',
        'sodaqoh_amount' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the total donation amount.
     */
    public function getTotalDonationAmountAttribute(): float
    {
        return $this->zakat_fitrah_amount + $this->zakat_mal_amount + $this->infaq_amount + $this->sodaqoh_amount;
    }

    /**
     * Get the initial voucher this claim is for.
     */
    public function initialVoucher(): BelongsTo
    {
        return $this->belongsTo(InitialVoucher::class);
    }

    /**
     * Get the merchant vouchers for this claim.
     */
    public function merchantVouchers(): HasManyThrough
    {
        return $this->hasManyThrough(
            MerchantVoucher::class,
            InitialVoucher::class,
            'id', // Foreign key on initial_vouchers table
            'initial_voucher_id', // Foreign key on merchant_vouchers table
            'initial_voucher_id', // Local key on claims table
            'id' // Local key on initial_vouchers table
        );
    }
}
