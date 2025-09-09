<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FounderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $user = \App\Models\User::firstOrCreate(
            ['email'=>'admin@mariealain.bj'],
            ['name'=>'Fondateur MARIE ALAIN','password'=>bcrypt('ChangeMe123!')]
        );

        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name'=>'founder']);
        $user->assignRole($role);
    }

}
