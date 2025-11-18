<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class Classroom extends Model
{
    protected $fillable = [
    'school_id',
    'name',
    'room_number',
    'capacity',
    'room_type',
    'equipment'
];

    protected $casts = [
    'equipment' => 'array',
    'capacity' => 'integer'
];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classes()
    {
        return $this->hasMany(ClassRoom::class, 'classroom_id');
    }
}
