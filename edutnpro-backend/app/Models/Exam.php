<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class Exam extends Model
{
    protected $fillable = [
    'name',
    'class_subject_id',
    'term_id',
    'exam_date',
    'start_time',
    'duration',
    'classroom_id',
    'max_score',
    'coefficient',
    'instructions'
];

    protected $casts = [
    'exam_date' => 'date',
    'duration' => 'integer',
    'max_score' => 'decimal:2',
    'coefficient' => 'decimal:2'
];

    public function classSubject()
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function supervisors()
    {
        return $this->belongsToMany(Teacher::class, 'exam_supervisors')->withTimestamps();
    }
}
