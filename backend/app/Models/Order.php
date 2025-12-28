<?php

namespace App\Models;

use App\Models\Address;
use App\Models\OrderItem;
use App\Models\OrderLocation;
use App\Models\OrderStatusHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total',
        'destination_pickup_id',
        'destination_address_id',
        'is_hidden',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'is_hidden' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function pickup(): BelongsTo
    {
        return $this->belongsTo(Pickup::class, 'destination_pickup_id');
    }
    
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'destination_address_id');
    }
    
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    
    public function locations(): HasMany
    {
        return $this->hasMany(OrderLocation::class);
    }
    
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }
    
    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }
    
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
