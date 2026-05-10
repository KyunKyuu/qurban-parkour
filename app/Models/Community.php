<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Community extends Model
{
    protected $fillable = [
        'pic_id',
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function pic(): BelongsTo
    {
        return $this->belongsTo(Pic::class);
    }

    public function initialVouchers(): HasMany
    {
        return $this->hasMany(InitialVoucher::class);
    }

    public function claims(): HasManyThrough
    {
        return $this->hasManyThrough(Claim::class, InitialVoucher::class);
    }
}
