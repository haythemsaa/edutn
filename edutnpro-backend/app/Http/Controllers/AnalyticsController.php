<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudyGroup;
use App\Models\TutorSession;
use App\Models\ForumTopic;
use App\Models\SharedResource;
use App\Models\HelpRequest;
use App\Models\XpTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get comprehensive school analytics dashboard
     */
    public function getSchoolDashboard(Request $request)
    {
        $schoolId = $request->user()->school_id;

        return response()->json([
            'overview' => $this->getOverviewStats($schoolId),
            'gamification' => $this->getGamificationStats($schoolId),
            'social_learning' => $this->getSocialLearningStats($schoolId),
            'engagement' => $this->getEngagementStats($schoolId),
            'trends' => $this->getTrends($schoolId),
        ]);
    }

    /**
     * Overview statistics
     */
    private function getOverviewStats($schoolId)
    {
        return [
            'total_students' => Student::where('school_id', $schoolId)->count(),
            'active_students_today' => XpTransaction::whereHas('student', fn($q) =>
                    $q->where('school_id', $schoolId)
                )
                ->whereDate('created_at', today())
                ->distinct('student_id')
                ->count('student_id'),

            'total_xp_awarded' => XpTransaction::whereHas('student', fn($q) =>
                    $q->where('school_id', $schoolId)
                )
                ->sum('amount'),

            'total_badges_earned' => DB::table('student_badges')
                ->join('students', 'student_badges.student_id', '=', 'students.id')
                ->where('students.school_id', $schoolId)
                ->count(),

            'active_study_groups' => StudyGroup::where('school_id', $schoolId)
                ->where('is_active', true)
                ->count(),

            'tutor_sessions_this_week' => TutorSession::where('school_id', $schoolId)
                ->where('created_at', '>=', now()->startOfWeek())
                ->count(),
        ];
    }

    /**
     * Gamification statistics
     */
    private function getGamificationStats($schoolId)
    {
        return [
            'average_student_level' => DB::table('student_achievements')
                ->where('school_id', $schoolId)
                ->avg('level'),

            'students_by_rank' => DB::table('student_achievements')
                ->where('school_id', $schoolId)
                ->select('rank', DB::raw('COUNT(*) as count'))
                ->groupBy('rank')
                ->get(),

            'xp_by_source' => XpTransaction::whereHas('student', fn($q) =>
                    $q->where('school_id', $schoolId)
                )
                ->select('source', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('source')
                ->get(),

            'top_achievers' => DB::table('student_achievements')
                ->join('students', 'student_achievements.student_id', '=', 'students.id')
                ->where('students.school_id', $schoolId)
                ->select(
                    'students.id',
                    DB::raw('CONCAT(students.first_name, " ", students.last_name) as name'),
                    'student_achievements.total_xp',
                    'student_achievements.level',
                    'student_achievements.rank'
                )
                ->orderBy('student_achievements.total_xp', 'desc')
                ->limit(10)
                ->get(),

            'most_popular_badges' => DB::table('student_badges')
                ->join('badges', 'student_badges.badge_id', '=', 'badges.id')
                ->join('students', 'student_badges.student_id', '=', 'students.id')
                ->where('students.school_id', $schoolId)
                ->select('badges.name', 'badges.icon', DB::raw('COUNT(*) as earned_count'))
                ->groupBy('badges.id', 'badges.name', 'badges.icon')
                ->orderBy('earned_count', 'desc')
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * Social learning statistics
     */
    private function getSocialLearningStats($schoolId)
    {
        return [
            'study_groups' => [
                'total' => StudyGroup::where('school_id', $schoolId)->count(),
                'active' => StudyGroup::where('school_id', $schoolId)
                    ->where('is_active', true)
                    ->count(),
                'average_members' => DB::table('study_group_members')
                    ->join('study_groups', 'study_group_members.study_group_id', '=', 'study_groups.id')
                    ->where('study_groups.school_id', $schoolId)
                    ->where('study_group_members.status', 'active')
                    ->select('study_groups.id', DB::raw('COUNT(*) as member_count'))
                    ->groupBy('study_groups.id')
                    ->avg('member_count'),
            ],

            'tutoring' => [
                'total_sessions' => TutorSession::where('school_id', $schoolId)->count(),
                'completed_sessions' => TutorSession::where('school_id', $schoolId)
                    ->where('status', 'completed')
                    ->count(),
                'average_rating' => TutorSession::where('school_id', $schoolId)
                    ->whereNotNull('rating')
                    ->avg('rating'),
                'total_hours' => TutorSession::where('school_id', $schoolId)
                    ->where('status', 'completed')
                    ->sum('duration_minutes') / 60,
            ],

            'forums' => [
                'total_topics' => ForumTopic::whereHas('forum', fn($q) =>
                        $q->where('school_id', $schoolId)
                    )->count(),
                'solved_topics' => ForumTopic::whereHas('forum', fn($q) =>
                        $q->where('school_id', $schoolId)
                    )
                    ->where('is_solved', true)
                    ->count(),
                'total_replies' => DB::table('forum_replies')
                    ->join('forum_topics', 'forum_replies.topic_id', '=', 'forum_topics.id')
                    ->join('subject_forums', 'forum_topics.forum_id', '=', 'subject_forums.id')
                    ->where('subject_forums.school_id', $schoolId)
                    ->count(),
            ],

            'resources' => [
                'total_shared' => SharedResource::where('school_id', $schoolId)->count(),
                'total_downloads' => SharedResource::where('school_id', $schoolId)
                    ->sum('downloads_count'),
                'verified' => SharedResource::where('school_id', $schoolId)
                    ->where('is_verified', true)
                    ->count(),
                'by_type' => SharedResource::where('school_id', $schoolId)
                    ->select('type', DB::raw('COUNT(*) as count'))
                    ->groupBy('type')
                    ->get(),
            ],

            'help_requests' => [
                'total' => HelpRequest::where('school_id', $schoolId)->count(),
                'answered' => HelpRequest::where('school_id', $schoolId)
                    ->where('status', 'answered')
                    ->count(),
                'average_response_time' => DB::table('help_requests')
                    ->where('school_id', $schoolId)
                    ->whereNotNull('answered_at')
                    ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, answered_at)) as avg_hours'))
                    ->value('avg_hours'),
            ],
        ];
    }

    /**
     * Engagement statistics
     */
    private function getEngagementStats($schoolId)
    {
        $totalStudents = Student::where('school_id', $schoolId)->count();

        // Students active in last 7 days
        $activeStudents = XpTransaction::whereHas('student', fn($q) =>
                $q->where('school_id', $schoolId)
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->distinct('student_id')
            ->count('student_id');

        return [
            'engagement_rate' => $totalStudents > 0 ? ($activeStudents / $totalStudents) * 100 : 0,
            'active_students_week' => $activeStudents,
            'inactive_students' => $totalStudents - $activeStudents,

            'daily_active_users' => XpTransaction::whereHas('student', fn($q) =>
                    $q->where('school_id', $schoolId)
                )
                ->whereDate('created_at', today())
                ->distinct('student_id')
                ->count('student_id'),

            'actions_per_student' => $totalStudents > 0 ?
                XpTransaction::whereHas('student', fn($q) =>
                    $q->where('school_id', $schoolId)
                )->count() / $totalStudents : 0,

            'most_active_students' => DB::table('xp_transactions')
                ->join('students', 'xp_transactions.student_id', '=', 'students.id')
                ->where('students.school_id', $schoolId)
                ->where('xp_transactions.created_at', '>=', now()->subDays(30))
                ->select(
                    'students.id',
                    DB::raw('CONCAT(students.first_name, " ", students.last_name) as name'),
                    DB::raw('COUNT(*) as action_count'),
                    DB::raw('SUM(xp_transactions.amount) as total_xp')
                )
                ->groupBy('students.id', 'name')
                ->orderBy('action_count', 'desc')
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * Trend analysis
     */
    private function getTrends($schoolId)
    {
        // Last 30 days XP trend
        $xpTrend = XpTransaction::whereHas('student', fn($q) =>
                $q->where('school_id', $schoolId)
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total_xp'),
                DB::raw('COUNT(DISTINCT student_id) as active_students')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Study group growth
        $groupTrend = StudyGroup::where('school_id', $schoolId)
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as new_groups')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Tutoring sessions trend
        $tutoringTrend = TutorSession::where('school_id', $schoolId)
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as sessions')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'xp_trend' => $xpTrend,
            'study_group_growth' => $groupTrend,
            'tutoring_trend' => $tutoringTrend,
        ];
    }

    /**
     * Get student individual analytics
     */
    public function getStudentAnalytics(Request $request, Student $student)
    {
        return response()->json([
            'gamification' => [
                'achievement' => $student->achievement,
                'badges_earned' => $student->badges()->count(),
                'total_xp' => $student->achievement?->total_xp ?? 0,
                'level' => $student->achievement?->level ?? 1,
                'rank' => $student->achievement?->rank ?? 'bronze',
                'xp_history' => $student->xpTransactions()
                    ->orderBy('created_at', 'desc')
                    ->limit(50)
                    ->get(),
            ],

            'social_learning' => [
                'study_groups_joined' => $student->studyGroups()->count(),
                'study_groups_created' => $student->createdStudyGroups()->count(),
                'tutoring_sessions_given' => $student->tutorSessions()->count(),
                'tutoring_sessions_received' => $student->tuteeSessions()->count(),
                'resources_shared' => $student->sharedResources()->count(),
                'forum_topics_created' => $student->forumTopics()->count(),
                'help_requests_posted' => $student->helpRequests()->count(),
                'help_answers_provided' => $student->helpAnswers()->count(),
            ],

            'engagement' => [
                'last_active' => $student->xpTransactions()->latest()->first()?->created_at,
                'actions_this_week' => $student->xpTransactions()
                    ->where('created_at', '>=', now()->startOfWeek())
                    ->count(),
                'streak' => [
                    'attendance' => $student->achievement?->attendance_streak ?? 0,
                    'assignment' => $student->achievement?->assignment_streak ?? 0,
                    'best' => $student->achievement?->best_streak ?? 0,
                ],
            ],
        ]);
    }

    /**
     * Export analytics data
     */
    public function exportData(Request $request)
    {
        $type = $request->get('type', 'overview');
        $schoolId = $request->user()->school_id;

        $data = match($type) {
            'gamification' => $this->getGamificationStats($schoolId),
            'social' => $this->getSocialLearningStats($schoolId),
            'engagement' => $this->getEngagementStats($schoolId),
            default => $this->getOverviewStats($schoolId),
        };

        return response()->json([
            'export_date' => now()->toDateTimeString(),
            'school_id' => $schoolId,
            'type' => $type,
            'data' => $data,
        ]);
    }
}
