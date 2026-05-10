<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'email',
        'password',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the initial vouchers assigned to this PIC.
     */
    public function initialVouchers(): HasMany
    {
        return $this->hasMany(InitialVoucher::class, 'assigned_pic_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    public function communities(): HasMany
    {
        return $this->hasMany(Community::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'pic_id');
    }
}
