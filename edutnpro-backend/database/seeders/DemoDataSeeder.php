<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\StudyGroup;
use App\Models\TutorProfile;
use App\Models\SharedResource;
use App\Models\HelpRequest;
use App\Models\CollaborativeNote;
use App\Models\Subject;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed demo data for testing and demonstration
     */
    public function run(): void
    {
        $this->command->info("=Ê Creating Demo Data...");

        if (Student::count() < 5) {
            $this->command->warn("   Not enough students. Need at least 5 students for demo data.");
            return;
        }

        $this->createDemoStudyGroups();
        $this->createDemoTutorProfiles();
        $this->createDemoResources();
        $this->createDemoHelpRequests();
        $this->createDemoNotes();

        $this->command->info(" Demo data created successfully!");
    }

    private function createDemoStudyGroups(): void
    {
        $students = Student::limit(5)->get();
        if ($students->count() < 3) return;

        $subjects = Subject::limit(3)->get();
        $groupsCreated = 0;

        foreach ($subjects as $subject) {
            $creator = $students->random();

            $group = StudyGroup::create([
                'school_id' => $creator->school_id,
                'creator_id' => $creator->id,
                'subject_id' => $subject->id,
                'name' => "Study Group - {$subject->name}",
                'name_ar' => "E,EH9) /1'3) - {$subject->name_ar}",
                'description' => "Let's study {$subject->name} together!",
                'description_ar' => "DF/13 {$subject->name_ar} E9'K!",
                'privacy' => 'public',
                'max_members' => 10,
                'is_active' => true,
            ]);

            // Add creator as admin
            $group->members()->attach($creator->id, [
                'role' => 'admin',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            // Add 2-3 random members
            $members = $students->except($creator->id)->random(rand(2, 3));
            foreach ($members as $member) {
                $group->members()->attach($member->id, [
                    'role' => 'member',
                    'status' => 'active',
                    'joined_at' => now()->subDays(rand(1, 10)),
                ]);
            }

            $groupsCreated++;
        }

        $this->command->info("   Created {$groupsCreated} demo study groups");
    }

    private function createDemoTutorProfiles(): void
    {
        $students = Student::limit(5)->get();
        if ($students->count() < 2) return;

        $subjects = Subject::limit(5)->get();
        $tutorsCreated = 0;

        // Create 2 tutor profiles
        foreach ($students->random(2) as $student) {
            TutorProfile::create([
                'student_id' => $student->id,
                'bio' => "Passionate about helping fellow students succeed!",
                'bio_ar' => "4:HA (E3'9/) 'D7D'( 'D2ED'! 9DI 'DF,'-!",
                'subjects' => $subjects->random(rand(2, 3))->pluck('id')->toArray(),
                'availability' => [
                    'monday' => ['09:00-12:00', '14:00-17:00'],
                    'wednesday' => ['09:00-12:00', '14:00-17:00'],
                    'friday' => ['14:00-17:00'],
                ],
                'average_rating' => rand(40, 50) / 10, // 4.0 - 5.0
                'total_sessions' => rand(5, 20),
                'total_hours' => rand(10, 50),
                'is_verified' => true,
                'is_active' => true,
            ]);
            $tutorsCreated++;
        }

        $this->command->info("   Created {$tutorsCreated} demo tutor profiles");
    }

    private function createDemoResources(): void
    {
        $students = Student::limit(5)->get();
        if ($students->isEmpty()) return;

        $subjects = Subject::limit(3)->get();
        $resourcesCreated = 0;

        $demoResources = [
            [
                'type' => 'link',
                'title' => 'Khan Academy Math Resources',
                'title_ar' => 'EH'1/ Khan Academy DD1J'6J'*',
                'description' => 'Excellent free math tutorials',
                'description_ar' => '/1H3 1J'6J'* E,'FJ) EE*'2)',
                'file_url' => 'https://www.khanacademy.org/math',
                'tags' => ['math', 'tutorial', 'free'],
            ],
            [
                'type' => 'link',
                'title' => 'Science Lab Experiments',
                'title_ar' => '*,'1( 'DE.*(1 'D9DEJ',
                'description' => 'Interactive science experiments',
                'description_ar' => '*,'1( 9DEJ) *A'9DJ)',
                'file_url' => 'https://phet.colorado.edu/',
                'tags' => ['science', 'lab', 'interactive'],
            ],
            [
                'type' => 'document',
                'title' => 'French Grammar Cheat Sheet',
                'title_ar' => 'H1B) :4 BH'9/ 'DD:) 'DA1F3J)',
                'description' => 'Quick reference for French grammar',
                'description_ar' => 'E1,9 31J9 DBH'9/ 'DD:) 'DA1F3J)',
                'tags' => ['french', 'grammar', 'reference'],
            ],
        ];

        foreach ($demoResources as $index => $resource) {
            $student = $students->random();
            $subject = $subjects->random();

            SharedResource::create(array_merge($resource, [
                'school_id' => $student->school_id,
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'visibility' => 'public',
                'downloads_count' => rand(10, 100),
                'views_count' => rand(50, 300),
                'average_rating' => rand(35, 50) / 10,
                'is_verified' => rand(0, 1) == 1,
            ]));
            $resourcesCreated++;
        }

        $this->command->info("   Created {$resourcesCreated} demo resources");
    }

    private function createDemoHelpRequests(): void
    {
        $students = Student::limit(5)->get();
        if ($students->count() < 2) return;

        $subjects = Subject::limit(3)->get();
        $requestsCreated = 0;

        $demoRequests = [
            [
                'title' => 'Need help with quadratic equations',
                'question' => 'I don\'t understand how to solve x² + 5x + 6 = 0. Can someone explain the steps?',
                'urgency' => 'high',
            ],
            [
                'title' => 'French verb conjugation confusion',
                'question' => 'When do I use passé composé vs imparfait? I keep getting confused.',
                'urgency' => 'medium',
            ],
            [
                'title' => 'Physics homework question',
                'question' => 'What\'s the difference between velocity and acceleration?',
                'urgency' => 'medium',
            ],
        ];

        foreach ($demoRequests as $request) {
            $student = $students->random();
            $subject = $subjects->random();

            HelpRequest::create(array_merge($request, [
                'school_id' => $student->school_id,
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'status' => 'open',
            ]));
            $requestsCreated++;
        }

        $this->command->info("   Created {$requestsCreated} demo help requests");
    }

    private function createDemoNotes(): void
    {
        $students = Student::limit(5)->get();
        if ($students->isEmpty()) return;

        $subjects = Subject::limit(3)->get();
        $notesCreated = 0;

        $demoNotes = [
            [
                'title' => 'Chapter 5 Summary - Mathematics',
                'title_ar' => 'ED.5 'DA5D 5 - 'D1J'6J'*',
                'content' => "# Quadratic Equations\n\n## Standard Form\nax² + bx + c = 0\n\n## Solutions\nx = (-b ± (b²-4ac)) / 2a\n\n## Key Points\n- Discriminant: b²-4ac\n- Two solutions if > 0\n- One solution if = 0\n- No real solutions if < 0",
                'access_level' => 'public',
            ],
            [
                'title' => 'French Verb Tenses',
                'title_ar' => '#2EF) 'D#A9'D 'DA1F3J)',
                'content' => "# Les Temps Verbaux\n\n## Présent\n- Actions habituelles\n- États actuels\n\n## Passé Composé\n- Actions complétées dans le passé\n- Avec avoir ou être\n\n## Imparfait\n- Actions habituelles passées\n- Descriptions",
                'access_level' => 'public',
            ],
        ];

        foreach ($demoNotes as $note) {
            $student = $students->random();
            $subject = $subjects->random();

            CollaborativeNote::create(array_merge($note, [
                'school_id' => $student->school_id,
                'subject_id' => $subject->id,
                'creator_id' => $student->id,
                'class_id' => $student->class_id,
                'contributors' => [$student->id],
                'version' => 1,
            ]));
            $notesCreated++;
        }

        $this->command->info("   Created {$notesCreated} demo collaborative notes");
    }
}
