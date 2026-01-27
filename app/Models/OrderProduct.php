<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class OrderProduct extends Pivot
{
    protected $table = 'order_products';

    protected $fillable = [
        'order_id',
        'product_id',
        'price',
        'packaging_price',
        'production_price',
        'transportation_price',
        'multi_delivery_price',
        'sell_percent',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
