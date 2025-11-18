<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class Leaderboard extends Model
{
    protected $fillable = [
    'type',
    'school_id',
    'class_id',
    'period_start',
    'period_end',
    'rankings'
];

    protected $casts = [
    'period_start' => 'date',
    'period_end' => 'date',
    'rankings' => 'array'
];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassRoom::class);
    }
}
