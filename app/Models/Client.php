<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'location_id',
        'active',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
