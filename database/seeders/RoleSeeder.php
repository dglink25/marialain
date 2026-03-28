<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder {
    public function run(): void {
        $roles = [
            'directeur_primaire',
            'censeur',
            'surveillant',
            'secretaire'
        ];

        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate([
                    'name' => $roleName ,
                    'display_name' => 'Directeur Primaire'

        ]);

            User::firstOrCreate(
                ['email' => $roleName.'@gmail.com'],
                [
                    'name' => ucfirst(str_replace('_',' ',$roleName)),
                    'password' => Hash::make('12345678'),
                    'role_id' => $role->id, 
                ]
            );
        }
    }
}

