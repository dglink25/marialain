<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Réinitialiser le cache des permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Créer les permissions principales
        $permissions = [
            'manage schools',
            'manage classes',
            'manage invitations',
            'manage users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Créer les rôles
        $roles = [
            'founder',
            'admin',
            'censeur',
            'secretaire',
            'directeur_primaire',
            'surveillant',
            'teacher',
            'parent',
            'student',
        ];

        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            if ($roleName === 'founder') {
                $role->givePermissionTo(Permission::all()); 
            }

            if ($roleName === 'admin') {
                $role->givePermissionTo(['manage schools', 'manage classes', 'manage invitations']);
            }

            if ($roleName === 'teacher') {
                $role->givePermissionTo(['manage classes']);
            }

            
        }
    }
}
