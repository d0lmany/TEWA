<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FavoriteList extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function favoriteListItems(): HasMany
    {
        return $this->hasMany(FavoriteListItem::class, 'list_id');
    }
    public function product()
    {
        return $this->belongsToMany(Product::class, 'favorite_list_items', 'list_id', 'product_id');
    }
}
