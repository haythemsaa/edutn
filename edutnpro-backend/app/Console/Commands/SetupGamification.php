<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupGamification extends Command
{
    protected $signature = 'edutn:setup-gamification';
    protected $description = 'Setup gamification system (badges, challenges, achievements)';

    public function handle(): int
    {
        $this->info("<® Setting up Gamification System...\n");

        // Seed badges
        $this->info("<Æ Creating badges...");
        Artisan::call('db:seed', ['--class' => 'BadgeSeeder']);
        $this->info(" Badges created");

        // Seed challenges and initialize achievements
        $this->info("<¯ Creating challenges and initializing achievements...");
        Artisan::call('db:seed', ['--class' => 'GamificationSeeder']);
        $this->info(" Gamification setup complete");

        return Command::SUCCESS;
    }
}
