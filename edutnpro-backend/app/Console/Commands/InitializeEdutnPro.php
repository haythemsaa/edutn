<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InitializeEdutnPro extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'edutn:init {--demo : Include demo data}';

    /**
     * The console command description.
     */
    protected $description = 'Initialize EDUTN PRO system with all innovation features';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("=€ Initializing EDUTN PRO System...\n");

        // Step 1: Run migrations
        $this->info("=æ Running database migrations...");
        Artisan::call('migrate', ['--force' => true]);
        $this->info(" Migrations completed\n");

        // Step 2: Setup Gamification
        $this->info("<® Setting up Gamification System...");
        Artisan::call('edutn:setup-gamification');
        $this->line(Artisan::output());

        // Step 3: Setup Social Learning
        $this->info("> Setting up Social Learning Platform...");
        Artisan::call('edutn:setup-social');
        $this->line(Artisan::output());

        // Step 4: Demo Data (optional)
        if ($this->option('demo')) {
            $this->info("=Ê Creating demo data...");
            Artisan::call('db:seed', ['--class' => 'DemoDataSeeder']);
            $this->info(" Demo data created\n");
        }

        // Success summary
        $this->newLine();
        $this->info("TPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPW");
        $this->info("Q   <‰ EDUTN PRO INITIALIZED SUCCESSFULLY! Q");
        $this->info("ZPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPPP]");
        $this->newLine();

        $this->table(
            ['Feature', 'Status'],
            [
                ['Database Migrations', ' Complete'],
                ['Gamification System', ' Complete'],
                ['Social Learning', ' Complete'],
                ['Demo Data', $this->option('demo') ? ' Complete' : 'í  Skipped'],
            ]
        );

        $this->newLine();
        $this->info("=Ú Next Steps:");
        $this->line("  1. Access the API at: /api/student/*");
        $this->line("  2. View gamification endpoints: /api/student/achievement");
        $this->line("  3. Browse study groups: /api/student/study-groups");
        $this->line("  4. Check leaderboard: /api/student/leaderboard");
        $this->newLine();

        return Command::SUCCESS;
    }
}
