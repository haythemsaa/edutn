<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportCard extends Model
{
    protected $fillable = [
        'student_id', 'class_section_id', 'school_id', 'academic_year', 'term',
        'total_average', 'class_rank', 'total_students', 'total_absences',
        'general_appreciation', 'general_appreciation_ar', 'conduct_comment', 'conduct_comment_ar',
        'status', 'published_at', 'sent_at', 'file_path',
    ];

    protected $casts = [
        'total_average' => 'decimal:2',
        'published_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReportCardItem::class);
    }
}