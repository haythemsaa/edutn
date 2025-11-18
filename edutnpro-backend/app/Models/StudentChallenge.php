<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentChallenge extends Model
{
    protected $fillable = [
        'student_id',
        'challenge_id',
        'progress',
        'completed_at',
        'xp_earned',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'progress' => 'integer',
        'xp_earned' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function updateProgress(int $increment = 1): void
    {
        $this->progress += $increment;
        $this->save();

        // Check if challenge is completed
        if ($this->progress >= $this->challenge->target_value && !$this->completed_at) {
            $this->challenge->awardCompletion($this->student);
        }
    }

    public function getProgressPercentage(): float
    {
        if ($this->challenge->target_value == 0) {
            return 0;
        }

        return min(($this->progress / $this->challenge->target_value) * 100, 100);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }
}
