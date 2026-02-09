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
        'approved',
        'date',
    ];

    public function products(){
        return $this->belongsToMany(Product::class, 'order_products')
            ->withPivot([
                'price',
                'packaging_material_price',
                'production_price',
                'packaging_price',
                'transportation_price',
                'multi_delivery_price',
                'sell_percent',
            ])
            ->withTimestamps();
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
