<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'product_attributes',
        'unit_price',
        'total',
    ];
    
    protected $casts = [
        'product_attributes' => 'array',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    public function calculateTotal(): float
    {
        $attributesPrice = 0;
        
        if (!empty($this->product_attributes)) {
            $attributes = ProductAttribute::whereIn('id', $this->product_attributes)
                ->where('product_id', $this->product_id)
                ->get();
            
            $attributesPrice = $attributes->sum('price');
        }
        
        return ($this->unit_price + $attributesPrice) * $this->quantity;
    }
}
