<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class OrderProduct extends Pivot
{
    protected $table = 'order_products';

    public $incrementing = true;

    protected $fillable = [
        'order_id',
        'product_id',
        'price',
        'portion_grams',
        'units_per_box',
    ];


    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function components()
    {
        return $this->hasMany(OrderProductComponent::class, 'order_product_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
