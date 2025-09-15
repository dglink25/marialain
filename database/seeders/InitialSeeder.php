<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class InitialSeeder extends Seeder
{
    public function run()
    {
        // Créer les rôles sans doublons
        $roles = [
            ['name' => 'super_admin', 'display_name' => 'Super Administrateur'],
            ['name' => 'directeur_primaire', 'display_name' => 'Directeur Primaire'],
            ['name' => 'censeur', 'display_name' => 'Censeur'],
            ['name' => 'surveillant', 'display_name' => 'Surveillant'],
            ['name' => 'secretaire', 'display_name' => 'Secrétaire'],
            ['name' => 'enseignant', 'display_name' => 'Enseignant'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']], // Condition
                ['display_name' => $role['display_name']] // Valeur si nouveau
            );
        }
        
        $entities = [
            ['slug' => 'maternelle', 'name' => 'Maternelle'],
            ['slug' => 'primaire', 'name' => 'Primaire'],
            ['slug' => 'secondaire', 'name' => 'Secondaire'],
        ];

        foreach ($entities as $entity) {
            Entity::firstOrCreate(
                ['slug' => $entity['slug']],
                ['name' => $entity['name']]
            );
        }
        User::firstOrCreate(
            ['email' => 'admin@mariealain'],
            [
                'name' => 'Admin MARI ALAIN',
                'password' => Hash::make('12345678'),
                'role_id' => Role::where('name', 'super_admin')->first()->id,
            ]
        );
    }
}
