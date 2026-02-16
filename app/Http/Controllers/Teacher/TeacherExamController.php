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
use Illuminate\Support\Facades\Storage;
use Imagick;
use Spatie\PdfToImage\Pdf;

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
    
    /**
     * Convertit un PDF en images PNG - Version ROBUSTE avec toutes les pages
     * Utilise Imagick comme méthode principale
     */
    private function convertPdfToPng($pdfPath)
    {
        $convertedPaths = [];
        
        try {
            // Méthode 1: Utilisation de Imagick (la plus fiable)
            if (class_exists('Imagick')) {
                try {
                    $imagick = new Imagick();
                    $imagick->setResolution(150, 150);
                    $imagick->readImage($pdfPath);
                    
                    $pageCount = $imagick->getNumberImages();
                    
                    for ($i = 0; $i < $pageCount; $i++) {
                        $imagick->setIteratorIndex($i);
                        
                        // Créer une nouvelle instance pour chaque page
                        $page = new Imagick();
                        $page->setResolution(150, 150);
                        $page->readImage($pdfPath . '[' . $i . ']');
                        
                        // Configuration pour PNG de qualité
                        $page->setImageFormat('png');
                        $page->setImageCompressionQuality(90);
                        
                        // Supprimer le canal alpha si présent
                        if ($page->getImageAlphaChannel()) {
                            $page->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
                        }
                        
                        // Améliorer la qualité
                        $page->setImageCompression(Imagick::COMPRESSION_ZIP);
                        $page->setImageBackgroundColor('white');
                        $page = $page->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
                        
                        $tempPngPath = sys_get_temp_dir() . '/exam_' . uniqid() . '_page_' . ($i + 1) . '.png';
                        $page->writeImage($tempPngPath);
                        
                        $convertedPaths[] = [
                            'path' => $tempPngPath,
                            'page' => $i + 1
                        ];
                        
                        $page->clear();
                        $page->destroy();
                    }
                    
                    $imagick->clear();
                    $imagick->destroy();
                    
                    if (count($convertedPaths) > 0) {
                        Log::info('Conversion PDF → PNG réussie avec Imagick: ' . $pageCount . ' pages');
                        return $convertedPaths;
                    }
                    
                } catch (\Exception $e) {
                    Log::warning('Erreur Imagick: ' . $e->getMessage());
                    // Fallback à Spatie
                }
            }
            
            // Méthode 2: Utilisation de Spatie PDF to Image
            if (class_exists('\Spatie\PdfToImage\Pdf')) {
                try {
                    $pdf = new Pdf($pdfPath);
                    
                    // Définir la résolution
                    if (method_exists($pdf, 'setResolution')) {
                        $pdf->setResolution(150);
                    }
                    
                    $pageCount = $pdf->getNumberOfPages();
                    
                    // Créer un dossier temporaire
                    $tempDir = sys_get_temp_dir() . '/exam_' . uniqid();
                    if (!file_exists($tempDir)) {
                        mkdir($tempDir, 0777, true);
                    }
                    
                    // Sauvegarder toutes les pages
                    for ($i = 1; $i <= $pageCount; $i++) {
                        $outputPath = $tempDir . '/page_' . $i . '.png';
                        $pdf->setPage($i)->saveImage($outputPath);
                        
                        $convertedPaths[] = [
                            'path' => $outputPath,
                            'page' => $i
                        ];
                    }
                    
                    Log::info('Conversion PDF → PNG réussie avec Spatie: ' . $pageCount . ' pages');
                    return $convertedPaths;
                    
                } catch (\Exception $e) {
                    Log::warning('Erreur Spatie PDF to Image: ' . $e->getMessage());
                    // Fallback à Ghostscript
                }
            }
            
            // Méthode 3: Utilisation de Ghostscript (commande shell)
            if (function_exists('shell_exec')) {
                $tempDir = sys_get_temp_dir() . '/exam_' . uniqid();
                if (!file_exists($tempDir)) {
                    mkdir($tempDir, 0777, true);
                }
                
                $outputFile = $tempDir . '/page_%d.png';
                $command = "gs -dSAFER -dBATCH -dNOPAUSE -sDEVICE=png16m -r150 -dTextAlphaBits=4 -dGraphicsAlphaBits=4 -sOutputFile={$outputFile} '{$pdfPath}' 2>&1";
                
                $output = shell_exec($command);
                
                $files = glob($tempDir . '/*.png');
                if (count($files) > 0) {
                    foreach ($files as $index => $file) {
                        $convertedPaths[] = [
                            'path' => $file,
                            'page' => $index + 1
                        ];
                    }
                    
                    Log::info('Conversion PDF → PNG réussie avec Ghostscript: ' . count($files) . ' pages');
                    return $convertedPaths;
                }
            }
            
            // Méthode 4: Si aucune méthode ne fonctionne, on utilise directement le PDF
            Log::warning('Aucune méthode de conversion disponible, utilisation du PDF original');
            
            // Retourner un chemin vide mais permettre de continuer avec le PDF original
            return [];
            
        } catch (\Exception $e) {
            Log::error('Erreur conversion PDF → PNG: ' . $e->getMessage());
            return [];
        }
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
                'file' => 'required|file|mimes:pdf|max:20480',
                'pdf_pages' => 'nullable|string' // Pages converties en base64
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
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Upload du fichier sur Cloudinary
            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                try {
                    $file = $request->file('file');
                    $pdfPath = $file->getRealPath();
                    $fileName = $file->getClientOriginalName();
                    
                    $uploadApi = new UploadApi();
                    
                    // 1. Upload du PDF original
                    Log::info('Upload du PDF original...');
                    $pdfUpload = $uploadApi->upload(
                        $pdfPath,
                        [
                            'folder' => 'teacher_exams/' . $activeYear->id . '/pdf',
                            'resource_type' => 'auto',
                            'public_id' => 'exam_pdf_' . time() . '_' . uniqid(),
                            'use_filename' => true,
                            'unique_filename' => true,
                        ]
                    );
                    
                    $pdfUrl = $pdfUpload['secure_url'];
                    $pdfPublicId = $pdfUpload['public_id'];
                    
                    // 2. Gestion des pages converties depuis le frontend
                    $previews = [];
                    $totalPages = 0;
                    
                    if ($request->has('pdf_pages') && !empty($request->pdf_pages)) {
                        try {
                            $pages = json_decode($request->pdf_pages, true);
                            
                            if (is_array($pages) && count($pages) > 0) {
                                $totalPages = count($pages);
                                
                                foreach ($pages as $index => $pageBase64) {
                                    // Créer un fichier temporaire à partir du base64
                                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $pageBase64));
                                    $tempImagePath = tempnam(sys_get_temp_dir(), 'page_') . '.png';
                                    file_put_contents($tempImagePath, $imageData);
                                    
                                    // Upload sur Cloudinary
                                    $pngUpload = $uploadApi->upload(
                                        $tempImagePath,
                                        [
                                            'folder' => 'teacher_exams/' . $activeYear->id . '/previews',
                                            'resource_type' => 'image',
                                            'public_id' => 'exam_preview_' . time() . '_' . uniqid() . '_page_' . ($index + 1),
                                        ]
                                    );
                                    
                                    $previews[] = [
                                        'page' => $index + 1,
                                        'url' => $pngUpload['secure_url'],
                                        'public_id' => $pngUpload['public_id']
                                    ];
                                    
                                    // Nettoyer le fichier temporaire
                                    @unlink($tempImagePath);
                                }
                            }
                        } catch (\Exception $e) {
                            Log::error('Erreur traitement des pages: ' . $e->getMessage());
                        }
                    } else {
                        // Fallback: convertir le PDF côté serveur
                        Log::info('Aucune page reçue, tentative conversion serveur...');
                        $convertedPages = $this->convertPdfToPng($pdfPath);
                        
                        if ($convertedPages && count($convertedPages) > 0) {
                            $totalPages = count($convertedPages);
                            
                            foreach ($convertedPages as $page) {
                                if (file_exists($page['path'])) {
                                    try {
                                        $pngUpload = $uploadApi->upload(
                                            $page['path'],
                                            [
                                                'folder' => 'teacher_exams/' . $activeYear->id . '/previews',
                                                'resource_type' => 'image',
                                                'public_id' => 'exam_preview_' . time() . '_' . uniqid() . '_page_' . $page['page'],
                                            ]
                                        );
                                        
                                        $previews[] = [
                                            'page' => $page['page'],
                                            'url' => $pngUpload['secure_url'],
                                            'public_id' => $pngUpload['public_id']
                                        ];
                                        
                                        @unlink($page['path']);
                                    } catch (\Exception $e) {
                                        Log::error("Erreur upload page {$page['page']}: " . $e->getMessage());
                                    }
                                }
                            }
                            
                            // Nettoyer le dossier temporaire si existant
                            if (isset($convertedPages[0]['path'])) {
                                $tempDir = dirname($convertedPages[0]['path']);
                                if (is_dir($tempDir)) {
                                    array_map('unlink', glob("{$tempDir}/*.*"));
                                    rmdir($tempDir);
                                }
                            }
                        }
                    }
                    
                    // 3. Créer une vignette de la première page
                    $thumbnailUrl = null;
                    if (count($previews) > 0) {
                        $thumbnailUrl = $previews[0]['url'];
                    }

                } catch (\Exception $e) {
                    Log::error('Erreur upload Cloudinary: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur lors de l\'upload du fichier sur Cloudinary: ' . $e->getMessage()
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
                'file_url' => $pdfUrl,
                'file_name' => $fileName,
                'preview_url' => $thumbnailUrl,
                'previews' => json_encode($previews),
                'total_pages' => $totalPages,
                'pdf_public_id' => $pdfPublicId,
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
                'message' => 'Erreur lors de la soumission de l\'épreuve: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Affiche les détails d'une épreuve avec visionneuse PDF améliorée
     */
    public function show($id)
    {
        $exam = TeacherExam::with(['class', 'subject', 'teacher'])
            ->where('teacher_id', Auth::id())
            ->findOrFail($id);
            
        // Décoder les prévisualisations
        $previews = [];
        if ($exam->previews) {
            $previews = json_decode($exam->previews, true);
        }
        
        return view('teacher.exams.show', compact('exam', 'previews'));
    }

    /**
     * Supprime une épreuve
     */
    public function destroy($id)
    {
        try {
            $exam = TeacherExam::where('teacher_id', Auth::id())->findOrFail($id);
            
            // Supprimer les fichiers de Cloudinary
            try {
                $uploadApi = new UploadApi();
                
                // Supprimer le PDF
                if ($exam->pdf_public_id) {
                    $uploadApi->destroy($exam->pdf_public_id);
                }
                
                // Supprimer les previews
                if ($exam->previews) {
                    $previews = json_decode($exam->previews, true);
                    foreach ($previews as $preview) {
                        if (isset($preview['public_id'])) {
                            $uploadApi->destroy($preview['public_id']);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Erreur suppression Cloudinary: ' . $e->getMessage());
            }
            
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