<?php

namespace App\Models\Orders;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'price',
        'stock',
        'user_id',
    ];

    protected $hidden = [
        'user_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });
    }

    public function scopeForUser(Product $product, int $userId): Builder
    {
        return $product->withoutGlobalScope('user')->where('user_id', $userId);
    }

    public function scopeAllUsers(Product $product): Builder
    {
        return $product->withoutGlobalScope('user');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
