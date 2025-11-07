<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seller extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'full_name', 'user_id',
        'code', 'type',
        'payment_account', 'verified_at'
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }
}
