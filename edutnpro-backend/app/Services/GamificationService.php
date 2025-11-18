<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\Badge;
use App\Models\StudentBadge;
use App\Models\XpTransaction;
use App\Models\Attendance;
use App\Models\Assignment;
use App\Models\Grade;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    /**
     * Handle attendance XP award
     */
    public function onAttendance(Attendance $attendance): void
    {
        if ($attendance->status !== 'present') {
            return;
        }

        $student = $attendance->student;
        $achievement = $this->getOrCreateAchievement($student);

        // Award attendance XP
        $achievement->addXP(
            XpTransaction::XP_ATTENDANCE,
            XpTransaction::TYPE_EARNED,
            XpTransaction::SOURCE_ATTENDANCE,
            $attendance,
            "Présence marquée"
        );

        // Update attendance streak
        $this->updateAttendanceStreak($student);

        // Check for badges
        $this->checkAndAwardBadges($student);
    }

    /**
     * Handle assignment submission XP award
     */
    public function onAssignmentSubmit(Assignment $assignment, Student $student, $submittedAt): void
    {
        $achievement = $this->getOrCreateAchievement($student);

        // Determine XP amount based on submission timing
        $xpAmount = XpTransaction::XP_ASSIGNMENT_SUBMIT;
        $description = "Devoir soumis";

        if ($assignment->due_date && $submittedAt < $assignment->due_date) {
            $xpAmount = XpTransaction::XP_ASSIGNMENT_EARLY;
            $description = "Devoir soumis en avance";
        }

        $achievement->addXP(
            $xpAmount,
            XpTransaction::TYPE_EARNED,
            XpTransaction::SOURCE_ASSIGNMENT,
            $assignment,
            $description
        );

        // Update assignment streak
        $this->updateAssignmentStreak($student);

        // Check for badges
        $this->checkAndAwardBadges($student);
    }

    /**
     * Handle grade XP award
     */
    public function onGradeAdded(Grade $grade): void
    {
        $student = $grade->student;
        $achievement = $this->getOrCreateAchievement($student);

        $percentage = ($grade->grade / $grade->max_grade) * 100;

        // Determine XP amount based on grade
        [$xpAmount, $description] = match (true) {
            $percentage == 100 => [
                XpTransaction::XP_PERFECT_SCORE,
                "Note parfaite: {$grade->grade}/{$grade->max_grade}"
            ],
            $percentage >= 90 => [
                XpTransaction::XP_GRADE_EXCELLENT,
                "Excellente note: {$grade->grade}/{$grade->max_grade}"
            ],
            $percentage >= 80 => [
                XpTransaction::XP_GRADE_GOOD,
                "Bonne note: {$grade->grade}/{$grade->max_grade}"
            ],
            $percentage >= 70 => [
                XpTransaction::XP_GRADE_AVERAGE,
                "Note satisfaisante: {$grade->grade}/{$grade->max_grade}"
            ],
            default => [0, null],
        };

        if ($xpAmount > 0) {
            $achievement->addXP(
                $xpAmount,
                XpTransaction::TYPE_EARNED,
                XpTransaction::SOURCE_GRADE,
                $grade,
                $description
            );
        }

        // Check for improvement
        $this->checkGradeImprovement($student, $grade);

        // Check for badges
        $this->checkAndAwardBadges($student);
    }

    /**
     * Update attendance streak
     */
    private function updateAttendanceStreak(Student $student): void
    {
        $achievement = $this->getOrCreateAchievement($student);

        // Check if yesterday was also present
        $yesterday = now()->subDay();
        $wasYesterdayPresent = Attendance::where('student_id', $student->id)
            ->whereDate('date', $yesterday)
            ->where('status', 'present')
            ->exists();

        if ($wasYesterdayPresent) {
            $achievement->updateStreak('attendance', true);

            // Check for streak bonuses
            if ($achievement->attendance_streak % 5 == 0 && $achievement->attendance_streak >= 5) {
                $xpBonus = XpTransaction::XP_STREAK_WEEK;
                if ($achievement->attendance_streak >= 20) {
                    $xpBonus = XpTransaction::XP_STREAK_MONTH;
                }

                $achievement->addXP(
                    $xpBonus,
                    XpTransaction::TYPE_BONUS,
                    XpTransaction::SOURCE_STREAK,
                    null,
                    "Bonus série: {$achievement->attendance_streak} jours consécutifs"
                );
            }
        }
    }

    /**
     * Update assignment streak
     */
    private function updateAssignmentStreak(Student $student): void
    {
        $achievement = $this->getOrCreateAchievement($student);
        $achievement->updateStreak('assignment', true);

        // Check for streak bonuses
        if ($achievement->assignment_streak % 5 == 0 && $achievement->assignment_streak >= 5) {
            $achievement->addXP(
                XpTransaction::XP_STREAK_WEEK,
                XpTransaction::TYPE_BONUS,
                XpTransaction::SOURCE_STREAK,
                null,
                "Bonus série: {$achievement->assignment_streak} devoirs consécutifs"
            );
        }
    }

    /**
     * Check for grade improvement
     */
    private function checkGradeImprovement(Student $student, Grade $newGrade): void
    {
        // Find previous grade for same subject
        $previousGrade = Grade::where('student_id', $student->id)
            ->where('subject_id', $newGrade->subject_id)
            ->where('id', '!=', $newGrade->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$previousGrade) {
            return;
        }

        $previousPercentage = ($previousGrade->grade / $previousGrade->max_grade) * 100;
        $newPercentage = ($newGrade->grade / $newGrade->max_grade) * 100;

        $improvement = $newPercentage - $previousPercentage;

        if ($improvement >= 10) {
            $achievement = $this->getOrCreateAchievement($student);
            $achievement->addXP(
                XpTransaction::XP_IMPROVED_GRADE,
                XpTransaction::TYPE_BONUS,
                XpTransaction::SOURCE_GRADE,
                $newGrade,
                "Amélioration de {$improvement}% en {$newGrade->subject->name}"
            );
        }
    }

    /**
     * Check and award eligible badges
     */
    public function checkAndAwardBadges(Student $student): void
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

            if ($this->checkBadgeCriteria($student, $badge)) {
                $this->awardBadge($student, $badge);
            }
        }
    }

    /**
     * Check if student meets badge criteria
     */
    private function checkBadgeCriteria(Student $student, Badge $badge): bool
    {
        $achievement = $student->achievement;
        $criteria = $badge->criteria;

        return match ($criteria['type']) {
            'total_xp' => $achievement->total_xp >= $criteria['value'],
            'level' => $achievement->level >= $criteria['value'],
            'attendance_streak' => $achievement->attendance_streak >= $criteria['value'],

            'attendance_days' => Attendance::where('student_id', $student->id)
                ->where('status', 'present')
                ->count() >= $criteria['value'],

            'assignments_submitted' => DB::table('assignment_submissions')
                ->where('student_id', $student->id)
                ->count() >= $criteria['value'],

            'perfect_scores' => Grade::where('student_id', $student->id)
                ->whereRaw('grade = max_grade')
                ->count() >= $criteria['value'],

            'average_above' => $this->getStudentAverage($student) >= $criteria['value'],

            'class_rank' => $this->getClassRank($student) == $criteria['value'],

            default => false,
        };
    }

    /**
     * Award badge to student
     */
    private function awardBadge(Student $student, Badge $badge): void
    {
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

        $achievement = $student->achievement;
        $achievement->addXP(
            $xpBonus,
            XpTransaction::TYPE_ACHIEVEMENT,
            XpTransaction::SOURCE_BADGE,
            $badge,
            "Badge débloqué: {$badge->name}"
        );
    }

    /**
     * Get or create student achievement
     */
    private function getOrCreateAchievement(Student $student): StudentAchievement
    {
        return StudentAchievement::firstOrCreate(
            ['student_id' => $student->id],
            [
                'school_id' => $student->school_id,
                'total_xp' => 0,
                'level' => 1,
                'rank' => 'bronze',
            ]
        );
    }

    /**
     * Get student overall average
     */
    private function getStudentAverage(Student $student): float
    {
        $average = DB::table('grades')
            ->where('student_id', $student->id)
            ->selectRaw('AVG((grade / max_grade) * 100) as avg')
            ->value('avg');

        return $average ?? 0;
    }

    /**
     * Get student class rank
     */
    private function getClassRank(Student $student): int
    {
        if (!$student->class_id) {
            return 0;
        }

        // Get all students in class with their averages
        $classAverages = DB::table('students')
            ->where('class_id', $student->class_id)
            ->leftJoin('grades', 'students.id', '=', 'grades.student_id')
            ->select('students.id', DB::raw('AVG((grades.grade / grades.max_grade) * 100) as avg'))
            ->groupBy('students.id')
            ->orderByDesc('avg')
            ->get();

        $rank = 1;
        foreach ($classAverages as $index => $studentAvg) {
            if ($studentAvg->id == $student->id) {
                return $index + 1;
            }
        }

        return 0;
    }

    /**
     * Initialize gamification for all existing students
     */
    public function initializeForAllStudents(): int
    {
        $students = Student::all();
        $count = 0;

        foreach ($students as $student) {
            $achievement = StudentAchievement::firstOrCreate(
                ['student_id' => $student->id],
                [
                    'school_id' => $student->school_id,
                    'total_xp' => 0,
                    'level' => 1,
                    'rank' => 'bronze',
                ]
            );

            // Retroactively award XP for past activities
            $this->retroactiveXPAward($student);

            $count++;
        }

        return $count;
    }

    /**
     * Retroactively award XP for past activities
     */
    private function retroactiveXPAward(Student $student): void
    {
        $achievement = $student->achievement;
        if (!$achievement) {
            return;
        }

        // Count past attendances
        $attendanceCount = Attendance::where('student_id', $student->id)
            ->where('status', 'present')
            ->count();

        // Count past assignments
        $assignmentCount = DB::table('assignment_submissions')
            ->where('student_id', $student->id)
            ->count();

        // Count past grades
        $goodGrades = Grade::where('student_id', $student->id)
            ->whereRaw('(grade / max_grade) >= 0.8')
            ->count();

        // Award retroactive XP (use lower amounts to not over-reward)
        $retroactiveXP =
            ($attendanceCount * 5) +  // 5 XP per attendance
            ($assignmentCount * 10) + // 10 XP per assignment
            ($goodGrades * 15);        // 15 XP per good grade

        if ($retroactiveXP > 0) {
            $achievement->addXP(
                $retroactiveXP,
                XpTransaction::TYPE_BONUS,
                XpTransaction::SOURCE_MANUAL,
                null,
                "Bonus rétroactif pour activités passées"
            );
        }
    }
}
