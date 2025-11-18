<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineClass extends Model
{
    protected $fillable = [
        'teacher_id', 'school_id', 'subject', 'class_level', 'title', 'title_ar',
        'description', 'description_ar', 'platform', 'meeting_url', 'meeting_id',
        'meeting_password', 'scheduled_at', 'duration_minutes', 'started_at',
        'ended_at', 'max_participants', 'participants_count', 'status',
        'recording_url', 'attachments', 'notes', 'is_recorded', 'auto_admit',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'attachments' => 'array',
        'is_recorded' => 'boolean',
        'auto_admit' => 'boolean',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}