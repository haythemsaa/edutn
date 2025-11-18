<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Challenge extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'type',
        'duration_type',
        'target_type',
        'target_value',
        'xp_reward',
        'badge_id',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'xp_reward' => 'integer',
        'target_value' => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function studentChallenges(): HasMany
    {
        return $this->hasMany(StudentChallenge::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_challenges')
            ->withPivot('progress', 'completed_at', 'xp_earned')
            ->withTimestamps();
    }

    // Challenge Types
    const TYPE_DAILY = 'daily';
    const TYPE_WEEKLY = 'weekly';
    const TYPE_MONTHLY = 'monthly';
    const TYPE_SPECIAL = 'special';

    // Duration Types
    const DURATION_DAILY = 'daily';
    const DURATION_WEEKLY = 'weekly';
    const DURATION_MONTHLY = 'monthly';
    const DURATION_CUSTOM = 'custom';

    // Target Types
    const TARGET_ATTENDANCE = 'attendance';
    const TARGET_ASSIGNMENTS = 'assignments';
    const TARGET_GRADES = 'grades';
    const TARGET_XP = 'xp';
    const TARGET_STREAK = 'streak';
    const TARGET_PARTICIPATION = 'participation';

    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->start_date && $now->isBefore($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->isAfter($this->end_date)) {
            return false;
        }

        return true;
    }

    public function checkCompletion(Student $student): bool
    {
        $studentChallenge = $this->studentChallenges()
            ->where('student_id', $student->id)
            ->first();

        if (!$studentChallenge) {
            return false;
        }

        return $studentChallenge->progress >= $this->target_value;
    }

    public function awardCompletion(Student $student): void
    {
        $studentChallenge = $this->studentChallenges()
            ->where('student_id', $student->id)
            ->first();

        if (!$studentChallenge || $studentChallenge->completed_at) {
            return;
        }

        $studentChallenge->update([
            'completed_at' => now(),
            'xp_earned' => $this->xp_reward,
        ]);

        // Award XP
        $achievement = $student->achievement;
        if ($achievement) {
            $achievement->addXP(
                $this->xp_reward,
                XpTransaction::TYPE_QUEST,
                XpTransaction::SOURCE_CHALLENGE,
                $this,
                "Défi complété: {$this->name}"
            );
        }

        // Award badge if any
        if ($this->badge_id) {
            StudentBadge::firstOrCreate([
                'student_id' => $student->id,
                'badge_id' => $this->badge_id,
            ], [
                'earned_at' => now(),
                'progress' => 100,
            ]);
        }
    }
}
