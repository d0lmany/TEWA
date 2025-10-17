<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoriteListItem extends Model
{
    protected $fillable = [
        'list_id',
        'product_id',
        'added_at'
    ];

    public $timestamps = false;

    protected $table = 'favorite_list_items';

    protected $casts = [
        'added_at' => 'datetime'
    ];

    public function favoriteList()
    {
        return $this->belongsTo(FavoriteList::class, 'list_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
