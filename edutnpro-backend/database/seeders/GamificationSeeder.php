<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Challenge;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\School;

class GamificationSeeder extends Seeder
{
    /**
     * Seed gamification challenges and initialize student achievements
     */
    public function run(): void
    {
        $this->command->info("<® Initializing Gamification System...");

        // 1. Create default challenges for all schools
        $this->seedChallenges();

        // 2. Initialize achievements for all students
        $this->initializeStudentAchievements();

        $this->command->info(" Gamification system initialized!");
    }

    private function seedChallenges(): void
    {
        $schools = School::all();
        $challengesCreated = 0;

        foreach ($schools as $school) {
            $defaultChallenges = [
                // Daily Challenges
                [
                    'school_id' => $school->id,
                    'name' => 'Perfect Attendance Today',
                    'name_ar' => '-6H1 E+'DJ 'DJHE',
                    'description' => 'Attend all classes today',
                    'description_ar' => '-6H1 ,EJ9 'D-55 'DJHE',
                    'type' => Challenge::TYPE_DAILY,
                    'duration_type' => Challenge::DURATION_DAILY,
                    'target_type' => Challenge::TARGET_ATTENDANCE,
                    'target_value' => 1,
                    'xp_reward' => 50,
                    'is_active' => true,
                ],
                [
                    'school_id' => $school->id,
                    'name' => 'Submit One Assignment',
                    'name_ar' => '*B/JE H',( H'-/',
                    'description' => 'Submit at least one assignment today',
                    'description_ar' => '*B/JE H',( H'-/ 9DI 'D#BD 'DJHE',
                    'type' => Challenge::TYPE_DAILY,
                    'duration_type' => Challenge::DURATION_DAILY,
                    'target_type' => Challenge::TARGET_ASSIGNMENTS,
                    'target_value' => 1,
                    'xp_reward' => 30,
                    'is_active' => true,
                ],

                // Weekly Challenges
                [
                    'school_id' => $school->id,
                    'name' => 'Perfect Week',
                    'name_ar' => '#3(H9 E+'DJ',
                    'description' => '100% attendance this week',
                    'description_ar' => '-6H1 100j G0' 'D#3(H9',
                    'type' => Challenge::TYPE_WEEKLY,
                    'duration_type' => Challenge::DURATION_WEEKLY,
                    'target_type' => Challenge::TARGET_ATTENDANCE,
                    'target_value' => 5,
                    'xp_reward' => 200,
                    'is_active' => true,
                ],
                [
                    'school_id' => $school->id,
                    'name' => 'Assignment Master',
                    'name_ar' => '3J/ 'DH',('*',
                    'description' => 'Submit 5 assignments this week',
                    'description_ar' => '*B/JE 5 H',('* G0' 'D#3(H9',
                    'type' => Challenge::TYPE_WEEKLY,
                    'duration_type' => Challenge::DURATION_WEEKLY,
                    'target_type' => Challenge::TARGET_ASSIGNMENTS,
                    'target_value' => 5,
                    'xp_reward' => 150,
                    'is_active' => true,
                ],
                [
                    'school_id' => $school->id,
                    'name' => 'XP Grinder',
                    'name_ar' => ','E9 'DFB'7',
                    'description' => 'Earn 500 XP this week',
                    'description_ar' => 'C3( 500 FB7) G0' 'D#3(H9',
                    'type' => Challenge::TYPE_WEEKLY,
                    'duration_type' => Challenge::DURATION_WEEKLY,
                    'target_type' => Challenge::TARGET_XP,
                    'target_value' => 500,
                    'xp_reward' => 100,
                    'is_active' => true,
                ],

                // Monthly Challenges
                [
                    'school_id' => $school->id,
                    'name' => 'Monthly Excellence',
                    'name_ar' => ''D*AHB 'D4G1J',
                    'description' => 'Maintain 85%+ average this month',
                    'description_ar' => ''D-A'8 9DI E9/D 85j+ G0' 'D4G1',
                    'type' => Challenge::TYPE_MONTHLY,
                    'duration_type' => Challenge::DURATION_MONTHLY,
                    'target_type' => Challenge::TARGET_GRADES,
                    'target_value' => 85,
                    'xp_reward' => 500,
                    'is_active' => true,
                ],
                [
                    'school_id' => $school->id,
                    'name' => 'No Absence Month',
                    'name_ar' => '4G1 (D' :J'(',
                    'description' => '20 days perfect attendance',
                    'description_ar' => '20 JHE -6H1 E+'DJ',
                    'type' => Challenge::TYPE_MONTHLY,
                    'duration_type' => Challenge::DURATION_MONTHLY,
                    'target_type' => Challenge::TARGET_ATTENDANCE,
                    'target_value' => 20,
                    'xp_reward' => 1000,
                    'is_active' => true,
                ],
            ];

            foreach ($defaultChallenges as $challengeData) {
                Challenge::updateOrCreate(
                    [
                        'school_id' => $challengeData['school_id'],
                        'name' => $challengeData['name'],
                    ],
                    $challengeData
                );
                $challengesCreated++;
            }
        }

        $this->command->info("   Created {$challengesCreated} challenges");
    }

    private function initializeStudentAchievements(): void
    {
        $students = Student::all();
        $initialized = 0;

        foreach ($students as $student) {
            StudentAchievement::firstOrCreate(
                ['student_id' => $student->id],
                [
                    'school_id' => $student->school_id,
                    'total_xp' => 0,
                    'level' => 1,
                    'rank' => 'bronze',
                    'attendance_streak' => 0,
                    'assignment_streak' => 0,
                    'best_streak' => 0,
                ]
            );
            $initialized++;
        }

        $this->command->info("   Initialized achievements for {$initialized} students");
    }
}
