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
use App\Models\AcademicYear;
use App\Mail\StudentRegistered;
use Illuminate\Support\Facades\Schema;
use Cloudinary\Api\Upload\UploadApi; // API upload
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Exception;

class StudentController extends Controller{
    public function checkActiveYear(){
        $activeYear = AcademicYear::where('active', 1)->first();
        if (!$activeYear) {
            return view('errors.no_active_year');
        }
        return $activeYear;
    }

    public function store(Request $request){
        // Vérifie si une année académique est active
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return redirect()->back()->with('error', 'Aucune année académique active trouvée.');
        }

        // Validation des données
        $validator = Validator::make($request->all(), [
            'first_name'            => 'required|string|max:255',
            'last_name'             => 'required|string|max:255',
            'birth_date'            => 'required|date',
            'birth_place'           => 'required|string|max:255',
            'entity_id'             => 'required|exists:entities,id',
            'classe_id'             => 'required|exists:classes,id',
            'registration_type'     => 'required|in:new,re_registration',
            'birth_certificate'     => 'nullable|max:2048',
            'vaccination_card'      => 'nullable|max:2048',
            'previous_report_card'  => 'nullable|max:2048',
            'diploma_certificate'   => 'nullable|max:2048',
            'parent_full_name'      => 'required|string|max:255',
            'parent_email'          => 'required|email|max:255',
            'parent_phone'          => 'required|string|max:20',
            'num_educ'              => 'required|string|max:50|unique:students,num_educ',
            'gender'                => 'required|string|in:M,F',
            'school_fees'           => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        // Upload Cloudinary (sécurisé)
        foreach (['birth_certificate', 'vaccination_card', 'previous_report_card', 'diploma_certificate'] as $fileField) {
            if ($request->hasFile($fileField) && $request->file($fileField)->isValid()) {

                $uploadApi = new UploadApi();

                $uploaded = $uploadApi->upload(
                    $request->file($fileField)->getRealPath(),
                    [
                        'folder' => 'students_files',
                        'resource_type' => 'auto', // 👈 IMPORTANT : gère PDF, images, vidéos, etc.
                    ]
                );

                $data[$fileField] = $uploaded['secure_url'];
            }
        }

        // Calcul automatique de l’âge (corrigé)
        $data['age'] = now()->diffInYears($data['birth_date']);
        
        $classe = Classe::query()
                ->where('id', $data['classe_id'])
                ->where('academic_year_id', $activeYear->id)
                ->firstOrFail();

        $totalFees = $classe->school_fees ?? 0;
        if ($data['registration_type'] === 'new') {
            $totalFees += $classe->registration_fee ?? 0;
        } elseif ($data['registration_type'] === 're_registration') {
            $totalFees += $classe->re_registration_fee ?? 0;
        }


        try {
            // Préparation des données d’inscription
            $studentData = [
                'first_name'           => $data['first_name'],
                'last_name'            => $data['last_name'],
                'birth_date'           => $data['birth_date'],
                'birth_place'          => $data['birth_place'],
                'gender'               => $data['gender'],
                'entity_id'            => $data['entity_id'],
                'academic_year_id'     => $activeYear->id,
                'class_id'             => $data['classe_id'],
                'registration_type'    => $data['registration_type'],
                'total_fees'           => $totalFees,
                'birth_certificate'    => $data['birth_certificate'] ?? null,
                'vaccination_card'     => $data['vaccination_card'] ?? null,
                'previous_report_card' => $data['previous_report_card'] ?? null,
                'diploma_certificate'  => $data['diploma_certificate'] ?? null,
                'parent_full_name'     => $data['parent_full_name'],
                'parent_email'         => $data['parent_email'],
                'parent_phone'         => $data['parent_phone'],
                'num_educ'             => $data['num_educ'],
                'age'                  => $data['age'],
            ];

            // Création de l’élève
            $student = Student::create($studentData);

            // Si la colonne school_fees existe
            if (Schema::hasColumn('students', 'school_fees') && isset($data['school_fees'])) {
                $student->update([
                    'school_fees' => $data['school_fees'],
                    'amount_paid' => $data['school_fees'],
                    'is_validated' => true,
                ]);

                // Création du paiement associé
                $student->payments()->create([
                    'tranche'      => 1,
                    'amount'       => $data['school_fees'],
                    'payment_date' => now(),
                ]);
            }

            // Envoi de mail (désactivé pour tests)
            // Mail::to($student->parent_email)->send(new StudentRegistered($student));

            return redirect()->back()->with('success', 'Inscription réussie avec succès.');
        } 
        catch (Exception $e) {
            //Log::error('Erreur inscription élève : ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l’inscription : ' . $e->getMessage());
        }
    }



    // Méthode pour récupérer les classes par entité
    public function getClassesByEntity($entity_id){
        $classes = Classe::where('entity_id', $entity_id)->get();
        return response()->json($classes);
    }

    public function edit($id){
        if (!$this->checkActiveYear() instanceof AcademicYear) {
            return $this->checkActiveYear();
        }

        if (!$this->checkActiveYear() instanceof AcademicYear) {
            return $this->checkActiveYear();
        }
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
        if (!$this->checkActiveYear() instanceof AcademicYear) {
            return $this->checkActiveYear();
        }
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string',
            'entity_id' => 'nullable|exists:entities,id',
            'class_id' => 'nullable|exists:classes,id',
            'birth_certificate' => 'nullable|mimes:pdf|max:2048',
            'vaccination_card' => 'nullable|mimes:pdf|max:2048',
            'previous_report_card' => 'nullable|mimes:pdf|max:2048',
            'diploma_certificate' => 'nullable|mimes:pdf|max:2048',
            'parent_full_name' => 'nullable|string',
            'parent_email' => 'nullable|email',
            'parent_phone' => 'nullable|string',
            'num_educ' => 'nullable|string',
            'gender' => 'nullable|string',
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
        if (!$this->checkActiveYear() instanceof AcademicYear) {
            return $this->checkActiveYear();
        }
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
        if (!$this->checkActiveYear() instanceof AcademicYear) {
            return $this->checkActiveYear();
        }

        $entities = Entity::all();
        $classes  = Classe::all();
        return view('admin.students.create', compact('entities', 'classes'));
    }



    public function index(Request $request){
        try {
            // --- Années académiques disponibles pour le select ---
            $academicYears = AcademicYear::orderByDesc('id')->get();
            $activeYear    = AcademicYear::where('active', true)->first();

            // Année sélectionnée : paramètre GET ou année active par défaut
            $selectedYearId = $request->filled('academic_year_id')
                ? (int) $request->academic_year_id
                : ($activeYear ? $activeYear->id : null);

            $selectedYear = $selectedYearId
                ? AcademicYear::find($selectedYearId)
                : $activeYear;

            // Aucune année disponible
            if (! $selectedYear) {
                return view('admin.students.index', [
                    'students'          => collect(),
                    'entities'          => Entity::all(),
                    'classes'           => collect(),
                    'activeYear'        => null,
                    'academicYears'     => $academicYears,
                    'selectedYearId'    => null,
                    'allClassesForJs'   => [],
                    'allEntitiesForJs'  => [],
                    'message'           => 'Aucune année académique active pour le moment.',
                ]);
            }

            // --- Requête de base : élèves validés de l'année sélectionnée ---
            $query = Student::with('entity', 'classe')
                ->where('is_validated', 1)
                ->where('academic_year_id', $selectedYear->id);

            // Filtre recherche nom/prénom
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name',  'like', "%{$search}%");
                });
            }

