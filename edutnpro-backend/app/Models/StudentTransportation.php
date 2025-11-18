<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class StudentTransportation extends Model
{
    protected $fillable = [
    'student_id',
    'route_id',
    'pickup_stop_id',
    'dropoff_stop_id',
    'shift',
    'is_active',
    'monthly_fee'
];

    protected $casts = [
    'is_active' => 'boolean',
    'monthly_fee' => 'decimal:2'
];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function route()
    {
        return $this->belongsTo(BusRoute::class);
    }

    public function pickupStop()
    {
        return $this->belongsTo(BusStop::class, 'pickup_stop_id');
    }

    public function dropoffStop()
    {
        return $this->belongsTo(BusStop::class, 'dropoff_stop_id');
    }
}
