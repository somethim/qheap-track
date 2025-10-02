<?php

namespace App\Models\Orders;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
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
                $builder->where('user_id', auth()->id());
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Client $client, int $userId): Builder
    {
        return $client->withoutGlobalScope('user')->where('user_id', $userId);
    }

    public function scopeAllUsers(Client $client): Builder
    {
        return $client->withoutGlobalScope('user');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
