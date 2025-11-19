<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Challenge;
use App\Models\StudentAchievement;
use App\Models\XpTransaction;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminGamificationController extends Controller
{
    /**
     * Get gamification dashboard stats
     */
    public function getDashboard(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $stats = [
            'total_xp_awarded' => XpTransaction::whereHas('student', fn($q) =>
                $q->where('school_id', $schoolId)
            )->sum('amount'),

            'total_badges_earned' => DB::table('student_badges')
                ->join('students', 'student_badges.student_id', '=', 'students.id')
                ->where('students.school_id', $schoolId)
                ->count(),

            'total_active_challenges' => Challenge::where('school_id', $schoolId)
                ->where('is_active', true)
                ->count(),

            'students_with_achievements' => StudentAchievement::where('school_id', $schoolId)
                ->count(),

            'average_student_level' => StudentAchievement::where('school_id', $schoolId)
                ->avg('level'),

            'top_xp_earners' => StudentAchievement::where('school_id', $schoolId)
                ->with('student')
                ->orderBy('total_xp', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($achievement) => [
                    'student_name' => $achievement->student->getFullNameAttribute(),
                    'total_xp' => $achievement->total_xp,
                    'level' => $achievement->level,
                    'rank' => $achievement->rank,
                ]),

            'most_earned_badges' => DB::table('student_badges')
                ->select('badge_id', DB::raw('COUNT(*) as earn_count'))
                ->join('badges', 'student_badges.badge_id', '=', 'badges.id')
                ->join('students', 'student_badges.student_id', '=', 'students.id')
                ->where('students.school_id', $schoolId)
                ->groupBy('badge_id')
                ->orderBy('earn_count', 'desc')
                ->limit(10)
                ->get(),

            'xp_by_source' => XpTransaction::whereHas('student', fn($q) =>
                    $q->where('school_id', $schoolId)
                )
                ->select('source', DB::raw('SUM(amount) as total_xp'))
                ->groupBy('source')
                ->get(),

            'engagement_trend' => XpTransaction::whereHas('student', fn($q) =>
                    $q->where('school_id', $schoolId)
                )
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as transactions'),
                    DB::raw('SUM(amount) as total_xp')
                )
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Manage badges
     */
    public function getBadges(Request $request)
    {
        $badges = Badge::withCount('studentBadges')
            ->orderBy('category')
            ->orderBy('points')
            ->paginate(50);

        return response()->json($badges);
    }

    public function createBadge(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'required|string',
            'description_ar' => 'nullable|string',
            'icon' => 'required|string|max:10',
            'color' => 'required|string|max:7',
            'category' => 'required|in:attendance,academic,improvement,special,social',
            'criteria' => 'required|array',
            'points' => 'required|integer|min:0',
            'rarity' => 'required|in:common,rare,epic,legendary',
        ]);

        $badge = Badge::create(array_merge($validated, ['is_active' => true]));

        return response()->json(['badge' => $badge], 201);
    }

    public function updateBadge(Request $request, Badge $badge)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'sometimes|string',
            'description_ar' => 'nullable|string',
            'icon' => 'sometimes|string|max:10',
            'color' => 'sometimes|string|max:7',
            'category' => 'sometimes|in:attendance,academic,improvement,special,social',
            'criteria' => 'sometimes|array',
            'points' => 'sometimes|integer|min:0',
            'rarity' => 'sometimes|in:common,rare,epic,legendary',
            'is_active' => 'sometimes|boolean',
        ]);

        $badge->update($validated);

        return response()->json(['badge' => $badge]);
    }

    public function deleteBadge(Badge $badge)
    {
        $badge->delete();
        return response()->json(['message' => 'Badge deleted successfully']);
    }

    /**
     * Manage challenges
     */
    public function getChallenges(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $challenges = Challenge::where('school_id', $schoolId)
            ->withCount('studentChallenges')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($challenges);
    }

    public function createChallenge(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'required|string',
            'description_ar' => 'nullable|string',
            'type' => 'required|in:daily,weekly,monthly,special',
            'duration_type' => 'required|in:daily,weekly,monthly,custom',
            'target_type' => 'required|in:attendance,assignments,grades,xp,streak,participation',
            'target_value' => 'required|integer|min:1',
            'xp_reward' => 'required|integer|min:0',
            'badge_id' => 'nullable|exists:badges,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $schoolId = $request->user()->school_id;

        $challenge = Challenge::create(array_merge($validated, [
            'school_id' => $schoolId,
            'is_active' => true,
        ]));

        return response()->json(['challenge' => $challenge], 201);
    }

    public function updateChallenge(Request $request, Challenge $challenge)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'sometimes|string',
            'description_ar' => 'nullable|string',
            'type' => 'sometimes|in:daily,weekly,monthly,special',
            'target_value' => 'sometimes|integer|min:1',
            'xp_reward' => 'sometimes|integer|min:0',
            'badge_id' => 'nullable|exists:badges,id',
            'is_active' => 'sometimes|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $challenge->update($validated);

        return response()->json(['challenge' => $challenge]);
    }

    public function deleteChallenge(Challenge $challenge)
    {
        $challenge->delete();
        return response()->json(['message' => 'Challenge deleted successfully']);
    }

    /**
     * Award XP manually to student
     */
    public function awardXP(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|integer',
            'description' => 'required|string',
        ]);

        $student = Student::findOrFail($validated['student_id']);
        $achievement = $student->achievement;

        if (!$achievement) {
            $achievement = StudentAchievement::create([
                'student_id' => $student->id,
                'school_id' => $student->school_id,
            ]);
        }

        $achievement->addXP(
            $validated['amount'],
            XpTransaction::TYPE_BONUS,
            XpTransaction::SOURCE_MANUAL,
            null,
            $validated['description']
        );

        return response()->json([
            'message' => 'XP awarded successfully',
            'achievement' => $achievement->fresh(),
        ]);
    }

    /**
     * Reset student achievement
     */
    public function resetStudentAchievement(Student $student)
    {
        $achievement = $student->achievement;

        if ($achievement) {
            // Clear badges
            $student->badges()->detach();

            // Reset achievement
            $achievement->update([
                'total_xp' => 0,
                'level' => 1,
                'rank' => 'bronze',
                'attendance_streak' => 0,
                'assignment_streak' => 0,
                'best_streak' => 0,
            ]);

            // Clear XP transactions
            $student->xpTransactions()->delete();
        }

        return response()->json(['message' => 'Student achievement reset successfully']);
    }

    /**
     * Get system-wide gamification settings
     */
    public function getSettings()
    {
        return response()->json([
            'xp_per_level' => 100,
            'rank_thresholds' => [
                'bronze' => 0,
                'silver' => 500,
                'gold' => 2000,
                'platinum' => 5000,
                'diamond' => 10000,
            ],
            'xp_values' => [
                'attendance' => XpTransaction::XP_ATTENDANCE,
                'assignment_submit' => XpTransaction::XP_ASSIGNMENT_SUBMIT,
                'assignment_early' => XpTransaction::XP_ASSIGNMENT_EARLY,
                'grade_excellent' => XpTransaction::XP_GRADE_EXCELLENT,
                'grade_good' => XpTransaction::XP_GRADE_GOOD,
                'perfect_score' => XpTransaction::XP_PERFECT_SCORE,
                'streak_week' => XpTransaction::XP_STREAK_WEEK,
                'streak_month' => XpTransaction::XP_STREAK_MONTH,
            ],
        ]);
    }
}
