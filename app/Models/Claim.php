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
        'category_type',
        'category_label',
        'patungan_target',
        'unit_price_snapshot',
        'contribution_amount',
        'instagram_username',
        'certificate_path',
        'certificate_generated_at',
        'subsidy_amount',
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
        'unit_price_snapshot' => 'decimal:2',
        'contribution_amount' => 'decimal:2',
        'subsidy_amount' => 'decimal:2',
        'certificate_generated_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the total donation amount.
     */
    public function getTotalDonationAmountAttribute(): float
    {
        $newAmount = (float) ($this->contribution_amount ?? 0);
        if ($newAmount > 0) {
            return $newAmount;
        }

        return (float) $this->zakat_fitrah_amount
            + (float) $this->zakat_mal_amount
            + (float) $this->infaq_amount
            + (float) $this->sodaqoh_amount;
    }

    public function getDisplayCategoryLabelAttribute(): string
    {
        if ($this->category_label) {
            return $this->category_label;
        }

        return 'Legacy Contribution';
    }

    public function getPatunganProgressPercentAttribute(): int
    {
        if ($this->category_type !== 'PATUNGAN' || !$this->unit_price_snapshot) {
            return 100;
        }

        return min(100, (int) round(($this->total_donation_amount / (float) $this->unit_price_snapshot) * 100));
    }

    public function getCertificateFilenameAttribute(): string
    {
        $safeName = trim(preg_replace('/[^A-Za-z0-9\-]+/', '-', $this->name ?? 'peserta'), '-');

        return 'sertifikat-apresiasi-' . ($safeName ?: 'peserta') . '.png';
    }

    /**
     * Get the initial voucher this claim is for.
     */
    public function initialVoucher(): BelongsTo
    {
        return $this->belongsTo(InitialVoucher::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(Pic::class);
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
