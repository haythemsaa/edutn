<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class StudentPoint extends Model
{
    protected $fillable = [
    'student_id',
    'total_points',
    'current_level',
    'current_streak',
    'longest_streak',
    'class_rank',
    'school_rank',
    'last_activity_date'
];

    protected $casts = [
    'total_points' => 'integer',
    'current_level' => 'integer',
    'current_streak' => 'integer',
    'longest_streak' => 'integer',
    'class_rank' => 'integer',
    'school_rank' => 'integer',
    'last_activity_date' => 'date'
];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
