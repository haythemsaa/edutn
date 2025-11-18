<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class TimetableSlot extends Model
{
    protected $fillable = [
    'class_id',
    'class_subject_id',
    'classroom_id',
    'day_of_week',
    'start_time',
    'end_time',
    'academic_year_id'
];

    protected $casts = [
    'day_of_week' => 'integer'
];

    public function class()
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function classSubject()
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
