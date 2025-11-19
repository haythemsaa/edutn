<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;

class BadgeSeeder extends Seeder
{
    /**
     * Seed default badges for gamification system
     */
    public function run(): void
    {
        $badges = Badge::getDefaultBadges();

        $created = 0;
        foreach ($badges as $badgeData) {
            Badge::updateOrCreate(
                ['name' => $badgeData['name']],
                array_merge($badgeData, ['is_active' => true])
            );
            $created++;
        }

        $this->command->info(" Created/Updated {$created} badges successfully!");
    }
}
