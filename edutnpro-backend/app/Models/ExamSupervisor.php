<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class ExamSupervisor extends Model
{
    protected $table = 'exam_supervisors';

    protected $fillable = [
    'exam_id',
    'teacher_id'
];

    protected $casts = [];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
