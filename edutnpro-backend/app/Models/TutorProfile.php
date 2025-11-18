<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TutorProfile extends Model
{
    protected $fillable = [
        'student_id',
        'bio',
        'bio_ar',
        'subjects',
        'availability',
        'average_rating',
        'total_sessions',
        'total_hours',
        'is_verified',
        'is_active',
    ];

    protected $casts = [
        'subjects' => 'array',
        'availability' => 'array',
        'average_rating' => 'decimal:2',
        'total_sessions' => 'integer',
        'total_hours' => 'integer',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TutorSession::class, 'tutor_id', 'student_id');
    }

    public function getStatsAttribute(): array
    {
        return [
            'total_sessions' => $this->total_sessions,
            'total_hours' => $this->total_hours,
            'average_rating' => $this->average_rating,
            'subjects_count' => count($this->subjects ?? []),
        ];
    }
}
