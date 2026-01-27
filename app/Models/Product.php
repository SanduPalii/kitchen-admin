<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_fi',
        'name_ee',
        'name_en',
        'type',
    ];

    public function components()
    {
        return $this->belongsToMany(
            Component::class,
            'product_components'
        )->withPivot('quantity');
    }

    public function orders()
    {
        return $this->belongsToMany(
            Order::class,
            'order_products'
        )->withPivot([
            'price',
            'packaging_price',
            'production_price',
            'transportation_price',
            'multi_delivery_price',
            'sell_percent'
        ]);
    }
}
