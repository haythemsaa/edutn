<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionsSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'name_ar' => 'علوم',
                'name_fr' => 'Sciences',
                'code' => 'SCI',
                'description' => 'Section Sciences Expérimentales',
            ],
            [
                'name_ar' => 'رياضيات',
                'name_fr' => 'Mathématiques',
                'code' => 'MATH',
                'description' => 'Section Mathématiques',
            ],
            [
                'name_ar' => 'آداب',
                'name_fr' => 'Lettres',
                'code' => 'LETT',
                'description' => 'Section Lettres',
            ],
            [
                'name_ar' => 'اقتصاد وتصرف',
                'name_fr' => 'Économie et Gestion',
                'code' => 'ECO',
                'description' => 'Section Économie et Gestion',
            ],
            [
                'name_ar' => 'تقنية',
                'name_fr' => 'Technique',
                'code' => 'TECH',
                'description' => 'Section Technique',
            ],
            [
                'name_ar' => 'إعلامية',
                'name_fr' => 'Informatique',
                'code' => 'INFO',
                'description' => 'Section Informatique',
            ],
            [
                'name_ar' => 'رياضة',
                'name_fr' => 'Sport',
                'code' => 'SPORT',
                'description' => 'Section Sport',
            ],
        ];

        foreach ($sections as $section) {
            Section::create($section);
        }

        $this->command->info('Sections tunisiennes created successfully!');
    }
}