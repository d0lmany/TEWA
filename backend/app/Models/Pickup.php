<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pickup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'country',
        'city', 'address'
    ];

    public $timestamps = false;

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}
