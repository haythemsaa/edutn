<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorSession extends Model
{
    protected $fillable = [
        'school_id',
        'tutor_id',
        'tutee_id',
        'subject_id',
        'topic',
        'description',
        'type',
        'mode',
        'scheduled_at',
        'duration_minutes',
        'meeting_url',
        'location',
        'status',
        'notes',
        'rating',
        'feedback',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_minutes' => 'integer',
        'rating' => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'tutor_id');
    }

    public function tutee(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'tutee_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function complete(string $notes = null, int $rating = null, string $feedback = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'notes' => $notes,
            'rating' => $rating,
            'feedback' => $feedback,
        ]);

        // Update tutor profile stats
        $tutorProfile = $this->tutor->tutorProfile;
        if ($tutorProfile) {
            $tutorProfile->increment('total_sessions');
            $tutorProfile->increment('total_hours', $this->duration_minutes / 60);

            if ($rating) {
                $avgRating = TutorSession::where('tutor_id', $this->tutor_id)
                    ->whereNotNull('rating')
                    ->avg('rating');
                $tutorProfile->update(['average_rating' => $avgRating]);
            }
        }

        // Award XP to tutor for helping
        if ($this->tutor->achievement) {
            $this->tutor->achievement->addXP(
                30, // XP for tutoring
                XpTransaction::TYPE_EARNED,
                'tutoring',
                $this,
                "Session de tutorat complétée avec {$this->tutee->getFullNameAttribute()}"
            );
        }
    }
}
