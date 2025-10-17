<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'user_id', 'product_id',
        'quantity', 'product_attributes'
    ];

    protected $casts = [
        'product_attributes' => 'array'
    ];

    public $timestamps = false;

    protected $table = 'cart_items';

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
