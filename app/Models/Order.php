<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'location_id',
        'user_id',
        'price',
        'size',
        'approved',
        'date',
        'commission_pct',
        'packaging_material',
        'production',
        'packaging',
        'transportation',
        'multi_delivery',
        'sell_percent',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products')
            ->withPivot([
                'price',
                'portion_grams',
                'units_per_box',
            ])
            ->withTimestamps();
    }


    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
