<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    protected $appends = [
        'rating',
        'reviews_count'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function productDetail(): HasOne
    {
        return $this->hasOne(ProductDetail::class);
    }

    public function productAttribute(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function getRatingAttribute(): float
    {
        return (float) $this->reviews()->avg('evaluation') ?? 0.0;
    }

        public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    public function scopeWithMinRating($query, $rating)
    {
        return $query->whereHas('reviews', function($q) use ($rating) {
            $q->selectRaw('AVG(evaluation) as avg_rating')
            ->having('avg_rating', '>=', $rating);
        });
    }

    public function scopeOrderByRating($query, $direction = 'desc')
    {
        return $query->withAvg('reviews as rating_avg', 'evaluation')
                ->orderBy('rating_avg', $direction);
    }
}
