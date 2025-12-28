<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderLocation extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'order_id',
        'location_type',
        'location_id',
        'notes',
        'arrived_at',
        'left_at',
    ];
    
    protected $casts = [
        'arrived_at' => 'datetime',
        'left_at' => 'datetime',
    ];
    
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    
    public function location()
    {
        return $this->morphTo();
    }
}
