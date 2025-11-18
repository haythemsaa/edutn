<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassDiary extends Model
{
    protected $fillable = [
        'class_section_id', 'teacher_id', 'subject_id', 'lesson_date', 'start_time', 'end_time',
        'lesson_title', 'lesson_title_ar', 'lesson_content', 'lesson_content_ar',
        'objectives', 'objectives_ar', 'homework', 'homework_ar', 'attachments',
        'students_present', 'students_absent', 'notes', 'status',
    ];

    protected $casts = [
        'lesson_date' => 'date',
        'attachments' => 'array',
    ];

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}