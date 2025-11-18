<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudentBadge;
use App\Models\Challenge;
use App\Models\StudentChallenge;
use App\Models\XpTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GamificationController extends Controller
{
    /**
     * Get student achievement profile
     */
    public function getAchievement(Student $student)
    {
        $achievement = $student->achievement()->with('badges.badge')->first();

        if (!$achievement) {
            // Create achievement record if doesn't exist
            $achievement = StudentAchievement::create([
                'student_id' => $student->id,
                'school_id' => $student->school_id,
                'total_xp' => 0,
                'level' => 1,
                'rank' => 'bronze',
            ]);
        }

        return response()->json([
            'achievement' => [
                'total_xp' => $achievement->total_xp,
                'level' => $achievement->level,
                'rank' => $achievement->rank,
                'rank_color' => $achievement->getRankColor(),
                'xp_to_next_level' => $achievement->getXPToNextLevel(),
                'level_progress' => $achievement->getLevelProgress(),
                'attendance_streak' => $achievement->attendance_streak,
                'assignment_streak' => $achievement->assignment_streak,
                'best_streak' => $achievement->best_streak,
                'badges' => $achievement->badges->map(fn($sb) => [
                    'id' => $sb->badge->id,
                    'name' => $sb->badge->name,
                    'name_ar' => $sb->badge->name_ar,
                    'icon' => $sb->badge->icon,
                    'color' => $sb->badge->color,
                    'category' => $sb->badge->category,
                    'rarity' => $sb->badge->rarity,
                    'earned_at' => $sb->earned_at,
                ]),
            ],
        ]);
    }

    /**
     * Get leaderboard
     */
    public function getLeaderboard(Request $request)
    {
        $type = $request->get('type', 'class'); // class, school, global
        $studentId = $request->user()->userable_id;
        $student = Student::findOrFail($studentId);

        $query = StudentAchievement::with('student');

        switch ($type) {
            case 'class':
                $classId = $student->class_id;
                $query->whereHas('student', fn($q) => $q->where('class_id', $classId));
                break;
            case 'school':
                $query->where('school_id', $student->school_id);
                break;
            case 'global':
                // All students
                break;
        }

        $leaderboard = $query->orderBy('total_xp', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($achievement, $index) {
                return [
                    'rank' => $index + 1,
                    'student_name' => $achievement->student->name,
                    'total_xp' => $achievement->total_xp,
                    'level' => $achievement->level,
                    'rank_tier' => $achievement->rank,
                    'rank_color' => $achievement->getRankColor(),
                ];
            });

        // Find current student's rank
        $myRank = $leaderboard->search(fn($item) => $item['student_name'] === $student->name);

        return response()->json([
            'leaderboard' => $leaderboard,
            'my_rank' => $myRank !== false ? $myRank + 1 : null,
        ]);
    }

    /**
     * Get all badges
     */
    public function getBadges(Request $request)
    {
        $category = $request->get('category');

        $query = Badge::where('is_active', true);

        if ($category) {
            $query->where('category', $category);
        }

        $badges = $query->orderBy('category')->orderBy('points')->get();

        // Get student's earned badges if authenticated
        $earnedBadgeIds = [];
        if ($request->user()) {
            $studentId = $request->user()->userable_id;
            $earnedBadgeIds = StudentBadge::where('student_id', $studentId)
                ->pluck('badge_id')
                ->toArray();
        }

        return response()->json([
            'badges' => $badges->map(fn($badge) => [
                'id' => $badge->id,
                'name' => $badge->name,
                'name_ar' => $badge->name_ar,
                'description' => $badge->description,
                'description_ar' => $badge->description_ar,
                'icon' => $badge->icon,
                'color' => $badge->color,
                'category' => $badge->category,
                'rarity' => $badge->rarity,
                'points' => $badge->points,
                'earned' => in_array($badge->id, $earnedBadgeIds),
            ]),
        ]);
    }

    /**
     * Get active challenges
     */
    public function getChallenges(Request $request)
    {
        $studentId = $request->user()->userable_id;
        $student = Student::findOrFail($studentId);

        $challenges = Challenge::where('school_id', $student->school_id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->with(['studentChallenges' => fn($q) => $q->where('student_id', $studentId)])
            ->get();

        return response()->json([
            'challenges' => $challenges->map(function ($challenge) {
                $studentChallenge = $challenge->studentChallenges->first();

                return [
                    'id' => $challenge->id,
                    'name' => $challenge->name,
                    'name_ar' => $challenge->name_ar,
                    'description' => $challenge->description,
                    'description_ar' => $challenge->description_ar,
                    'type' => $challenge->type,
                    'target_type' => $challenge->target_type,
                    'target_value' => $challenge->target_value,
                    'xp_reward' => $challenge->xp_reward,
                    'start_date' => $challenge->start_date,
                    'end_date' => $challenge->end_date,
                    'progress' => $studentChallenge?->progress ?? 0,
                    'progress_percentage' => $studentChallenge?->getProgressPercentage() ?? 0,
                    'completed' => $studentChallenge?->isCompleted() ?? false,
                    'completed_at' => $studentChallenge?->completed_at,
                ];
            }),
        ]);
    }

    /**
     * Get XP transaction history
     */
    public function getTransactions(Request $request)
    {
        $studentId = $request->user()->userable_id;

        $transactions = XpTransaction::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($transactions);
    }

    /**
     * Award XP to student (internal use by other controllers)
     */
    public static function awardXP(Student $student, int $amount, string $type, string $source, $sourceModel = null, string $description = null): void
    {
        $achievement = $student->achievement;

        if (!$achievement) {
            $achievement = StudentAchievement::create([
                'student_id' => $student->id,
                'school_id' => $student->school_id,
            ]);
        }

        $achievement->addXP($amount, $type, $source, $sourceModel, $description);

        // Check for badge eligibility after XP award
        self::checkBadges($student);

        // Update challenge progress if applicable
        self::updateChallengeProgress($student, $source);
    }

    /**
     * Check and award eligible badges
     */
    protected static function checkBadges(Student $student): void
    {
        $achievement = $student->achievement;
        if (!$achievement) {
            return;
        }

        $badges = Badge::where('is_active', true)->get();

        foreach ($badges as $badge) {
            // Skip if already earned
            if (StudentBadge::where('student_id', $student->id)->where('badge_id', $badge->id)->exists()) {
                continue;
            }

            $criteria = $badge->criteria;
            $eligible = false;

            // Check badge criteria
            switch ($criteria['type']) {
                case 'total_xp':
                    $eligible = $achievement->total_xp >= $criteria['value'];
                    break;

                case 'level':
                    $eligible = $achievement->level >= $criteria['value'];
                    break;

                case 'attendance_streak':
                    $eligible = $achievement->attendance_streak >= $criteria['value'];
                    break;

                case 'attendance_days':
                    // Would need to count from attendance records
                    break;

                case 'assignments_submitted':
                    // Would need to count from assignments
                    break;

                case 'average_above':
                    // Check student overall average
                    $avgGrade = DB::table('grades')
                        ->where('student_id', $student->id)
                        ->avg('grade');
                    $eligible = $avgGrade >= $criteria['value'];
                    break;

                // Add more criteria checks as needed
            }

            if ($eligible) {
                StudentBadge::create([
                    'student_id' => $student->id,
                    'badge_id' => $badge->id,
                    'earned_at' => now(),
                    'progress' => 100,
                ]);

                // Award bonus XP for earning badge
                $xpBonus = match ($badge->rarity) {
                    Badge::RARITY_LEGENDARY => XpTransaction::XP_BADGE_LEGENDARY,
                    Badge::RARITY_EPIC => XpTransaction::XP_BADGE_EPIC,
                    Badge::RARITY_RARE => XpTransaction::XP_BADGE_RARE,
                    default => XpTransaction::XP_BADGE_COMMON,
                };

                $achievement->addXP(
                    $xpBonus,
                    XpTransaction::TYPE_ACHIEVEMENT,
                    XpTransaction::SOURCE_BADGE,
                    $badge,
                    "Badge débloqué: {$badge->name}"
                );
            }
        }
    }

    /**
     * Update challenge progress
     */
    protected static function updateChallengeProgress(Student $student, string $source): void
    {
        $challenges = Challenge::where('school_id', $student->school_id)
            ->where('is_active', true)
            ->where(function ($query) use ($source) {
                $targetType = match ($source) {
                    XpTransaction::SOURCE_ATTENDANCE => Challenge::TARGET_ATTENDANCE,
                    XpTransaction::SOURCE_ASSIGNMENT => Challenge::TARGET_ASSIGNMENTS,
                    XpTransaction::SOURCE_GRADE => Challenge::TARGET_GRADES,
                    default => null,
                };

                if ($targetType) {
                    $query->where('target_type', $targetType);
                }
            })
            ->get();

        foreach ($challenges as $challenge) {
            $studentChallenge = StudentChallenge::firstOrCreate([
                'student_id' => $student->id,
                'challenge_id' => $challenge->id,
            ], [
                'progress' => 0,
            ]);

            if (!$studentChallenge->isCompleted()) {
                $studentChallenge->updateProgress(1);
            }
        }
    }

    /**
     * Seed default badges (one-time setup)
     */
    public function seedBadges()
    {
        $defaultBadges = Badge::getDefaultBadges();

        foreach ($defaultBadges as $badgeData) {
            Badge::firstOrCreate(
                ['name' => $badgeData['name']],
                array_merge($badgeData, ['is_active' => true])
            );
        }

        return response()->json([
            'message' => 'Default badges seeded successfully',
            'count' => count($defaultBadges),
        ]);
    }
}
