<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class StudentBadge extends Model
{
    protected $fillable = [
    'student_id',
    'badge_id',
    'earned_at',
    'progress',
    'metadata'
];

    protected $casts = [
    'earned_at' => 'datetime',
    'progress' => 'integer',
    'metadata' => 'array'
];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }
}
