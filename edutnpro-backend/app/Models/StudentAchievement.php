<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentAchievement extends Model
{
    protected $fillable = [
        'student_id', 'school_id', 'total_xp', 'level', 'rank',
        'attendance_streak', 'assignment_streak', 'best_streak', 'stats',
    ];

    protected $casts = [
        'stats' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function badges(): HasMany
    {
        return $this->hasMany(StudentBadge::class, 'student_id', 'student_id');
    }

    public function addXP(int $amount, string $type, string $source, $sourceModel = null, string $description = null): void
    {
        $this->total_xp += $amount;

        // Calculate new level (100 XP per level)
        $this->level = floor($this->total_xp / 100) + 1;

        // Calculate rank based on XP
        $this->rank = $this->calculateRank($this->total_xp);

        $this->save();

        // Log transaction
        XpTransaction::create([
            'student_id' => $this->student_id,
            'type' => $type,
            'amount' => $amount,
            'source' => $source,
            'source_model_type' => $sourceModel ? get_class($sourceModel) : null,
            'source_model_id' => $sourceModel?->id,
            'description' => $description,
        ]);
    }

    public function updateStreak(string $type, bool $increment = true): void
    {
        $field = $type . '_streak';

        if ($increment) {
            $this->$field++;
            if ($this->$field > $this->best_streak) {
                $this->best_streak = $this->$field;
            }
        } else {
            $this->$field = 0;
        }

        $this->save();
    }

    public function resetStreak(string $type): void
    {
        $this->updateStreak($type, false);
    }

    private function calculateRank(int $xp): string
    {
        return match (true) {
            $xp >= 10000 => 'diamond',
            $xp >= 5000 => 'platinum',
            $xp >= 2000 => 'gold',
            $xp >= 500 => 'silver',
            default => 'bronze',
        };
    }

    public function getRankColor(): string
    {
        return match ($this->rank) {
            'diamond' => '#B9F2FF',
            'platinum' => '#E5E4E2',
            'gold' => '#FFD700',
            'silver' => '#C0C0C0',
            'bronze' => '#CD7F32',
            default => '#808080',
        };
    }

    public function getXPToNextLevel(): int
    {
        $nextLevelXP = $this->level * 100;
        return $nextLevelXP - ($this->total_xp % 100);
    }

    public function getLevelProgress(): float
    {
        $currentLevelXP = ($this->level - 1) * 100;
        $nextLevelXP = $this->level * 100;
        $progressXP = $this->total_xp - $currentLevelXP;

        return ($progressXP / 100) * 100;
    }
}
