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
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
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

    public function __construct(Classe $classe, Subject $subject, int $trimestre, AcademicYear $activeYear)
    {
        $this->classe     = $classe;
        $this->subject    = $subject;
        $this->trimestre  = $trimestre;
        $this->activeYear = $activeYear;
    }

    /**
     * Retourne la collection des élèves avec leurs notes.
     */
    public function collection(): Collection
    {
        // Élèves validés de la classe pour l'année active, triés alphabétiquement
        $students = Student::where('class_id', $this->classe->id)
            ->where('academic_year_id', $this->activeYear->id)
            ->where('is_validated', 1)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Toutes les notes de la matière pour ce trimestre
        $allGrades = Grade::where('class_id', $this->classe->id)
            ->where('subject_id', $this->subject->id)
            ->where('trimestre', $this->trimestre)
            ->where('academic_year_id', $this->activeYear->id)
            ->get();

        $rows = collect();
        $num  = 1;

        foreach ($students as $student) {
            $studentGrades = $allGrades->where('student_id', $student->id);

            // Moyenne des interrogations (séquences 1 à 5)
            $interroValues = $studentGrades
                ->where('type', 'interrogation')
                ->pluck('value')
                ->filter()
                ->values()
                ->toArray();

            $moyenneInterro = !empty($interroValues)
                ? round(array_sum($interroValues) / count($interroValues), 2)
                : null;

            // Devoir 1 et Devoir 2
            $devoir1 = $studentGrades->where('type', 'devoir')->where('sequence', 1)->first()->value ?? null;
            $devoir2 = $studentGrades->where('type', 'devoir')->where('sequence', 2)->first()->value ?? null;

            $rows->push([
                'num'            => $num++,
                'num_educ'       => $student->num_educ ?? '-',
                'nom'            => strtoupper($student->last_name),
                'prenoms'        => $student->first_name,
                'moy_interro'    => $moyenneInterro !== null ? number_format($moyenneInterro, 2, '.', '') : '',
                'devoir1'        => $devoir1 !== null ? number_format($devoir1, 2, '.', '') : '',
                'devoir2'        => $devoir2 !== null ? number_format($devoir2, 2, '.', '') : '',
            ]);
        }

        return $rows;
    }

    /**
     * Mapping des colonnes (les clés du tableau renvoyé par collection()).
     */
    public function map($row): array
    {
        return [
            $row['num'],
            $row['num_educ'],
            $row['nom'],
            $row['prenoms'],
            $row['moy_interro'],
            $row['devoir1'],
            $row['devoir2'],
        ];
    }

    /**
     * En-têtes des colonnes.
     */
    public function headings(): array
    {
        return [
            'N°',
            'Numéro Educ',
            'Nom',
            'Prénom(s)',
            'Moy. Interrogations',
            'Devoir N°1',
            'Devoir N°2',
        ];
    }

    /**
     * Titre de la feuille.
     */
    public function title(): string
    {
        return 'Notes T' . $this->trimestre;
    }

    /**
     * Styles appliqués sur la feuille.
     */
    public function styles(Worksheet $sheet): array
    {
        // ── Ligne de titre fusionnée ────────────────────────────────────────────
        $sheet->insertNewRowBefore(1, 3);

        // Ligne 1 : Titre principal
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', strtoupper($this->subject->name) . ' — ' . $this->classe->name);
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4338CA']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Ligne 2 : Sous-titre (trimestre + année)
        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue(
            'A2',
            'Trimestre ' . $this->trimestre . ' — Année académique : ' . $this->activeYear->name
        );
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 11, 'color' => ['rgb' => '374151']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E7FF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // Ligne 3 vide (espacement)
        $sheet->getRowDimension(3)->setRowHeight(6);

        // ── En-têtes (ligne 4 après insertion) ─────────────────────────────────
        $headerRow = 4;
        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6366F1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'A5B4FC']],
            ],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(22);

        // ── Données : zébrure + bordures ────────────────────────────────────────
        $lastRow = $sheet->getHighestRow();
        for ($r = $headerRow + 1; $r <= $lastRow; $r++) {
            $bgColor = ($r % 2 === 0) ? 'F5F3FF' : 'FFFFFF';
            $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']],
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
        }

        // Centrer les colonnes numériques
        $sheet->getStyle("A5:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E5:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}