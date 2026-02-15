<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderProductComponent extends Model
{
    protected $fillable = [
        'order_product_id',
        'component_id',
        'grams',
        'price_per_kg',
    ];

    public function orderProduct()
    {
        return $this->belongsTo(OrderProduct::class);
    }

    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}

