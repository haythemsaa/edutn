<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@edutnpro.tn',
            'password' => Hash::make('password'),
            'phone' => '+216 70 000 000',
            'is_active' => true,
        ]);
        $superAdmin->assignRole('super_admin');

        // Create Demo Admin
        $admin = User::create([
            'name' => 'Demo Admin',
            'email' => 'demo@edutnpro.tn',
            'password' => Hash::make('password'),
            'phone' => '+216 70 000 001',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Create Demo Teacher
        $teacher = User::create([
            'name' => 'Demo Teacher',
            'email' => 'teacher@edutnpro.tn',
            'password' => Hash::make('password'),
            'phone' => '+216 70 000 002',
            'is_active' => true,
        ]);
        $teacher->assignRole('teacher');

        $this->command->info('Admin users created successfully!');
        $this->command->info('Email: admin@edutnpro.tn | Password: password');
    }
}