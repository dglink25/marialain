<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class FounderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(){

        $user = User::firstOrCreate(
            ['email' => 'admin@mariealain.bj'],
            [
                'name' => 'Fondateur MARIE ALAIN',
                'password' => bcrypt('ChangeMe123!'),
            ]
        );
        $role = Role::firstOrCreate(['name' => 'founder']);
        if (!$user->hasRole($role->name)) {
            $user->assignRole($role);
        }
    }
}
