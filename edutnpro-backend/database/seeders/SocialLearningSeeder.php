<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubjectForum;
use App\Models\Subject;
use App\Models\School;

class SocialLearningSeeder extends Seeder
{
    /**
     * Seed subject forums for social learning
     */
    public function run(): void
    {
        $this->command->info("> Initializing Social Learning Platform...");

        $this->createSubjectForums();

        $this->command->info(" Social learning platform initialized!");
    }

    private function createSubjectForums(): void
    {
        $schools = School::all();
        $forumsCreated = 0;

        foreach ($schools as $school) {
            $subjects = Subject::where('school_id', $school->id)->get();

            foreach ($subjects as $subject) {
                SubjectForum::firstOrCreate(
                    [
                        'school_id' => $school->id,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'name' => "Forum {$subject->name}",
                        'name_ar' => "EF*/I {$subject->name_ar}",
                        'description' => "Discussion forum for {$subject->name}",
                        'description_ar' => "EF*/I 'DFB'4 D {$subject->name_ar}",
                        'is_active' => true,
                        'moderation_enabled' => true,
                    ]
                );
                $forumsCreated++;
            }
        }

        $this->command->info("   Created {$forumsCreated} subject forums");
    }
}
