<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Component extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'quantity'];
    protected $appends = ['cost'];
    public $timestamps = true;

    public function ingredients()
    {
        return $this->belongsToMany(
            Ingredient::class,
            'component_items'
        )
            ->using(ComponentItem::class)
            ->withPivot('quantity')
            ->withTimestamps()
            ->orderByPivot('id');
    }

    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'product_components'
        )->withPivot('quantity');
    }

    public function getCostAttribute(): float
    {
        $this->loadMissing('ingredients');

        return $this->ingredients->sum(function ($ingredient) {
            // Round each row to 2dp before summing — matches Excel's per-row ROUND()
            return round((float) $ingredient->pivot->quantity * (float) ($ingredient->kg_price ?? 0), 2);
        });
    }
}

