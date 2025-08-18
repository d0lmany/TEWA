<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
