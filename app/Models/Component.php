<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Component extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
    ];

    public function ingredients()
    {
        return $this->belongsToMany(
            Ingredient::class,
            'component_items'
        )->withPivot('quantity');
    }

    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'product_components'
        )->withPivot('quantity');
    }
}
