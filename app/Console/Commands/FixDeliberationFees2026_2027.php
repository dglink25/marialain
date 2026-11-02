<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\Classe;
use App\Models\DeliberationStudent;
use App\Models\Student;
use App\Models\StudentAcademicRecord;
use App\Models\StudentPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixDeliberationFees2026_2027 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deliberation:fix-fees-2026-2027 {--dry-run : Exécuter une simulation sans modifier la base de données} {--all : Corriger également tous les élèves inscrits en 2026-2027}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige la scolarité des élèves délibérés de 2025-2026 vers 2026-2027 et remet à zéro les tranches/montants payés.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $allStudents = (bool) $this->option('all');

        $this->info("====================================================================");
        $this->info(" CORRECTION DES SCOLARITÉS ET PAIEMENTS (2025-2026 -> 2026-2027)");
        $this->info("====================================================================");

        if ($isDryRun) {
            $this->warn("MODE SIMULATION (DRY-RUN) : Aucune modification ne sera enregistrée.");
        }

        // 1. Charger les années académiques
        $sourceYear = AcademicYear::where('name', '2025-2026')->first();
        $targetYear = AcademicYear::where('name', '2026-2027')->first();

        if (!$sourceYear || !$targetYear) {
            $this->error("Erreur : Les années académiques 2025-2026 ou 2026-2027 sont introuvables.");
            return self::FAILURE;
        }

        $this->line("Année source : <comment>{$sourceYear->name}</comment> (ID: {$sourceYear->id})");
        $this->line("Année cible  : <comment>{$targetYear->name}</comment> (ID: {$targetYear->id})");

        // 2. Corriger les paiements 2026-2027 qui avaient academic_year_id = null
        $nullPaymentsCount = StudentPayment::whereNull('academic_year_id')
            ->whereHas('student', function ($q) use ($targetYear) {
                $q->where('academic_year_id', $targetYear->id);
            })->count();

        if ($nullPaymentsCount > 0) {
            $this->warn("Trouvé {$nullPaymentsCount} paiement(s) de 2026-2027 avec academic_year_id = NULL.");
            if (!$isDryRun) {
                StudentPayment::whereNull('academic_year_id')
                    ->whereHas('student', function ($q) use ($targetYear) {
                        $q->where('academic_year_id', $targetYear->id);
                    })->update(['academic_year_id' => $targetYear->id]);
                $this->info("Paiements mis à jour avec academic_year_id = {$targetYear->id}.");
            }
        }

        // 3. Identifier les élèves délibérés de 2025-2026 vers 2026-2027
        $delibFromRecords = StudentAcademicRecord::where('academic_year_id', $sourceYear->id)
            ->where('next_academic_year_id', $targetYear->id)
            ->where('statut_deliberation', 'passed')
            ->pluck('student_id');

        $delibFromStudents = DeliberationStudent::where('old_academic_year_id', $sourceYear->id)
            ->where('new_academic_year_id', $targetYear->id)
            ->whereHas('deliberation', function ($q) {
                $q->where('is_cancelled', false);
            })
            ->pluck('student_id');

        $deliberatedIds = $delibFromRecords->merge($delibFromStudents)->unique();

        $this->info("Élèves identifiés comme délibérés de 2025-2026 vers 2026-2027 : {$deliberatedIds->count()} élève(s).");

        // 4. Charger les élèves cibles
        $studentsQuery = Student::with('classe')->where('academic_year_id', $targetYear->id);

        if (!$allStudents) {
            $studentsQuery->whereIn('id', $deliberatedIds);
        }

        $students = $studentsQuery->get();
        $this->info("Total d'élèves à examiner en {$targetYear->name} : {$students->count()}");

        if ($students->isEmpty()) {
            $this->info("Aucun élève à traiter.");
            return self::SUCCESS;
        }

        // 5. Charger en une seule requête les paiements réels effectués en 2026-2027
        // (y compris les paiements sans academic_year_id qui appartiennent à des élèves inscrits en 2026-2027)
        $payments2026_2027 = StudentPayment::where(function ($q) use ($targetYear) {
                $q->where('academic_year_id', $targetYear->id)
                  ->orWhereNull('academic_year_id');
            })
            ->whereIn('student_id', $students->pluck('id'))
            ->selectRaw('student_id, SUM(amount) as total')
            ->groupBy('student_id')
            ->pluck('total', 'student_id');

        $updatedCount = 0;
        $amountResetCount = 0;
        $feesFixedCount = 0;

        $rows = [];

        foreach ($students as $student) {
            $classe = $student->classe;
            if (!$classe) {
                continue;
            }

            $correctSchoolFees = (float) ($classe->school_fees ?? 0);
            $entityId = (int) ($student->entity_id ?? $classe->entity_id ?? 3);

            $inscriptionFee = ($entityId === 3)
                ? (float) ($classe->registration_fee ?? 10000)
                : 5000;

            $reRegFee = ($entityId === 3)
                ? (float) ($classe->re_registration_fee ?? 5000)
                : 0;

            // Déterminer le total des frais
            if ($student->registration_type === 'graduated') {
                $correctTotalFees = 0.0;
            } elseif ($student->registration_type === 'new') {
                $correctTotalFees = $correctSchoolFees + $inscriptionFee;
            } else {
                // Par défaut pour délibéré : re_registration
                $correctTotalFees = $correctSchoolFees + $reRegFee;
            }

            // Paiement réel pour l'année 2026-2027 (0 si aucun paiement fait en 2026-2027)
            $actualPaid2026_2027 = (float) ($payments2026_2027[$student->id] ?? 0);

            $needsUpdate = false;
            $reason = [];

            // Vérifier la scolarité de la classe
            if ((float)$student->school_fees !== $correctSchoolFees) {
                $needsUpdate = true;
                $feesFixedCount++;
                $reason[] = "school_fees: {$student->school_fees} -> {$correctSchoolFees}";
            }

            // Vérifier les frais totaux
            if ((float)$student->total_fees !== $correctTotalFees) {
                $needsUpdate = true;
                $reason[] = "total_fees: {$student->total_fees} -> {$correctTotalFees}";
            }

            // Vérifier si montant payé contient encore celui de l'année précédente
            if ((float)$student->amount_paid !== $actualPaid2026_2027) {
                $needsUpdate = true;
                $amountResetCount++;
                $reason[] = "amount_paid: {$student->amount_paid} -> {$actualPaid2026_2027}";
            }

            if ((float)$student->school_fees_paid !== $actualPaid2026_2027) {
                $needsUpdate = true;
                $reason[] = "school_fees_paid: {$student->school_fees_paid} -> {$actualPaid2026_2027}";
            }

            $correctFullyPaid = ($actualPaid2026_2027 >= $correctTotalFees && $correctTotalFees > 0);
            if ((bool)$student->fully_paid !== $correctFullyPaid) {
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                $updatedCount++;
                if (count($rows) < 15) {
                    $rows[] = [
                        $student->id,
                        $student->last_name . ' ' . $student->first_name,
                        $classe->name,
                        number_format($correctSchoolFees, 0, ',', ' '),
                        number_format($correctTotalFees, 0, ',', ' '),
                        number_format($student->amount_paid, 0, ',', ' ') . ' -> ' . number_format($actualPaid2026_2027, 0, ',', ' '),
                        implode(', ', $reason),
                    ];
                }

                if (!$isDryRun) {
                    $student->update([
                        'school_fees'      => $correctSchoolFees,
                        'total_fees'       => $correctTotalFees,
                        'amount_paid'      => $actualPaid2026_2027,
                        'school_fees_paid' => $actualPaid2026_2027,
                        'fully_paid'       => $correctFullyPaid,
                    ]);
                }
            }
        }

        $this->table(
            ['ID', 'Élève', 'Classe', 'Scolarité (FCFA)', 'Total frais (FCFA)', 'Paiement 26-27', 'Ajustements (Extrait)'],
            $rows
        );

        $this->info("====================================================================");
        $this->info(" RÉSULTAT DE LA CORRECTION :");
        $this->info(" - Total élèves corrigés              : {$updatedCount}");
        $this->info(" - Scolarités/Frais ajustés           : {$feesFixedCount}");
        $this->info(" - Montants payés/tranches remis à 0  : {$amountResetCount}");
        $this->info("====================================================================");

        if ($isDryRun) {
            $this->warn("Rappel : Simulation uniquement. Pour appliquer les modifications, relancez sans l'option --dry-run :");
            $this->line("php artisan deliberation:fix-fees-2026-2027" . ($allStudents ? " --all" : ""));
        } else {
            $this->info("Toutes les corrections ont été appliquées avec succès en base de données.");
        }

        return self::SUCCESS;
    }
}
