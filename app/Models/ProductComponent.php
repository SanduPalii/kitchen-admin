<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductComponent extends Pivot
{
    protected $table = 'product_components';

    protected $fillable = [
        'product_id',
        'component_id',
        'quantity',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}
