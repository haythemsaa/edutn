<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XpTransaction extends Model
{
    protected $fillable = [
        'student_id',
        'type',
        'amount',
        'source',
        'source_model_type',
        'source_model_id',
        'description',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function sourceModel()
    {
        return $this->morphTo('source_model');
    }

    // XP Types
    const TYPE_EARNED = 'earned';
    const TYPE_BONUS = 'bonus';
    const TYPE_PENALTY = 'penalty';
    const TYPE_QUEST = 'quest';
    const TYPE_ACHIEVEMENT = 'achievement';

    // XP Sources
    const SOURCE_ATTENDANCE = 'attendance';
    const SOURCE_ASSIGNMENT = 'assignment';
    const SOURCE_GRADE = 'grade';
    const SOURCE_BEHAVIOR = 'behavior';
    const SOURCE_PARTICIPATION = 'participation';
    const SOURCE_QUIZ = 'quiz';
    const SOURCE_EXAM = 'exam';
    const SOURCE_PROJECT = 'project';
    const SOURCE_CHALLENGE = 'challenge';
    const SOURCE_BADGE = 'badge';
    const SOURCE_STREAK = 'streak';
    const SOURCE_MANUAL = 'manual';

    // XP Amounts
    const XP_ATTENDANCE = 10;
    const XP_ASSIGNMENT_SUBMIT = 20;
    const XP_ASSIGNMENT_EARLY = 30;
    const XP_GRADE_EXCELLENT = 50; // >= 90%
    const XP_GRADE_GOOD = 30; // >= 80%
    const XP_GRADE_AVERAGE = 15; // >= 70%
    const XP_PERFECT_SCORE = 100;
    const XP_IMPROVED_GRADE = 25;
    const XP_QUIZ_COMPLETE = 15;
    const XP_EXAM_COMPLETE = 40;
    const XP_PROJECT_SUBMIT = 50;
    const XP_STREAK_WEEK = 50;
    const XP_STREAK_MONTH = 200;
    const XP_BADGE_COMMON = 25;
    const XP_BADGE_RARE = 50;
    const XP_BADGE_EPIC = 100;
    const XP_BADGE_LEGENDARY = 250;
}
