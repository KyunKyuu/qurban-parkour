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
        'pic_type',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function isKasie(): bool
    {
        return $this->pic_type !== 'komunitas';
    }

    public function isKomunitas(): bool
    {
        return $this->pic_type === 'komunitas';
    }

    public function initialVouchers(): HasMany
    {
        return $this->hasMany(InitialVoucher::class, 'assigned_pic_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    /** Communities owned by this PIC Kasie (pic_id FK) */
    public function communities(): HasMany
    {
        return $this->hasMany(Community::class, 'pic_id');
    }

    /** The single community this PIC Komunitas manages (pic_komunitas_id FK) */
    public function communityAsPicKomunitas(): HasOne
    {
        return $this->hasOne(Community::class, 'pic_komunitas_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'pic_id');
    }
}
