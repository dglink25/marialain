<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Entity;
use App\Models\Classe;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with('entity', 'classe')->paginate(10);
        return view('students.index', compact('students'));
    }

    public function create()
    {
        $entities = Entity::all();
        return view('students.create', compact('entities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'birth_date' => 'required|date',
            'entity_id' => 'required|exists:entities,id',
            'classe_id' => 'required|exists:classes,id',
            'birth_certificate' => 'required|mimes:pdf|max:2048',
            'vaccination_card' => 'nullable|mimes:pdf|max:2048',
            'previous_report_card' => 'nullable|mimes:pdf|max:2048',
            'diploma_certificate' => 'nullable|mimes:pdf|max:2048',
            'parent_full_name' => 'required|string',
            'parent_email' => 'required|email',
            'school_fees' => 'required|integer|min:0',
        ]);

        $data = $request->all();

        // Upload fichiers
        foreach (['birth_certificate','vaccination_card','previous_report_card','diploma_certificate'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('students_files','public');
            }
        }

        // Calcul automatique de l'âge
        $data['age'] = now()->diffInYears($request->birth_date);

        Student::create($data);

        return redirect()->back()->with('success', 'Étudiant ajouté avec succès.');
    }

    // Méthode pour récupérer les classes par entité
    public function getClassesByEntity($entity_id)
    {
        $classes = Classe::where('entity_id', $entity_id)->get();
        return response()->json($classes);
    }

    public function edit($id){
        $student = Student::findOrFail($id);
        $entities = Entity::all();
        $classes = Classe::all();

        return view('students.edit', compact('student', 'entities', 'classes'));
    }

    public function show($id)
    {
        $student = Student::with(['entity', 'classe'])->findOrFail($id);
        return view('students.show', compact('student'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'entity_id' => 'required|exists:entities,id',
            'classe_id' => 'required|exists:classes,id',
            'school_fees_paid' => 'required|integer|min:0',
        ]);

        $student = Student::findOrFail($id);
        $student->update($request->all());

        return redirect()->route('admin.students.index')->with('success', 'Informations mises à jour.');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Étudiant supprimé avec succès.');
    }

    public function listAlphabetical()
    {
        $entities = \App\Models\Entity::with(['classes.students' => function ($query) {
            $query->orderBy('last_name')->orderBy('first_name');
        }])->get();

        return view('admin.students.list', compact('entities'));
    }


}
