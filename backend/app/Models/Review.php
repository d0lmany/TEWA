<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'product_id',
        'text', 'evaluation'
    ];

    protected $casts = [
        'evaluation' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopePositive($query)
    {
        return $query->where('evaluation', '>=', 4);
    }

    public function scopeNegative($query)
    {
        return $query->where('evaluation', '<=', 2);
    }

    public function scopeWithText($query)
    {
        return $query->whereNotNull('text');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
