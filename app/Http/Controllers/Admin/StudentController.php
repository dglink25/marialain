<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Entity;
use App\Models\Classe;
use Illuminate\Support\Facades\Validator;
use App\Mail\StudentValidated;
use Illuminate\Support\Facades\Mail;
use App\Models\StudentPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class StudentController extends Controller{
    
    public function store(Request $request){
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
            
            $studentData = [
                'first_name'            => $data['first_name'],
                'last_name'             => $data['last_name'],
                'birth_date'            => $data['birth_date'],
                'birth_place'           => $data['birth_place'],
                'gender'                => $data['gender'],
                'entity_id'             => $data['entity_id'],
                'class_id'              => $data['classe_id'],
                'birth_certificate'     => $data['birth_certificate'] ?? null,
                'vaccination_card'      => $data['vaccination_card'] ?? null,
                'previous_report_card'  => $data['previous_report_card'] ?? null,
                'diploma_certificate'   => $data['diploma_certificate'] ?? null,
                'parent_full_name'      => $data['parent_full_name'],
                'parent_email'          => $data['parent_email'],
                'parent_phone'          => $data['parent_phone'],
                'num_educ'              => $data['num_educ'],
                'age'                   => $data['age'],
            ];

            // Ajoute school_fees uniquement si la colonne existe dans la table students
            if (\Schema::hasColumn('students', 'school_fees') && isset($data['school_fees'])) {
                $studentData['school_fees'] = $data['school_fees'];
                $studentData['amount_paid'] = $data['school_fees'];

                $student = Student::create($studentData);

            
                $student->update([
                    'is_validated' => true,
                ]);

                // Crée le paiement associé
                $payment = $student->payments()->create([
                    'tranche'      => 1,
                    'amount'       => $data['school_fees'],
                    'payment_date' => now(),
                ]);

                // Envoi d'email aux parents
                Mail::to($student->parent_email)->send(new StudentValidated($student));

                // Met à jour les montants payés
                $student->school_fees_paid = $student->payments()->sum('amount');
                $student->fully_paid = $student->school_fees_paid >= ($student->classe->school_fees ?? 0);
                $student->save();

                return redirect()->back()->with('success', 'Inscription réussi avec succès.');
            }

            $student = Student::create($studentData);

            return redirect()->back()->with('success', 'Inscription réussi avec succès.');
        } 
        catch (\Exception $e) {
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

    public function show($id){
        $student = Student::with(['entity', 'classe'])->findOrFail($id);
        return view('admin.students.show', compact('student'));
    }


    public function update(Request $request, $id){
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

    public function destroy($id){
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

    public function create(){
        $entities = Entity::all();
        $classes = Classe::all();
        return view('admin.students.create', compact('entities', 'classes'));
    }

    public function index(Request $request){
        $query = Student::with('entity', 'classe')
            ->where('is_validated', 1);

    
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('entity_id')) {
            $query->where('entity_id', $request->entity_id);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $students = $query->paginate(10)->withQueryString();

        // Charger listes entités et classes pour le filtre
        $entities = \App\Models\Entity::all();
        $classes  = \App\Models\Classe::all();

        return view('admin.students.index', compact('students', 'entities', 'classes'));
    }

    public function inscription(){
        $entities = Entity::all();
        $classes = Classe::all(); 
        return view('admin.students.inscription', compact('entities', 'classes'));
    }

   public function exportPdf(Request $request){
        $query = Student::with('entity', 'classe')
            ->where('is_validated', 1); // uniquement les validés

        $className = 'Toutes les classes';

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);

            // récupérer le nom de la classe
            $class = \App\Models\Classe::find($request->class_id);
            if ($class) {
                $className = $class->name;
            }
        }

        $students = $query->orderBy('last_name')
                        ->orderBy('first_name')
                        ->get();

        $pdf = Pdf::loadView('admin.students.pdf', [
            'students' => $students,
            'className' => $className,
        ])->setPaper('a4', 'landscape'); // paysage

        $fileName = $request->filled('class_id')
            ? 'liste_classe_'.$request->class_id.'.pdf'
            : 'liste_toutes_classes.pdf';

        return $pdf->download($fileName);
    }


    public function exportAllPdf(){
        // Récupérer toutes les classes avec leurs élèves validés
        $classes = \App\Models\Classe::with(['students' => function($q) {
            $q->where('is_validated', 1)
            ->orderBy('last_name')
            ->orderBy('first_name');
        }])->get();

        // Dossier temporaire
        $tempFolder = storage_path('app/temp_pdfs');
        if (!file_exists($tempFolder)) {
            mkdir($tempFolder, 0777, true);
        }

        $zipFile = storage_path('app/liste_eleves_classes.zip');
        $zip = new ZipArchive;
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($classes as $class) {
                if ($class->students->isEmpty()) {
                    continue; // ignorer si pas d'élèves
                }

                // Générer le PDF de la classe
                $pdf = Pdf::loadView('admin.students.pdf', [
                    'students' => $class->students,
                    'className' => $class->name,
                ])
                ->setPaper('a4', 'landscape'); // <-- mode paysage

                $fileName = "classe_".str_replace(' ', '_', $class->name).".pdf";
                $pdfPath = $tempFolder.'/'.$fileName;

                file_put_contents($pdfPath, $pdf->output());

                // Ajouter dans le zip
                $zip->addFile($pdfPath, $fileName);
            }
            $zip->close();
        }

        // Retourner le zip en téléchargement
        return response()->download($zipFile)->deleteFileAfterSend(true);
    }

}
