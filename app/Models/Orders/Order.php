<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'supplier_id',
        'order_number',
    ];

    protected $appends = [
        'total_amount',
        'item_count',
    ];

    public static function clientOrders(): Builder
    {
        return self::query()->whereNotNull('client_id')->orderByDesc('created_at');
    }

    public static function supplierOrders(): Builder
    {
        return self::query()->whereNotNull('supplier_id')->orderByDesc('created_at');
    }

    protected static function booted(): void
    {
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

    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(
            Product::class,
            OrderProduct::class,
            'order_id',
            'id',
            'id',
            'product_id'
        );
    }

    protected function totalAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->orderProducts->sum(fn ($op) => $op->quantity * $op->price)
        );
    }

    protected function itemCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->orderProducts->sum('quantity')
        );
    }
}
