<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    protected $appends = [
        'rating',
    ];

    public $timestamps = false;

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function getRatingAttribute()
    {
        $productsWithReviews = Product::where('shop_id', $this->id)
            ->whereHas('reviews')
            ->withAvg('reviews', 'evaluation')
            ->get();
        
        if ($productsWithReviews->isEmpty()) {
            return 0.0;
        }
        
        return (float) $productsWithReviews->avg('reviews_avg_evaluation');
    }
}
