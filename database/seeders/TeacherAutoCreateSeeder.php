<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TeacherAutoCreateSeeder extends Seeder
{
    public function run(): void
    {
        // Liste des emails
        $emails = [
            "amadouabdoulbassith@gmail.com",
            "fadalicsma@gmail.com",
            "hessoujosue6726@gmail.com",
            "oscar.godonou@yahoo.com",
            "aguemonconstant1@gmail.com",
            "fabiendavo@gmail.com",
            "haroldthossou@gmail.com",
            "okegouc@gmail.com",
            "ali87charles@gmail.com",
            "florenttitekoun316@gmail.com",
            "ahozegni80@gmail.com",
            "ulvagoss@gmail.com",
            "sebastiendinok@gmail.com",
            "aimetantchinita@gmail.com",
            "edahkingslim@gmail.com",
            "maximehounkanlo0@gmail.com",
            "sourouavoc@gmail.com",
            "astrid1200@gmail.com",
        ];

        foreach ($emails as $email) {

            // Extraire le nom avant @
            $namePart = explode('@', $email)[0];
            $name = ucfirst($namePart);

            // Vérifier si le user existe déjà
            $existingUser = DB::table('users')->where('email', $email)->first();

            if ($existingUser) {
                // Supprimer d'abord l'invitation liée
                DB::table('teacher_invitations')->where('user_id', $existingUser->id)->delete();

                // Supprimer le user
                DB::table('users')->where('id', $existingUser->id)->delete();
            }

            // Insérer le nouveau user
            $userId = DB::table('users')->insertGetId([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make("12345678"),
                'role_id' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insérer l'invitation
            DB::table('teacher_invitations')->insert([
                'user_id' => $userId,
                'censeur_id' => 4,
                'token' => Str::random(32),
                'accepted' => true,
                'academic_year_id' => 1,
                'accepted_at' => Carbon::now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
