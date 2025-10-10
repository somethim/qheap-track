<?php

namespace App\Models\Orders;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'contact_email',
        'contact_phone',
        'address',
        'user_id',
    ];

    protected $hidden = [
        'user_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('suppliers.user_id', auth()->id());
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Supplier $supplier, int $userId): Builder
    {
        return $supplier->withoutGlobalScope('user')->where('suppliers.user_id', $userId);
    }

    public function scopeAllUsers(Supplier $supplier): Builder
    {
        return $supplier->withoutGlobalScope('user');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
