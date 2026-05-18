<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Classe;
use App\Models\Conduct;
use App\Models\Grade;
use App\Models\Punishment;
use App\Models\Student;
use App\Models\StudentAcademicRecord;
use App\Models\Subject;
use Illuminate\Support\Collection;

/**
 * Service central pour :
 * 1. Calculer la moyenne d'un élève (trimestre ou annuelle)
 * 2. Créer / mettre à jour les snapshots student_academic_records
 * 3. Calculer les rangs dans une classe pour une année donnée
 */
class AcademicRecordService
{
    // ────────────────────────────────────────────────────────────────────────
    //  CALCUL DE MOYENNE D'UN ÉLÈVE POUR UN TRIMESTRE
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Calcule la moyenne générale d'un élève pour un trimestre donné.
     * Retourne null si aucune note n'a été saisie.
     */
    public function calculerMoyenneTrimestre(
        int $studentId,
        int $classId,
        int $trimestre,
        AcademicYear $year,
        Collection $subjects
    ): ?float {
        $grades = Grade::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $year->id)
            ->where('trimestre', $trimestre)
            ->get();

        // Conduite
        $conduct = Conduct::where('student_id', $studentId)
            ->where('trimestre', $trimestre)
            ->where('academic_year_id', $year->id)
            ->first();

        $punishHours = Punishment::where('student_id', $studentId)
            ->where('academic_year_id', $year->id)
            ->sum('hours');

        $conduiteSur20 = max(0, ($conduct ? $conduct->grade : 0) - ($punishHours / 2));

        $totalPoints = 0;
        $totalCoef   = 0;

        foreach ($subjects as $subject) {
            $coefRecord = $subject->classTeacherSubjects->first();
            $coef = $coefRecord->coefficient ?? 1;

            $subjectGrades = $grades->where('subject_id', $subject->id);

            $interroNotes = $subjectGrades
                ->where('type', 'interrogation')
                ->pluck('value')
                ->filter(fn($v) => $v !== null)
                ->values()
                ->toArray();

            $devoir1 = $subjectGrades->where('type', 'devoir')->where('sequence', 1)->first()->value ?? null;
            $devoir2 = $subjectGrades->where('type', 'devoir')->where('sequence', 2)->first()->value ?? null;

            $moyenneInterro = !empty($interroNotes)
                ? array_sum($interroNotes) / count($interroNotes)
                : null;

            $notesPourMoyenne = array_filter([$moyenneInterro, $devoir1, $devoir2], fn($v) => $v !== null);

            if (!empty($notesPourMoyenne)) {
                $moy = array_sum($notesPourMoyenne) / count($notesPourMoyenne);
                $totalPoints += $moy * $coef;
                $totalCoef   += $coef;
            }
        }

        // Conduite (coeff 1)
        if ($conduiteSur20 > 0) {
            $totalPoints += $conduiteSur20;
            $totalCoef   += 1;
        }

        return $totalCoef > 0 ? round($totalPoints / $totalCoef, 2) : null;
    }

    // ────────────────────────────────────────────────────────────────────────
    //  CALCUL DES MOYENNES POUR TOUS LES ÉLÈVES D'UNE CLASSE
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Retourne un tableau indexé par student_id :
     * [
     *   student_id => [
     *     1 => float|null,   // moy trimestre 1
     *     2 => float|null,
     *     3 => float|null,
     *     'annuelle' => float|null,
     *     'rang'     => int,
     *   ]
     * ]
     */
    public function calculerMoyennesClasse(
        Classe $classe,
        AcademicYear $year
    ): array {
        $subjects = Subject::whereHas('classTeacherSubjects', function ($q) use ($classe, $year) {
            $q->where('class_id', $classe->id)
              ->where('academic_year_id', $year->id);
        })->with(['classTeacherSubjects' => function ($q) use ($classe, $year) {
            $q->where('class_id', $classe->id)
              ->where('academic_year_id', $year->id);
        }])->get();

        $students = Student::where('class_id', $classe->id)
            ->where('academic_year_id', $year->id)
            ->where('is_validated', true)
            ->get();

        $result = [];

        foreach ($students as $student) {
            $moys = [];
            foreach ([1, 2, 3] as $t) {
                $moys[$t] = $this->calculerMoyenneTrimestre(
                    $student->id, $classe->id, $t, $year, $subjects
                );
            }

            $moyennesValides = array_filter($moys, fn($v) => $v !== null);
            $moys['annuelle'] = !empty($moyennesValides)
                ? round(array_sum($moyennesValides) / count($moyennesValides), 2)
                : null;

            $result[$student->id] = $moys;
        }

        // Calcul des rangs annuels
        $annuelles = array_filter(
            array_map(fn($d) => $d['annuelle'], $result),
            fn($v) => $v !== null
        );
        arsort($annuelles);

        $rang = 1;
        foreach ($annuelles as $sid => $moy) {
            $result[$sid]['rang'] = $rang++;
        }

        return $result;
    }

    // ────────────────────────────────────────────────────────────────────────
    //  CRÉATION / MISE À JOUR DES SNAPSHOTS POUR UNE CLASSE ENTIÈRE
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Génère les StudentAcademicRecord pour tous les élèves validés
     * d'une classe donnée pour une année donnée.
     *
     * @param string $statut  'pending' (en cours d'année), 'passed'/'repeated' (après délibération)
     */
    public function archiverClasse(
        Classe $classe,
        AcademicYear $year,
        string $statut = 'pending'
    ): int {
        $students = Student::where('class_id', $classe->id)
            ->where('academic_year_id', $year->id)
            ->where('is_validated', true)
            ->get();

        if ($students->isEmpty()) {
            return 0;
        }

        $moyennesParEleve = $this->calculerMoyennesClasse($classe, $year);

        $count = 0;
        foreach ($students as $student) {
            StudentAcademicRecord::createOrUpdateSnapshot(
                $student,
                $year,
                $moyennesParEleve[$student->id] ?? [],
                $statut
            );
            $count++;
        }

        return $count;
    }

    // ────────────────────────────────────────────────────────────────────────
    //  RÉCUPÉRATION DES MATIÈRES D'UNE CLASSE POUR UNE ANNÉE
    // ────────────────────────────────────────────────────────────────────────

    public function getSubjectsForClass(Classe $classe, AcademicYear $year): Collection
    {
        return Subject::whereHas('classTeacherSubjects', function ($q) use ($classe, $year) {
            $q->where('class_id', $classe->id)
              ->where('academic_year_id', $year->id);
        })->with(['classTeacherSubjects' => function ($q) use ($classe, $year) {
            $q->where('class_id', $classe->id)
              ->where('academic_year_id', $year->id);
        }])->get();
    }
}