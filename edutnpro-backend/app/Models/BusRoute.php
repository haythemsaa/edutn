<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class BusRoute extends Model
{
    protected $fillable = [
    'bus_id',
    'route_name',
    'shift',
    'departure_time',
    'arrival_time',
    'distance_km',
    'estimated_duration',
    'is_active'
];

    protected $casts = [
    'distance_km' => 'decimal:2',
    'estimated_duration' => 'integer',
    'is_active' => 'boolean'
];

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function stops()
    {
        return $this->hasMany(BusStop::class, 'route_id');
    }

    public function studentTransportations()
    {
        return $this->hasMany(StudentTransportation::class, 'route_id');
    }
}
