<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Entity;
use App\Models\Classe;
use Illuminate\Support\Facades\Validator;


class StudentController extends Controller{
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'birth_date' => 'required|date',
                'birth_place' => 'required|string',
                'entity_id' => 'required|exists:entities,id',
                'classe_id' => 'required|exists:classes,id',
                'birth_certificate' => 'required|mimes:pdf|max:2048',
                'vaccination_card' => 'nullable|mimes:pdf|max:2048',
                'previous_report_card' => 'nullable|mimes:pdf|max:2048',
                'diploma_certificate' => 'nullable|mimes:pdf|max:2048',
                'parent_full_name' => 'required|string',
                'parent_email' => 'required|email',
                'parent_phone' => 'required|string',
                'school_fees' => 'required|integer|min:0',
                'num_educ' => 'required|string',
                'gender' => 'required|string',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                                ->withErrors($validator)
                                ->withInput();
            }

        $data = $request->all();

        // Upload fichiers
        foreach (['birth_certificate','vaccination_card','previous_report_card','diploma_certificate'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('students_files','public');
            }
        }

        // Calcul automatique de l'âge
        $data['age'] = now()->diffInYears($request->birth_date);
        $data['age'] = (-1)*$data['age'];
        var_dump($data['age']);

        try {
            $student = Student::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'birth_date' => $data['birth_date'],
                'birth_place' => $data['birth_place'],
                'gender' => $data['gender'],
                'entity_id' => $data['entity_id'],
                'class_id' => $data['classe_id'],
                'birth_certificate' => $data['birth_certificate'] ?? null,
                'vaccination_card' => $data['vaccination_card'] ?? null,
                'previous_report_card' => $data['previous_report_card'] ?? null,
                'diploma_certificate' => $data['diploma_certificate'] ?? null,
                'parent_full_name' => $data['parent_full_name'],
                'parent_email' => $data['parent_email'],
                'parent_phone' =>   $data['parent_phone'],
                'school_fees' => $data['school_fees'],
                'num_educ' => $data['num_educ'],
                'age' => $data['age'],
            ]);

            return redirect()->back()->with('success', 'Étudiant ajouté avec succès.');
        } catch (\Exception $e) {
            // Retourne l'erreur pour debug
            return redirect()->back()->with('error', 'Erreur lors de l\'ajout : '.$e->getMessage());
        }
    }


    // Méthode pour récupérer les classes par entité
    public function getClassesByEntity($entity_id){
        $classes = Classe::where('entity_id', $entity_id)->get();
        return response()->json($classes);
    }

    public function edit($id){
        $student = Student::findOrFail($id);
        $entities = Entity::all();
        $classes = Classe::all();

        return view('admin.students.edit', compact('student', 'entities', 'classes'));
    }

    public function show($id)
    {
        $student = Student::with(['entity', 'classe'])->findOrFail($id);
        return view('admin.students.show', compact('student'));
    }


    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'birth_date' => 'required|date',
            'birth_place' => 'required|string',
            'entity_id' => 'required|exists:entities,id',
            'class_id' => 'required|exists:classes,id',
            'birth_certificate' => 'nullable|mimes:pdf|max:2048',
            'vaccination_card' => 'nullable|mimes:pdf|max:2048',
            'previous_report_card' => 'nullable|mimes:pdf|max:2048',
            'diploma_certificate' => 'nullable|mimes:pdf|max:2048',
            'parent_full_name' => 'required|string',
            'parent_email' => 'required|email',
            'parent_phone' => 'required|string',
            'school_fees' => 'required|integer|min:0',
            'num_educ' => 'required|string',
            'gender' => 'required|string',
        ]);

        $data['age'] = now()->diffInYears($request->birth_date);
        $data['age'] = (-1)*$data['age'];
        $data = $request->all();

        // Upload fichiers si nécessaire
        foreach (['birth_certificate','vaccination_card','previous_report_card','diploma_certificate'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $data[$fileField] = $request->file($fileField)->store('students_files','public');
            }
        }

        // Calcul automatique de l'âge
        $data['age'] = now()->diffInYears($request->birth_date);

        $student->update($data);

        return redirect()->route('admin.students.index')
                        ->with('success', 'Étudiant mis à jour avec succès.');
    }


    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Étudiant supprimé avec succès.');
    }

    public function listAlphabetical(){
        $entities = \App\Models\Entity::with(['classes.students' => function ($query) {
            $query->orderBy('last_name')->orderBy('first_name');
        }])->get();

        return view('admin.students.list', compact('entities'));
    }

    public function create()
    {
        $entities = Entity::all();
        $classes = Classe::all(); // Ajouté pour sélectionner la classe
        return view('admin.students.create', compact('entities', 'classes'));
    }

    public function index()
    {
        $students = Student::with('entity', 'classe')->paginate(10);
        return view('admin.students.index', compact('students'));
    }


}
