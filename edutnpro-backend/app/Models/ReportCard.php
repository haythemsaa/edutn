<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class ReportCard extends Model
{
    protected $fillable = [
    'student_id',
    'term_id',
    'general_average',
    'class_rank',
    'class_size',
    'mention',
    'pdf_path',
    'data',
    'generated_at'
];

    protected $casts = [
    'general_average' => 'decimal:2',
    'class_rank' => 'integer',
    'class_size' => 'integer',
    'data' => 'array',
    'generated_at' => 'datetime'
];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }
}
