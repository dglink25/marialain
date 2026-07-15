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
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class NotesSubjectExport extends DefaultValueBinder implements
    FromCollection,
    WithHeadings,
    WithTitle,
    WithStyles,
    WithMapping,
    WithColumnWidths,
    WithCustomValueBinder
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

    /**
     * Force la colonne A (Matricule) à être écrite comme un VRAI texte
     * (type de cellule 's'), exactement comme dans le modèle. Sans ça,
     * PhpSpreadsheet détecte automatiquement que "1150323458180"
     * ressemble à un nombre et l'écrit en tant que nombre (type 'n'),
     * même si on applique ensuite un format d'affichage texte : le
     * format ne change pas le type réel de la donnée stockée.
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

    public function title(): string {
        $name = strtoupper($this->subject->name);

        $mapping = [
            'COMMUNICATION ECRITE'                     => 'FRANCAIS COM. ECRITE',
            'ANGLAIS'                                  => 'ANGLAIS',
            'MATHEMATIQUES'                            => 'MATHS GENE',
            'EDUCATION PHYSIQUE ET SPORTIVE (EPS)'     => 'EPS',
            'HISTOIRE-GEOGRAPHIE'                      => 'HIST-GEO',
            'SCIENCE DE LA VIE ET DE LA TERRE (SVT)'  => 'SVT',
            'SVT'                                      => 'SVT',
            'LECTURE'                                  => 'FRANCAIS LECTURE',
            'PHYSIQUE CHIMIE ET TECHNOLOGIE (PCT)'     => 'PCT',
            'PHILOSOPHIE'                              => 'PHILO',
            'ALLEMAND'                                 => 'ALLEMAND',
            'ESPAGNOL'                                 => 'ESPAGNOL',
        ];

        return $mapping[$name] ?? $name;
    }

    public function columnWidths(): array {
        return [
            'A' => 20.83, // Matricule
            'B' => 40.83, // Nom
            'C' => 40.83, // Prénoms
            'D' => 10.83, // Moy. interro
            'E' => 10.83, // Devoir 1
            'F' => 10.83, // Devoir 2
        ];
    }

    public function styles(Worksheet $sheet): array {
        // Police du modèle : Calibri, taille 12, non gras
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);

        return [];
    }
}