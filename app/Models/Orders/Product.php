<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sku',
        'price',
        'stock_quantity',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            $product->sku = Str::upper(Str::random(3)).'-'.Str::random(8);
        });
    }
}
