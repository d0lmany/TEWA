<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'birthday',
    ];

    public $timestamps = false;

    protected $casts = [
        'password' => 'hashed',
        'is_banned' => 'boolean',
    ];

    protected $attributes = [
        'role' => 'user',
        'is_banned' => 0,
    ];

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function seller(): HasOne
    {
        return $this->hasOne(Seller::class);
    }

    public function sellerVerification(): HasOne
    {
        return $this->hasOne(SellerVerification::class);
    }

    public function cart(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function favoriteList(): HasMany
    {
        return $this->hasMany(FavoriteList::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}
