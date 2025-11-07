<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Claim extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'entity',
        'entity_id', 'topic',
        'text'   
    ];

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeAnonymous($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('entity', 'product')
                    ->where('entity_id', (string) $productId);
    }

    public function scopeUrgentTopics($query)
    {
        return $query->whereIn('topic', [
            'Не доставлен товар',
            'Поврежденный товар',
            'Обман с ценой'
        ]);
    }
}
