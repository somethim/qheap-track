<?php

namespace App\Models\Orders;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'supplier_id',
        'order_number',
        'user_id',
    ];

    protected $hidden = [
        'user_id',
    ];

    protected $appends = [
        'cost',
        'stock',
    ];

    public static function clientOrders(): Builder
    {
        return self::query()->whereNotNull('client_id');
    }

    public static function supplierOrders(): Builder
    {
        return self::query()->whereNotNull('supplier_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });

        static::creating(static function (Order $order) {
            if ($order->isDirty('client_id') && $order->isDirty('supplier_id')) {
                dump($order->client_id, $order->supplier_id);
                throw new InvalidArgumentException('An order cannot have both a client and a supplier.');
            }

            if ($order->isDirty('client_id') && is_null($order->client_id)) {
                throw new InvalidArgumentException('Client ID cannot be null when creating an order for a client.');
            }

            if ($order->isDirty('supplier_id') && is_null($order->supplier_id)) {
                throw new InvalidArgumentException('Supplier ID cannot be null when creating an order for a supplier.');
            }

            if (is_null($order->client_id) && is_null($order->supplier_id)) {
                throw new InvalidArgumentException('An order must have either a client or a supplier.');
            }

            $order->order_number = Str::uuid()->toString();
        });
    }

    public function scopeForUser(Order $order, int $userId): Builder
    {
        return $order->withoutGlobalScope('user')->where('user_id', $userId);
    }

    public function scopeAllUsers(Order $query): Builder
    {
        return $query->withoutGlobalScope('user');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function scopeOrderByTotalAmount(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->select('orders.*')
            ->addSelect([
                'cost_calc' => OrderProduct::query()
                    ->selectRaw('COALESCE(SUM(stock * price), 0)')
                    ->whereColumn('order_id', 'orders.id'),
            ])
            ->orderBy('cost_calc', $direction);
    }

    public function scopeOrderByItemCount(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->select('orders.*')
            ->addSelect([
                'stock_calc' => OrderProduct::query()
                    ->selectRaw('COALESCE(SUM(stock), 0)')
                    ->whereColumn('order_id', 'orders.id'),
            ])
            ->orderBy('stock_calc', $direction);
    }

    protected function cost(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->orderProducts->sum(fn ($op) => $op->stock * $op->price)
        );
    }

    protected function stock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->orderProducts->sum('stock')
        );
    }
}
