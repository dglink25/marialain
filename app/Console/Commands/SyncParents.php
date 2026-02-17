<?php

namespace App\Console\Commands;

use App\Models\ParentUser;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SyncParents extends Command
{
    protected $signature = 'parents:sync';
    protected $description = 'Synchronise les parents à partir des données des élèves';

    public function handle() {
        $this->info('Début de la synchronisation des parents...');

        $parentPhones = Student::whereNotNull('parent_phone')
            ->distinct()
            ->pluck('parent_phone');

        $bar = $this->output->createProgressBar(count($parentPhones));
        $bar->start();

        $created = 0;
        $updated = 0;

        foreach ($parentPhones as $rawPhone) {

            // Nettoyage du numéro
            $phone = $this->normalizePhone($rawPhone);

            if (!$phone) {
                $bar->advance();
                continue;
            }

            $student = Student::where('parent_phone', $rawPhone)->first();
            if (!$student) continue;

            $parentName = trim($student->parent_full_name ?? 'Parent ' . $phone);
            $parentEmail = $student->parent_email ?? null;

            $parent = ParentUser::updateOrCreate(
                ['phone' => $phone],
                [
                    'full_name' => $parentName,
                    'email' => $parentEmail,
                    'password' => Hash::make('12345678'),
                ]
            );

            if ($parent->wasRecentlyCreated) {
                $created++;
                $this->line("\n✓ Parent créé : {$parent->full_name} ({$phone})");
            } else {
                $updated++;
            }

            $bar->advance();
        }

        $bar->finish();

        $this->newLine(2);
        $this->info("Synchronisation terminée !");
        $this->table(
            ['Statut', 'Nombre'],
            [
                ['Créés', $created],
                ['Mis à jour', $updated],
                ['Total', count($parentPhones)],
            ]
        );
    }


    private function normalizePhone($phone) {
        // Supprimer tous les caractères non numériques
        $phone = preg_replace('/\D/', '', $phone);

        // Supprimer indicatif pays 229 s'il existe
        if (str_starts_with($phone, '229')) {
            $phone = substr($phone, 3);
        }

        // Si 8 chiffres → ajouter 01 devant
        if (strlen($phone) === 8) {
            $phone = '01' . $phone;
        }

        // Si 9 chiffres et commence par 1 → ajouter 0 devant
        if (strlen($phone) === 9 && str_starts_with($phone, '1')) {
            $phone = '0' . $phone;
        }

        // Si pas exactement 10 chiffres → invalide
        if (strlen($phone) !== 10) {
            return null;
        }

        return $phone;
    }
}