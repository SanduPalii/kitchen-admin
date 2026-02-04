<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ComponentItem extends Pivot
{
    protected $table = 'component_items';

    public $timestamps = true;

    protected $fillable = [
        'component_id',
        'ingredient_id',
        'quantity',
    ];

    public function component()
    {
        return $this->belongsTo(Component::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
