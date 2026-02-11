<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\TeacherExam;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TeacherExamController extends Controller
{
    /**
     * Affiche la liste des épreuves
     */
    public function index(Request $request)
    {
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
        
        // Pour les filtres
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
    public function create()
    {
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
        
        return view('teacher.exams.create', compact('classes'));
    }
    
    /**
     * Enregistre une nouvelle épreuve
     */
    public function store(Request $request)
    {
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
                'file' => 'required|file|mimes:pdf|max:10240',
            ], [
                'file.required' => 'Veuillez sélectionner un fichier PDF.',
                'file.mimes' => 'Seuls les fichiers PDF sont acceptés.',
                'file.max' => 'Le fichier ne doit pas dépasser 10 Mo.',
                'numero_evaluation.required' => 'Veuillez sélectionner le numéro de l\'évaluation.',
                'titre.required' => 'Le titre est obligatoire.',
            ]);

            $validator->after(function ($validator) use ($request) {
                if ($request->type === 'devoir' && !in_array($request->numero_evaluation, [1, 2])) {
                    $validator->errors()->add('numero_evaluation', 'Pour un devoir, le numéro d\'évaluation doit être 1 ou 2.');
                }
                if ($request->type === 'interrogation' && !in_array($request->numero_evaluation, [1, 2, 3, 4, 5])) {
                    $validator->errors()->add('numero_evaluation', 'Pour une interrogation, le numéro d\'évaluation doit être entre 1 et 5.');
                }
            });

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Upload du fichier sur Cloudinary
            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                try {
                    $uploadApi = new UploadApi();
                    
                    $uploaded = $uploadApi->upload(
                        $request->file('file')->getRealPath(),
                        [
                            'folder' => 'teacher_exams/' . $activeYear->id,
                            'resource_type' => 'auto',
                            'public_id' => 'exam_' . time() . '_' . uniqid(),
                        ]
                    );

                    $fileUrl = $uploaded['secure_url'];
                    $fileName = $request->file('file')->getClientOriginalName();

                } catch (\Exception $e) {
                    Log::error('Erreur upload Cloudinary: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur lors de l\'upload du fichier sur Cloudinary.'
                    ], 500);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Fichier invalide ou manquant.'
                ], 400);
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
                'file_url' => $fileUrl,
                'file_name' => $fileName,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Épreuve soumise avec succès.',
                'data' => $exam,
                'redirect' => route('teacher.exams.index')
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur soumission épreuve: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la soumission de l\'épreuve.'
            ], 500);
        }
    }

    /**
     * Affiche les détails d'une épreuve
     */
    public function show($id)
    {
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
            $exam->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Épreuve supprimée avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression.'
            ], 500);
        }
    }

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
    
    /**
     * Statistiques pour le dashboard
     */
    public function statistics()
    {
        $teacher = Auth::user();
        $activeYear = AcademicYear::where('active', true)->first();
        
        $stats = [
            'by_type' => TeacherExam::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->selectRaw('type, count(*) as total')
                ->groupBy('type')
                ->get(),
            'by_trimestre' => TeacherExam::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->selectRaw('trimestre, count(*) as total')
                ->groupBy('trimestre')
                ->get(),
            'recent' => TeacherExam::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->with(['class', 'subject'])
                ->latest()
                ->take(5)
                ->get()
        ];
        
        return response()->json($stats);
    }
}