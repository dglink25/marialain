<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;
use App\Models\Classe;
use App\Models\StudentAcademicRecord;

/**
 * FixRangsAnnuelsSeeder
 *
 * Calcule et met à jour les rangs annuels dans student_academic_records,
 * groupés par classe et par année.
 *
 * À lancer APRÈS FixHistoricalArchivesSeeder.
 */
class FixRangsAnnuelsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== FixRangsAnnuelsSeeder ===');

        $years = AcademicYear::orderBy('id')->get();

        foreach ($years as $year) {
            $this->command->info("→ Année : {$year->name}");

            // Toutes les classes qui ont des records pour cette année
            $classIds = StudentAcademicRecord::where('academic_year_id', $year->id)
                ->distinct()
                ->pluck('class_id');

            foreach ($classIds as $classId) {
                $classe = Classe::find($classId);
                $nom = $classe ? $classe->name : "classe#{$classId}";

                $records = StudentAcademicRecord::where('academic_year_id', $year->id)
                    ->where('class_id', $classId)
                    ->whereNotNull('moy_annuelle')
                    ->orderByDesc('moy_annuelle')
                    ->get();

                $rang = 1;
                $prevMoy = null;
                $sameCount = 1;

                foreach ($records as $idx => $record) {
                    if ($prevMoy !== null && $record->moy_annuelle == $prevMoy) {
                        $sameCount++;
                    } else {
                        if ($idx > 0) {
                            $rang += $sameCount;
                            $sameCount = 1;
                        }
                    }
                    $record->rang_annuel = $rang;
                    $record->save();
                    $prevMoy = $record->moy_annuelle;
                }

                $this->command->line("  ✓ {$nom} : {$records->count()} rang(s) calculé(s)");
            }
        }

        $this->command->info('✅ Rangs annuels mis à jour.');
    }
}