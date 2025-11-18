<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class BusStop extends Model
{
    protected $fillable = [
    'route_id',
    'stop_name',
    'address',
    'latitude',
    'longitude',
    'stop_order',
    'arrival_time'
];

    protected $casts = [
    'latitude' => 'decimal:8',
    'longitude' => 'decimal:8',
    'stop_order' => 'integer'
];

    public function route()
    {
        return $this->belongsTo(BusRoute::class);
    }
}
