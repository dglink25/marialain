<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $roles = ['founder','admin','censeur','secretaire','directeur_primaire','surveillant','teacher','parent','student'];
        foreach ($roles as $r) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name'=>$r]);
        }

    }

}
