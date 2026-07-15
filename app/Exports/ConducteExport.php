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
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class ConducteExport extends DefaultValueBinder implements
    FromCollection,
    WithHeadings,
    WithTitle,
    WithStyles,
    WithMapping,
    WithColumnWidths,
    WithCustomValueBinder
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
                'nom'         => mb_strtoupper($student->last_name, 'UTF-8'),
                'prenoms'     => $student->first_name,
                'moy_interro' => $conduiteFinal > 0 ? number_format($conduiteFinal, 2, '.', '') : '',
            ]);
        }

        return $rows;
    }

    /**
     * Force la colonne A (Matricule) à être écrite comme un VRAI texte
     * (type de cellule 's'), exactement comme dans le modèle. Sans ça,
     * PhpSpreadsheet détecte automatiquement que le matricule ressemble
     * à un nombre et l'écrit en tant que nombre (type 'n'), même si on
     * applique ensuite un format d'affichage texte : le format ne change
     * pas le type réel de la donnée stockée.
     */
    public function bindValue(Cell $cell, $value): bool {
        if ($cell->getColumn() === 'A') {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function map($row): array {
        return [
            (string) $row['matricule'],
            $row['nom'],
            $row['prenoms'],
            $row['moy_interro'],
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


    public function columnWidths(): array {
        return [
            'A' => 20.83, // Matricule
            'B' => 40.83, // Nom
            'C' => 40.83, // Prénoms
            'D' => 10.83, // Moy.
        ];
    }

    public function styles(Worksheet $sheet): array {
        // Police du modèle : Calibri, taille 12
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);

        return [];
    }
}