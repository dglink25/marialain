<?php

namespace App\Exports;

use App\Models\AcademicYear;
use App\Models\Classe;
use App\Models\Conduct;
use App\Models\Punishment;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class ConducteExport implements
    FromCollection,
    WithHeadings,
    WithTitle,
    WithStyles,
    WithMapping,
    ShouldAutoSize
    {
    protected Classe $classe;
    protected int $trimestre;
    protected AcademicYear $activeYear;

    public function __construct(Classe $classe, int $trimestre, AcademicYear $activeYear) {
        $this->classe     = $classe;
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

        $conducts = Conduct::where('academic_year_id', $this->activeYear->id)
            ->where('trimestre', $this->trimestre)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $punishments = Punishment::where('academic_year_id', $this->activeYear->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->selectRaw('student_id, SUM(hours) as total_hours')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        $rows = collect();

        foreach ($students as $student) {
            $conductGrade  = $conducts[$student->id]->grade ?? 0;
            $punishHours   = $punishments[$student->id]->total_hours ?? 0;
            $conduiteFinal = round(max(0, $conductGrade - ($punishHours / 2)), 2);

            $rows->push([
                'matricule'   => $student->num_educ ?? '',
                'nom'         => strtoupper($student->last_name),
                'prenoms'     => $student->first_name,
                'moy_interro' => $conduiteFinal > 0 ? number_format($conduiteFinal, 2, '.', '') : '',
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
            'Moy.',
        ];
    }

    public function title(): string {
        return 'Conduite';
    }

    public function styles(Worksheet $sheet): array {
        return [];
    }
}