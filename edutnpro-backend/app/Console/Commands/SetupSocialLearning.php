<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupSocialLearning extends Command
{
    protected $signature = 'edutn:setup-social';
    protected $description = 'Setup social learning platform (forums, study groups)';

    public function handle(): int
    {
        $this->info("> Setting up Social Learning Platform...\n");

        // Seed subject forums
        $this->info("=¬ Creating subject forums...");
        Artisan::call('db:seed', ['--class' => 'SocialLearningSeeder']);
        $this->info(" Social learning setup complete");

        return Command::SUCCESS;
    }
}
