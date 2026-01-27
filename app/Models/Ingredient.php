<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'size',
        'unit',
        'kg_price',
    ];

    public function components()
    {
        return $this->belongsToMany(
            Component::class,
            'component_items'
        )->withPivot('quantity');
    }
}
