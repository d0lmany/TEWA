<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'product_id',
        'quantity', 'product_attributes'
    ];

    protected $casts = [
        'product_attributes' => 'array'
    ];

    protected $attributes = [
        'product_attributes' => '[]',
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
