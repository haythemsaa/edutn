<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            SectionsSeeder::class,
            AdminUserSeeder::class,
        ]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('🔐 Login credentials:');
        $this->command->info('   Email: admin@edutnpro.tn');
        $this->command->info('   Password: password');
    }
}
