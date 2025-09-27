<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Models\Student;
use Illuminate\Http\Request;
use PDF; // laravel-dompdf
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentsExport;

class StudentExportController extends Controller
{
    public function index()
    {
        // Charger toutes les entités avec leurs classes et élèves
        $entities = Entity::with(['classes.students' => function($q){
            $q->orderBy('last_name')->orderBy('first_name');
        }])->get();

        return view('admin.students.list', compact('entities'));
    }



    public function exportExcel()
    {
        return Excel::download(new StudentsExport, 'liste_etudiants.xlsx');
    }
}
