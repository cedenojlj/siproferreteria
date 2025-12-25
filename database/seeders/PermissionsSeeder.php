<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        // Create the core permission
        $manageRolesPermission = Permission::firstOrCreate(['name' => 'manage_roles',
            'slug' => 'manage_roles',
            'description' => 'Permission to manage user roles and permissions.']);

        // Create a Super Admin role
        $superAdminRole = Role::firstOrCreate(['name' => 'Super_Admin',
            'slug' => 'super_admin',
            'description' => 'Role with all permissions, typically assigned to the highest level admin users.']);
        
        // Give Super Admin all permissions, or just the specific one
        // For a robust system, a Super Admin should probably have all permissions.
        // We can achieve this by using a Gate::before rule in AuthServiceProvider,
        // but for now, we'll assign the specific permission.
        $superAdminRole->permissions()->attach($manageRolesPermission->id);

        // Find and assign the Super Admin role to the first user
        // In a real application, you might have a specific admin user to assign this to.
        $user = User::first();
        if ($user) {
            $user->assignRole($superAdminRole);
        }
    }
}
