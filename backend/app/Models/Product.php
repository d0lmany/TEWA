<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'name',
        'quantity',
        'photo',
        'category_id',
        'shop_id',
        'discount',
        'tags',
        'status',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function productDetail(): HasOne
    {
        return $this->hasOne(ProductDetail::class);
    }
}
