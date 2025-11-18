<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class Attendance extends Model
{
    protected $fillable = [
    'student_id',
    'class_id',
    'date',
    'period',
    'status',
    'comments',
    'teacher_id',
    'justified_at',
    'justification_document'
];

    protected $casts = [
    'date' => 'date',
    'justified_at' => 'datetime'
];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
