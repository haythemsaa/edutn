<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class ClassSubject extends Model
{
    protected $fillable = [
    'class_id',
    'subject_id',
    'teacher_id',
    'coefficient',
    'hours_per_week'
];

    protected $casts = [
    'coefficient' => 'decimal:2',
    'hours_per_week' => 'decimal:2'
];

    public function class()
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function timetableSlots()
    {
        return $this->hasMany(TimetableSlot::class);
    }
}
