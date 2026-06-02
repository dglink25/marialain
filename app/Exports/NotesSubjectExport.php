<?php

namespace App\Exports;

use App\Models\AcademicYear;
use App\Models\Classe;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class NotesSubjectExport implements
    FromCollection,
    WithHeadings,
    WithTitle,
    WithStyles,
    WithMapping,
    ShouldAutoSize
    {
    protected Classe $classe;
    protected Subject $subject;
    protected int $trimestre;
    protected AcademicYear $activeYear;

    public function __construct(Classe $classe, Subject $subject, int $trimestre, AcademicYear $activeYear){
        $this->classe     = $classe;
        $this->subject    = $subject;
        $this->trimestre  = $trimestre;
        $this->activeYear = $activeYear;
    }

    public function collection(): Collection {
        $students = Student::where('class_id', $this->classe->id)
            ->where('academic_year_id', $this->activeYear->id)
            ->where('is_validated', 1)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $allGrades = Grade::where('class_id', $this->classe->id)
            ->where('subject_id', $this->subject->id)
            ->where('trimestre', $this->trimestre)
            ->where('academic_year_id', $this->activeYear->id)
            ->get();

        $rows = collect();

        foreach ($students as $student) {
            $studentGrades = $allGrades->where('student_id', $student->id);

            $interroValues = $studentGrades
                ->where('type', 'interrogation')
                ->pluck('value')
                ->filter()
                ->values()
                ->toArray();

            $moyenneInterro = !empty($interroValues)
                ? round(array_sum($interroValues) / count($interroValues), 2)
                : null;

            $devoir1 = $studentGrades->where('type', 'devoir')->where('sequence', 1)->first()->value ?? null;
            $devoir2 = $studentGrades->where('type', 'devoir')->where('sequence', 2)->first()->value ?? null;

            $rows->push([
                'matricule'   => $student->num_educ ?? '',
                'nom'         => strtoupper($student->last_name),
                'prenoms'     => $student->first_name,
                'moy_interro' => $moyenneInterro !== null ? number_format($moyenneInterro, 2, '.', '') : '',
                'devoir1'     => $devoir1 !== null ? number_format($devoir1, 2, '.', '') : '',
                'devoir2'     => $devoir2 !== null ? number_format($devoir2, 2, '.', '') : '',
            ]);
        }

        return $rows;
    }

    public function map($row): array {
        return [
            $row['matricule'],
            $row['nom'],
            $row['prenoms'],
            $row['moy_interro'],
            $row['devoir1'],
            $row['devoir2'],
        ];
    }

    public function headings(): array {
        return [
            'Matricule',
            'Nom',
            'Prénoms',
            'Moy. interro',
            'Devoir 1',
            'Devoir 2',
        ];
    }

    public function title(): string{
        return strtoupper($this->subject->name);
    }

    public function styles(Worksheet $sheet): array {
        return [];
    }
}