<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Schools
            'view-schools', 'create-schools', 'edit-schools', 'delete-schools',
            // Students
            'view-students', 'create-students', 'edit-students', 'delete-students',
            // Teachers
            'view-teachers', 'create-teachers', 'edit-teachers', 'delete-teachers',
            // Grades
            'view-grades', 'create-grades', 'edit-grades', 'delete-grades',
            // Classes
            'view-classes', 'create-classes', 'edit-classes', 'delete-classes',
            // Subjects
            'view-subjects', 'create-subjects', 'edit-subjects', 'delete-subjects',
            // Reports
            'view-reports', 'generate-reports',
            // System
            'manage-users', 'manage-roles', 'manage-permissions', 'manage-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'view-schools', 'edit-schools',
            'view-students', 'create-students', 'edit-students',
            'view-teachers', 'create-teachers', 'edit-teachers',
            'view-grades', 'create-grades', 'edit-grades',
            'view-classes', 'create-classes', 'edit-classes',
            'view-subjects', 'create-subjects', 'edit-subjects',
            'view-reports', 'generate-reports',
        ]);

        $teacher = Role::create(['name' => 'teacher']);
        $teacher->givePermissionTo([
            'view-students',
            'view-grades', 'create-grades', 'edit-grades',
            'view-classes',
            'view-subjects',
        ]);

        $student = Role::create(['name' => 'student']);
        $student->givePermissionTo([
            'view-grades',
        ]);

        $parent = Role::create(['name' => 'parent']);
        $parent->givePermissionTo([
            'view-students',
            'view-grades',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}