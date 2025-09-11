<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Student::with(['entity', 'classe'])
            ->orderBy('last_name')->orderBy('first_name')
            ->get()
            ->map(function ($student) {
                return [
                    'Nom' => $student->last_name,
                    'Prénoms' => $student->first_name,
                    'Entité' => $student->entity->name ?? '',
                    'Classe' => $student->classe->name ?? '',
                ];
            });
    }

    public function headings(): array
    {
        return ['Nom', 'Prénoms', 'Entité', 'Classe'];
    }
}
