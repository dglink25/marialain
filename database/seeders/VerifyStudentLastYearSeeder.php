<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * VerifyStudentLastYearSeeder
 * ============================================================
 * Vérifie que pour chaque élève dans student_academic_records,
 * le champ students.academic_year_id + students.class_id
 * correspond bien à la DERNIÈRE année du cursus de l'élève.
 *
 * Ce script est en READ-ONLY par défaut (mode vérification).
 * Passer $applyFix = true pour appliquer les corrections.
 *
 * Règles :
 *  - Si un élève a un cursus dans SAR et son student.academic_year_id
 *    pointe vers une année plus ancienne que son dernier SAR :
 *    → INCOHÉRENCE → corriger student pour pointer vers la dernière
 *    année du cursus (mais JAMAIS vers 2025-2026 si ce n'est pas
 *    dans le cursus SAR).
 *  - Les élèves de 2025-2026 (academic_year_id=1) ne sont PAS touchés.
 * ============================================================
 */
class VerifyStudentLastYearSeeder extends Seeder
{
    /** ID de l'année active 2025-2026 — NE PAS TOUCHER */
    private const ACTIVE_YEAR_ID = 1;

    /**
     * Mettre à true pour appliquer les corrections automatiquement.
     * En false, le script affiche juste les incohérences.
     */
    private bool $applyFix = false;

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════╗');
        $this->command->info('║   VerifyStudentLastYearSeeder                        ║');
        $this->command->info("║   Mode : " . ($this->applyFix ? 'CORRECTION' : 'VÉRIFICATION SEULE') . str_repeat(' ', $this->applyFix ? 39 : 34) . '║');
        $this->command->info('╚══════════════════════════════════════════════════════╝');
        $this->command->info('');

        if (!DB::getSchemaBuilder()->hasTable('student_academic_records')) {
            $this->command->error('Table student_academic_records introuvable. Migrez d\'abord.');
            return;
        }

        $totalSAR = DB::table('student_academic_records')->count();
        if ($totalSAR === 0) {
            $this->command->warn('student_academic_records est vide. Lancez BuildHistoricalCursusSeeder d\'abord.');
            return;
        }

        $this->command->info("  student_academic_records contient : {$totalSAR} enregistrements");
        $this->command->info('');

        // ── Charger le dernier year_id par élève dans SAR ─────────────────
        // "dernier" = l'année avec le plus grand id (le plus récent)
        $dernierParEleve = DB::table('student_academic_records')
            ->select('student_id', DB::raw('MAX(academic_year_id) as dernier_year_id'))
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        // ── Charger tous les students hors 2025-2026 ──────────────────────
        $students = DB::table('students')
            ->where('academic_year_id', '!=', self::ACTIVE_YEAR_ID)
            ->select('id', 'academic_year_id', 'class_id', 'first_name', 'last_name')
            ->get();

        $incoherences = [];
        $ok = 0;

        foreach ($students as $student) {
            $dernier = $dernierParEleve->get($student->id);

            if (!$dernier) {
                // Élève non couvert dans SAR → signaler
                $incoherences[] = [
                    'type'       => 'NON_DANS_SAR',
                    'student_id' => $student->id,
                    'nom'        => $student->last_name . ' ' . $student->first_name,
                    'actuel'     => "year={$student->academic_year_id} class={$student->class_id}",
                    'attendu'    => 'aucun cursus SAR',
                ];
                continue;
            }

            $dernierYearId = $dernier->dernier_year_id;

            if ($student->academic_year_id != $dernierYearId) {
                // Récupérer la class_id de ce dernier year dans SAR
                $dernierRecord = DB::table('student_academic_records')
                    ->where('student_id', $student->id)
                    ->where('academic_year_id', $dernierYearId)
                    ->first();

                $incoherences[] = [
                    'type'              => 'YEAR_INCORRECT',
                    'student_id'        => $student->id,
                    'nom'               => $student->last_name . ' ' . $student->first_name,
                    'actuel'            => "year={$student->academic_year_id} class={$student->class_id}",
                    'attendu'           => "year={$dernierYearId} class=" . ($dernierRecord->class_id ?? '?'),
                    'new_year_id'       => $dernierYearId,
                    'new_class_id'      => $dernierRecord->class_id ?? $student->class_id,
                ];
            } else {
                $ok++;
            }
        }

        // ── Afficher les résultats ────────────────────────────────────────
        $this->command->info("  ✓ Cohérents : {$ok}");
        $this->command->info("  ⚠ Incohérences : " . count($incoherences));
        $this->command->info('');

        if (empty($incoherences)) {
            $this->command->info('✅ Tout est cohérent. Aucune correction nécessaire.');
            return;
        }

        // Afficher le détail
        $this->command->table(
            ['Type', 'ID Élève', 'Nom', 'Actuel', 'Attendu'],
            array_map(fn($i) => [
                $i['type'],
                $i['student_id'],
                $i['nom'],
                $i['actuel'],
                $i['attendu'],
            ], array_slice($incoherences, 0, 50)) // max 50 lignes affichées
        );

        if (count($incoherences) > 50) {
            $this->command->warn('... et ' . (count($incoherences) - 50) . ' autres.');
        }

        // ── Appliquer les corrections si demandé ──────────────────────────
        if ($this->applyFix) {
            $corriges = 0;
            foreach ($incoherences as $i) {
                if ($i['type'] === 'YEAR_INCORRECT') {
                    DB::table('students')
                        ->where('id', $i['student_id'])
                        ->update([
                            'academic_year_id' => $i['new_year_id'],
                            'class_id'         => $i['new_class_id'],
                            'updated_at'       => now(),
                        ]);
                    $corriges++;
                }
            }
            $this->command->info("✅ {$corriges} élève(s) corrigé(s).");
        } else {
            $this->command->warn('Mode vérification seule. Pour appliquer les corrections :');
            $this->command->warn('  → Mettre $applyFix = true dans ce seeder');
            $this->command->warn('  → Puis relancer : php artisan db:seed --class=VerifyStudentLastYearSeeder');
        }
    }
}