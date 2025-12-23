<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'pickup-id',
        'address', 'is_default'
    ];

    public $timestamps = false;

    protected $casts = [
        'is_default' => 'boolean',
    ];

    protected $attributes = [
        'pickup_id' => 1
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pickup(): BelongsTo
    {
        return $this->belongsTo(Pickup::class);
    }
}
