<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InitialVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'code',
        'status',
        'assigned_pic_id',
        'community_id',
        'claimed_at',
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
    ];

    /**
     * Get the batch this voucher belongs to.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(VoucherBatch::class, 'batch_id');
    }

    /**
     * Get the PIC this voucher is assigned to.
     */
    public function pic(): BelongsTo
    {
        return $this->belongsTo(Pic::class, 'assigned_pic_id');
    }

    /**
     * Get the community this voucher belongs to.
     * Null = voucher dari PJ default (langsung via WA, tanpa komunitas).
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * Get the claim for this voucher.
     */
    public function claim(): HasOne
    {
        return $this->hasOne(Claim::class);
    }

    /**
     * Get the merchant vouchers generated from this initial voucher.
     */
    public function merchantVouchers(): HasMany
    {
        return $this->hasMany(MerchantVoucher::class);
    }

    /**
     * Scope to get only assigned vouchers.
     */
    public function scopeAssigned($query)
    {
        return $query->where('status', 'ASSIGNED');
    }

    /**
     * Scope to get only unassigned vouchers.
     */
    public function scopeUnassigned($query)
    {
        return $query->where('status', 'UNASSIGNED');
    }

    /**
     * Scope to get only claimed vouchers.
     */
    public function scopeClaimed($query)
    {
        return $query->where('status', 'CLAIMED');
    }
}
