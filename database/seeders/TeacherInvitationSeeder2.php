<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TeacherInvitationSeeder2 extends Seeder
{
    public function run(){
        $emails = [
            'donatienzounma@gmail.com',
            'wencesfr@gmail.com',
        ];

        foreach ($emails as $email) {
            $namePart = ucfirst(strtok($email, '@')); // Partie avant le @, première lettre en majuscule

            // Insertion dans la table users
            $userId = DB::table('users')->insertGetId([
                'name' => $namePart,
                'email' => $email,
                'password' => Hash::make('12345678'),
                'role_id' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insertion dans teacher_invitations
            DB::table('teacher_invitations')->insert([
                'user_id' => $userId,
                'censeur_id' => 4,
                'token' => Str::random(40),
                'accepted' => true,
                'academic_year_id' => 1,
                'accepted_at' => Carbon::today(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
