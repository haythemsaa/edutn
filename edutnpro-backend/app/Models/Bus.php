<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class Bus extends Model
{
    protected $fillable = [
    'school_id',
    'bus_number',
    'license_plate',
    'capacity',
    'driver_name',
    'driver_phone',
    'driver_license',
    'supervisor_name',
    'supervisor_phone',
    'gps_device_id',
    'status',
    'insurance_expiry',
    'inspection_date'
];

    protected $casts = [
    'capacity' => 'integer',
    'insurance_expiry' => 'date',
    'inspection_date' => 'date'
];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function routes()
    {
        return $this->hasMany(BusRoute::class);
    }
}
