<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\TeacherExam;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Api\Admin\AdminApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TeacherExamController extends Controller{
    /**
     * Affiche la liste des épreuves
     */
    public function index(Request $request){
        $teacher = Auth::user();
        $activeYear = AcademicYear::where('active', true)->first();
        
        $query = TeacherExam::with(['class', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->where('academic_year_id', $activeYear->id);
        
        // Filtres
        if ($request->filled('trimestre')) {
            $query->where('trimestre', $request->trimestre);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        
        // Statistiques
        $stats = [
            'total' => TeacherExam::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->count(),
            'devoirs' => TeacherExam::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->where('type', 'devoir')
                ->count(),
            'interrogations' => TeacherExam::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->where('type', 'interrogation')
                ->count(),
            'trimestre1' => TeacherExam::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->where('trimestre', 1)
                ->count(),
            'trimestre2' => TeacherExam::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->where('trimestre', 2)
                ->count(),
            'trimestre3' => TeacherExam::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->where('trimestre', 3)
                ->count(),
        ];
        
        $exams = $query->orderBy('created_at', 'desc')->paginate(12);
        
        $classes = Classe::whereHas('teachers', function($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })->get();
        
        $subjects = Subject::whereHas('teachers', function($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })->get();
        
        return view('teacher.exams.index', compact('exams', 'stats', 'classes', 'subjects'));
    }
    
    /**
     * Affiche le formulaire de création d'épreuve
     */
    public function create(Request $request){
        $teacher = Auth::user();
        
        $classes = Classe::with(['subjects' => function($q) use ($teacher) {
            $q->whereHas('teachers', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            });
        }])
        ->whereHas('teachers', function($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })
        ->get();
        
        // Pré-remplir si les paramètres sont présents
        $selectedClassId = $request->query('class_id');
        $selectedSubjectId = $request->query('subject_id');
        
        return view('teacher.exams.create', compact('classes', 'selectedClassId', 'selectedSubjectId'));
    }
    

    public function store(Request $request) {
        try {
            $activeYear = AcademicYear::where('active', true)->firstOrFail();
            $teacher = Auth::user();

            $validator = Validator::make($request->all(), [
                'class_id' => 'required|exists:classes,id',
                'subject_id' => 'required|exists:subjects,id',
                'trimestre' => 'required|in:1,2,3',
                'type' => 'required|in:interrogation,devoir',
                'numero_evaluation' => 'required|integer',
                'titre' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'file' => 'required|file|mimes:pdf|max:20480',
            ]);

            $validator->after(function ($validator) use ($request) {
                if ($request->type === 'devoir' && !in_array((int)$request->numero_evaluation, [1, 2])) {
                    $validator->errors()->add('numero_evaluation', 'Pour un devoir, le numéro d\'évaluation doit être 1 ou 2.');
                }
                if ($request->type === 'interrogation' && !in_array((int)$request->numero_evaluation, [1, 2, 3, 4, 5])) {
                    $validator->errors()->add('numero_evaluation', 'Pour une interrogation, le numéro d\'évaluation doit être entre 1 et 5.');
                }
            });

            if ($validator->fails()) {
                // Si c'est une requête AJAX
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors()
                    ], 422);
                }
                // Sinon, redirection avec erreurs
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Upload du fichier sur Cloudinary
            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                try {
                    $file = $request->file('file');
                    $fileName = $file->getClientOriginalName();
                    
                    $uploadApi = new UploadApi();
                    
                    Log::info('Upload du PDF sur Cloudinary...');
                    $pdfUpload = $uploadApi->upload(
                        $file->getRealPath(),
                        [
                            'folder' => 'teacher_exams/' . $activeYear->id,
                            'resource_type' => 'raw',
                            'public_id' => pathinfo($fileName, PATHINFO_FILENAME) . '_' . time(),
                            'use_filename' => true,
                            'unique_filename' => true,
                        ]
                    );
                    
                    $pdfUrl = $pdfUpload['secure_url'];
                    $pdfPublicId = $pdfUpload['public_id'];
                    
                    Log::info('PDF uploadé avec succès: ' . $pdfUrl);

                } catch (\Exception $e) {
                    Log::error('Erreur upload Cloudinary: ' . $e->getMessage());
                    
                    $errorMessage = 'Erreur lors de l\'upload du fichier. Vérifiez la configuration Cloudinary.';
                    
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => $errorMessage
                        ], 500);
                    }
                    
                    return redirect()->back()->with('error', $errorMessage)->withInput();
                }
            } else {
                $errorMessage = 'Fichier invalide ou manquant.';
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 400);
                }
                
                return redirect()->back()->with('error', $errorMessage)->withInput();
            }

            // Création de l'épreuve
            $exam = TeacherExam::create([
                'teacher_id' => $teacher->id,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'academic_year_id' => $activeYear->id,
                'trimestre' => $request->trimestre,
                'type' => $request->type,
                'numero_evaluation' => $request->numero_evaluation,
                'titre' => $request->titre,
                'description' => $request->description,
                'file_url' => $pdfUrl,
                'file_name' => $fileName,
                'pdf_public_id' => $pdfPublicId,
                'total_pages' => 1,
            ]);

            // Succès - réponse selon le type de requête
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Épreuve publiée avec succès.',
                    'data' => $exam,
                    'redirect' => route('teacher.exams.index')
                ], 201);
            }
            
            return redirect()->route('teacher.exams.index')
                ->with('success', 'Épreuve publiée avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur soumission épreuve: ' . $e->getMessage());
            
            $errorMessage = 'Erreur lors de la soumission: ' . $e->getMessage();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->back()->with('error', $errorMessage)->withInput();
        }
    }

    public function show($id) {
        $exam = TeacherExam::with(['class', 'subject', 'teacher'])
            ->where('teacher_id', Auth::id())
            ->findOrFail($id);
        
        return view('teacher.exams.show', compact('exam'));
    }

    /**
     * Supprime une épreuve
     */
    public function destroy($id)
    {
        try {
            $exam = TeacherExam::where('teacher_id', Auth::id())->findOrFail($id);
            
            // Supprimer le fichier de Cloudinary
            if ($exam->pdf_public_id) {
                try {
                    $uploadApi = new UploadApi();
                    $result = $uploadApi->destroy($exam->pdf_public_id, [
                        'resource_type' => 'raw' // Important pour les PDF
                    ]);
                    
                    Log::info('Suppression Cloudinary: ' . json_encode($result));
                    
                } catch (\Exception $e) {
                    Log::error('Erreur suppression Cloudinary: ' . $e->getMessage());
                    // On continue même si la suppression Cloudinary échoue
                }
            }
            
            // Supprimer l'enregistrement en base
            $exam->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Épreuve supprimée avec succès.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur suppression épreuve: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression.'
            ], 500);
        }
    }

    /**
     * Récupère les numéros d'évaluation disponibles
     */
    public function getEvaluationNumbers(Request $request)
    {
        $type = $request->type;
        
        if ($type === 'devoir') {
            $numbers = [1, 2];
        } elseif ($type === 'interrogation') {
            $numbers = [1, 2, 3, 4, 5];
        } else {
            $numbers = [];
        }

        return response()->json([
            'success' => true,
            'numbers' => $numbers
        ]);
    }
}