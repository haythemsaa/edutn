<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $fillable = [
        'teacher_id',
        'school_id',
        'title',
        'title_ar',
        'description',
        'description_ar',
        'subject',
        'class_level',
        'type',
        'due_date',
        'total_points',
        'attachments',
        'status',
        'allow_late_submission',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'attachments' => 'array',
        'allow_late_submission' => 'boolean',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}
