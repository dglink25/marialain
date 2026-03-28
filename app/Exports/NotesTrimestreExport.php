<?php

namespace App\Exports;

use App\Models\Classe;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\Conduct;
use App\Models\Punishment;
use App\Models\AcademicYear;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NotesTrimestreExport implements FromCollection, WithHeadings, WithStyles{
    protected $classId, $trimestre, $academicYearId;

    public function __construct($classId, $trimestre, $academicYearId)
    {
        $this->classId = $classId;
        $this->trimestre = $trimestre;
        $this->academicYearId = $academicYearId;
    }

    public function collection()
    {
        $classe = Classe::with('students')->findOrFail($this->classId);
        $subjects = Subject::where('classe_id', $this->classId)
            ->where('academic_year_id', $this->academicYearId)
            ->get();

        $grades = Grade::where('academic_year_id', $this->academicYearId)
            ->where('trimestre', $this->trimestre)
            ->whereIn('student_id', $classe->students->pluck('id'))
            ->get();

        $conducts = Conduct::where('academic_year_id', $this->academicYearId)
            ->whereIn('student_id', $classe->students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $punishments = Punishment::where('academic_year_id', $this->academicYearId)
            ->whereIn('student_id', $classe->students->pluck('id'))
            ->selectRaw('student_id, SUM(hours) as total_hours')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        $data = [];
        $moyennes = [];

        foreach ($classe->students as $index => $student) {
            $row = [
                'N°' => $index + 1,
                'Numéro Educ Master' => strtoupper($student->num_educ),
                'Nom' => strtoupper($student->last_name),
                'Prénoms' => ucfirst($student->first_name),
                'Sexe' => strtoupper($student->gender ?? '-'),
            ];

            $totalCoef = 0;
            $totalPts = 0;

            foreach ($subjects as $subject) {
                $studentGrades = $grades->where('student_id', $student->id)
                                        ->where('subject_id', $subject->id);

                $interros = $studentGrades->where('type', 'interrogation')->pluck('value')->toArray();
                $devoirs = $studentGrades->where('type', 'devoir')->pluck('value')->toArray();

                $moyInterro = count($interros) ? round(array_sum($interros)/count($interros), 2) : null;
                $moyDevoir = count($devoirs) ? round(array_sum($devoirs)/count($devoirs), 2) : null;
                $coef = $subject->coefficient ?? 1;

                $moyenne = ($moyInterro !== null && $moyDevoir !== null)
                    ? round(($moyInterro + $moyDevoir) / 2, 2)
                    : null;

                $totalCoef += $coef;
                $totalPts += ($moyenne ?? 0) * $coef;

                $row[$subject->name] = $moyenne ?? '-';
            }

            // Conduite
            $conduct = $conducts[$student->id]->grade ?? 0;
            $punishHours = $punishments[$student->id]->total_hours ?? 0;
            $finalConduct = max(0, $conduct - ($punishHours / 2));
            $row['Conduite'] = $finalConduct;

            $moyGen = $totalCoef > 0 ? round($totalPts / $totalCoef, 2) : 0;
            $row['Moyenne Générale'] = $moyGen;
            $moyennes[$student->id] = $moyGen;

            $data[] = $row;
        }

        // Classement par moyenne
        arsort($moyennes);
        $rang = 1;
        $rangs = [];
        foreach ($moyennes as $sid => $moy) {
            $rangs[$sid] = $rang++;
        }

        // Ajout des rangs
        foreach ($data as &$row) {
            $student = $classe->students->where('last_name', $row['Nom'])->first();
            $row['Rang'] = $rangs[$student->id] ?? '-';
        }

        return collect($data);
    }

    public function headings(): array
    {
        $classe = Classe::find($this->classId);
        $subjects = Subject::where('classe_id', $this->classId)
            ->where('academic_year_id', $this->academicYearId)
            ->pluck('name')
            ->toArray();

        return array_merge(['N°', 'Nom', 'Prénoms', 'Sexe'], $subjects, ['Conduite', 'Moyenne Générale', 'Rang']);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:Z1')->getFont()->setBold(true);
        $sheet->getStyle('A1:Z1')->getAlignment()->setHorizontal('center');
        return [];
    }
}