            // Filtre niveau (entity_id)
            if ($request->filled('entity_id')) {
                $query->where('entity_id', $request->entity_id);
            }

            // Filtre classe
            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }

            // Filtre date d'inscription
            if ($request->filled('date')) {
                $query->whereDate('created_at', $request->date);
            }

            $students = $query->paginate(10)->withQueryString();

            // Classes et entités filtrées par l'année sélectionnée
            $classes  = Classe::where('academic_year_id', $selectedYear->id)->get();
            $entities = Entity::whereHas('classes', function ($q) use ($selectedYear) {
                $q->where('academic_year_id', $selectedYear->id);
            })->get();

            // ── Données JSON pour le filtrage dynamique JS ──────────────────────
            // Préparées ici (tableaux PHP simples) pour éviter l'erreur Blade :
            // "Unclosed '[' does not match ')'" causée par fn() dans @json().

            // Classes groupées par academic_year_id → { "1": [{id, name, entity_id}, …], … }
            $allClassesForJs = Classe::select('id', 'name', 'academic_year_id', 'entity_id')
                ->get()
                ->groupBy('academic_year_id')
                ->map(function ($group) {
                    return $group->map(function ($c) {
                        return ['id' => $c->id, 'name' => $c->name, 'entity_id' => $c->entity_id];
                    })->values()->toArray();
                })
                ->toArray();

            // Entités avec la liste de leurs classes (id + academic_year_id uniquement)
            $allEntitiesForJs = Entity::with(['classes' => function ($q) {
                $q->select('id', 'academic_year_id', 'entity_id');
            }])->get()->map(function ($e) {
                return [
                    'id'      => $e->id,
                    'name'    => $e->name,
                    'classes' => $e->classes->map(function ($c) {
                        return ['id' => $c->id, 'academic_year_id' => $c->academic_year_id];
                    })->values()->toArray(),
                ];
            })->toArray();

            return view('admin.students.index', compact(
                'students',
                'entities',
                'classes',
                'activeYear',
                'academicYears',
                'selectedYearId',
                'allClassesForJs',
                'allEntitiesForJs'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur inattendue : ' . $e->getMessage());
        }
    }

    public function inscription(){
        if (!$this->checkActiveYear() instanceof AcademicYear) {
            return $this->checkActiveYear();
        }

        $entities = Entity::all();
        $classes  = Classe::all(); 
        return view('admin.students.inscription', compact('entities', 'classes'));
    }


    public function exportPdf(Request $request){

        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'Aucune année académique active trouvée.');
        }

        $query = Student::with('entity', 'classe')
            ->where('is_validated', 1)
            ->where('academic_year_id', $activeYear->id); 

        $className = 'Toutes les classes';

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);

            // récupérer le nom de la classe
            $class = \App\Models\Classe::find($request->class_id);
            if ($class) {
                $className = $class->name;
            }
        }

        $students = $query
                        ->orderBy('last_name')
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

        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'Aucune année académique active trouvée.');
        }

        // Récupérer toutes les classes avec leurs élèves validés
        $classes = \App\Models\Classe::with(['students' => function($q) use ($activeYear) {
            $q->where('is_validated', 1)
            ->where('academic_year_id', $activeYear->id)
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

    public function pending(){
        try {
            // Vérifier si une année scolaire est active
            $activeYear = AcademicYear::where('active', true)->first();

            if (!$activeYear) {
                return view('admin.students.pending', [
                    'students' => collect(),  // liste vide
                    'activeYear' => null,
                    'message' => 'Aucune année scolaire active pour le moment.'
                ]);
            }


            // Récupérer les élèves non validés de l'année active
            $students = Student::where('is_validated', false)
                            ->where('academic_year_id', $activeYear->id)
                            ->get();

            return view('admin.students.pending', compact('students', 'activeYear'));

        } 
        catch (\Exception $e) {
            // Gestion d'autres erreurs inattendues
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue : '.$e->getMessage());
        }
    }

    public function downloadReceipt(StudentPayment $payment){
        try {
            if (!$payment) {
                return redirect()->back()->with('error', 'Reçu non trouvé.');
            }

            $student = $payment->student; // Assure-toi que la relation student existe dans le modèle StudentPayment

            $pdf = Pdf::loadView('pdf.receipt', [
                'student' => $student,
                'payment' => $payment
            ])->setPaper('A4', 'portrait');

            return $pdf->download('recu_'.$payment->id.'.pdf');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la génération du PDF : '.$e->getMessage());
        }
    }

    public function exportEmmagementPdf(Request $request) {
        // 1. Vérifier l'année académique active
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'Aucune année académique active trouvée.');
        }
    
        // 2. Valider les paramètres
        $request->validate([
            'class_id'  => 'required|exists:classes,id',
            'trimestre' => 'required|in:1,2,3',
        ]);
    
        // 3. Récupérer la classe
        $classe = \App\Models\Classe::findOrFail($request->class_id);
    
        // 4. Récupérer les élèves validés de cette classe, triés alphabétiquement
        $students = Student::with(['entity', 'classe'])
            ->where('is_validated', 1)
            ->where('academic_year_id', $activeYear->id)
            ->where('class_id', $request->class_id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    
        // 5. Générer le PDF
        $pdf = Pdf::loadView('admin.students.emmagement_pdf', [
            'students'     => $students,
            'className'    => $classe->name,
            'trimestre'    => $request->trimestre,
            'academicYear' => $activeYear->name ?? $activeYear->year ?? '',
        ])->setPaper('a4', 'landscape');
    
        // 6. Nom du fichier
        $fileName = 'emmagement_bulletin_'
            . str_replace(' ', '_', $classe->name)
            . '_T' . $request->trimestre
            . '.pdf';
    
        return $pdf->download($fileName);
    }


}
