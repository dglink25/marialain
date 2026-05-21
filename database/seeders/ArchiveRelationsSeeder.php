<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArchiveRelationsSeeder extends Seeder{
    
    private const SOURCE_YEAR_ID = 1;

    private const TARGET_YEAR_IDS = [3, 4, 5, 6, 7, 8, 9, 10];

    public function run(): void {
        $this->command->info('══════════════════════════════════════════════════');
        $this->command->info('  ArchiveRelationsSeeder – Démarrage');
        $this->command->info('══════════════════════════════════════════════════');

        // ── 1. Charger les données source (année 2025-2026) ──────────────────

        /** @var array<int,object>  $sourceClasses  id => {id, name} */
        $sourceClasses = DB::table('classes')
            ->where('academic_year_id', self::SOURCE_YEAR_ID)
            ->get()
            ->keyBy('id');

        /** Mapping nom_classe => class_id pour l'année source */
        $sourceByName = $sourceClasses->mapWithKeys(fn($c) => [$c->name => $c->id]);

        /** CTS source, groupés par class_id */
        $sourceCTS = DB::table('class_teacher_subject')
            ->where('academic_year_id', self::SOURCE_YEAR_ID)
            ->get()
            ->groupBy('class_id');

        /** Timetables source, groupés par class_id */
        $sourceTimetables = DB::table('timetables')
            ->where('academic_year_id', self::SOURCE_YEAR_ID)
            ->get()
            ->groupBy('class_id');

        $this->command->info("  Source : " . $sourceClasses->count() . " classes, "
            . $sourceCTS->flatten()->count() . " CTS, "
            . $sourceTimetables->flatten()->count() . " timetables");
        $this->command->newLine();

        // ── 2. Traiter chaque année cible ────────────────────────────────────

        foreach (self::TARGET_YEAR_IDS as $targetYearId) {
            $this->processYear(
                $targetYearId,
                $sourceByName,
                $sourceCTS,
                $sourceTimetables
            );
        }

        $this->command->newLine();
        $this->command->info('══════════════════════════════════════════════════');
        $this->command->info('  Seeder terminé avec succès ✓');
        $this->command->info('══════════════════════════════════════════════════');
    }

    // ────────────────────────────────────────────────────────────────────────
    //  Traitement d'une année cible
    // ────────────────────────────────────────────────────────────────────────

    private function processYear(
        int   $targetYearId,
        \Illuminate\Support\Collection $sourceByName,
        \Illuminate\Support\Collection $sourceCTS,
        \Illuminate\Support\Collection $sourceTimetables
    ): void {
        // Récupérer les classes de cette année archivée
        $targetClasses = DB::table('classes')
            ->where('academic_year_id', $targetYearId)
            ->get();

        if ($targetClasses->isEmpty()) {
            $this->command->warn("  Année {$targetYearId} : aucune classe trouvée, ignorée.");
            return;
        }

        $yearName = DB::table('academic_years')->find($targetYearId)->name ?? "Année {$targetYearId}";
        $this->command->info("  ── Année {$yearName} (id={$targetYearId}) ──────────────");

        $ctsInserted       = 0;
        $ctsSkipped        = 0;
        $ttInserted        = 0;
        $ttSkipped         = 0;
        $classesMatched    = 0;
        $classesUnmatched  = 0;

        foreach ($targetClasses as $targetClass) {
            // Trouver la classe source par nom
            $sourceClassId = $sourceByName->get($targetClass->name);

            if ($sourceClassId === null) {
                $classesUnmatched++;
                $this->command->warn("    ↳ Pas de correspondance source pour : {$targetClass->name}");
                continue;
            }

            $classesMatched++;

            // ── CTS ──────────────────────────────────────────────────────────
            $ctsList = $sourceCTS->get($sourceClassId, collect());

            foreach ($ctsList as $cts) {
                $exists = DB::table('class_teacher_subject')
                    ->where('class_id',          $targetClass->id)
                    ->where('academic_year_id',  $targetYearId)
                    ->where('subject_id',         $cts->subject_id)
                    ->exists();

                if ($exists) {
                    $ctsSkipped++;
                    continue;
                }

                DB::table('class_teacher_subject')->insert([
                    'class_id'         => $targetClass->id,
                    'academic_year_id' => $targetYearId,
                    'teacher_id'       => $cts->teacher_id,
                    'subject_id'       => $cts->subject_id,
                    'coefficient'      => $cts->coefficient,
                    'amount_brut'      => $cts->amount_brut ?? '0.00',
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                $ctsInserted++;
            }

            // ── Timetables ───────────────────────────────────────────────────
            $ttList = $sourceTimetables->get($sourceClassId, collect());

            foreach ($ttList as $tt) {
                $exists = DB::table('timetables')
                    ->where('class_id',          $targetClass->id)
                    ->where('academic_year_id',  $targetYearId)
                    ->where('subject_id',         $tt->subject_id)
                    ->where('day',                $tt->day)
                    ->where('start_time',         $tt->start_time)
                    ->exists();

                if ($exists) {
                    $ttSkipped++;
                    continue;
                }

                DB::table('timetables')->insert([
                    'class_id'         => $targetClass->id,
                    'academic_year_id' => $targetYearId,
                    'teacher_id'       => $tt->teacher_id,
                    'subject_id'       => $tt->subject_id,
                    'day'              => $tt->day,
                    'start_time'       => $tt->start_time,
                    'end_time'         => $tt->end_time,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                $ttInserted++;
            }
        }

        $this->command->info("    Classes : {$classesMatched} mappées, {$classesUnmatched} sans correspondance");
        $this->command->info("    CTS     : {$ctsInserted} insérées, {$ctsSkipped} déjà présentes");
        $this->command->info("    Emplois : {$ttInserted} insérés, {$ttSkipped} déjà présents");
        $this->command->newLine();
    }
}