<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Shop extends Model
{
    use HasFactory;
    protected $appends = [
        'name', 'picture',
        'description', 'seller_id'
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function reviews(): HasManyThrough
    {
        return $this->HasManyThrough(Review::class, Product::class);
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->count();
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
