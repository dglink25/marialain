<?php

namespace App\Http\Controllers\Censeur;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\TeacherExam;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use setasign\Fpdi\Fpdi;

class CenseurExamController extends Controller{
    
    public function types($classeId){
        // Vérifier que l'ID est numérique
        if (!is_numeric($classeId)) {
            abort(404);
        }
        
        $classe = Classe::findOrFail($classeId);
        $activeYear = AcademicYear::where('active', true)->first();
        
        // Compter les élèves de la classe pour l'année active
        $studentCount = Student::where('class_id', $classeId)
            ->where('academic_year_id', $activeYear->id)
            ->count();
        
        // Initialiser les statistiques par trimestre
        $stats = [
            'par_trimestre' => []
        ];
        
        for ($t = 1; $t <= 3; $t++) {
            $stats['par_trimestre'][$t] = [
                'total' => 0,
                'devoirs' => [],
                'interrogations' => []
            ];
            
            // Compter les devoirs par numéro
            for ($n = 1; $n <= 2; $n++) {
                $count = TeacherExam::where('class_id', $classeId)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('trimestre', $t)
                    ->where('type', 'devoir')
                    ->where('numero_evaluation', $n)
                    ->count();
                
                $stats['par_trimestre'][$t]['devoirs'][$n] = $count;
                $stats['par_trimestre'][$t]['total'] += $count;
            }
            
            // Compter les interrogations par numéro
            for ($n = 1; $n <= 5; $n++) {
                $count = TeacherExam::where('class_id', $classeId)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('trimestre', $t)
                    ->where('type', 'interrogation')
                    ->where('numero_evaluation', $n)
                    ->count();
                
                $stats['par_trimestre'][$t]['interrogations'][$n] = $count;
                $stats['par_trimestre'][$t]['total'] += $count;
            }
        }
        
        return view('censeur.exams.types', compact('classe', 'activeYear', 'stats', 'studentCount'));
    }
    
    /**
     * Télécharge toutes les épreuves d'un type spécifique fusionnées en un seul PDF
     */
    public function downloadAll(Request $request, $classeId){
        try {
            // Vérifier que l'ID est numérique
            if (!is_numeric($classeId)) {
                abort(404);
            }
            
            $classe = Classe::findOrFail($classeId);
            $activeYear = AcademicYear::where('active', true)->first();
            
            $type = $request->get('type', 'devoir');
            $numero = $request->get('numero', 1);
            $trimestre = $request->get('trimestre', 1);
            
            // Récupérer TOUTES les épreuves du même type, même trimestre, même numéro pour toutes les matières
            $exams = TeacherExam::with(['classe', 'subject'])
                ->where('class_id', $classeId)
                ->where('academic_year_id', $activeYear->id)
                ->where('trimestre', $trimestre)
                ->where('type', $type)
                ->where('numero_evaluation', $numero)
                ->orderBy('subject_id') // Trier par matière
                ->get();
            
            if ($exams->isEmpty()) {
                return redirect()->back()->with('error', 'Aucune épreuve trouvée pour ces critères.');
            }
            
            // Générer le PDF fusionné
            return $this->mergeExamsPDF($exams, $classe, $type, $numero, $trimestre);
            
        } catch (\Exception $e) {
            Log::error('Erreur downloadAll: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la génération du PDF.');
        }
    }
 
    /**
     * Télécharge les copies via formulaire - fusionne toutes les épreuves correspondantes
     */
    public function downloadCopies(Request $request){
        try {
            $request->validate([
                'classe_id' => 'required|numeric|exists:classes,id',
                'trimestre' => 'required|in:1,2,3',
                'type' => 'required|in:devoir,interrogation',
                'numero' => 'required|integer|min:1|max:5'
            ]);
            
            $classe = Classe::findOrFail($request->classe_id);
            $activeYear = AcademicYear::where('active', true)->first();
            
            // Vérification supplémentaire pour les devoirs (max 2)
            if ($request->type === 'devoir' && $request->numero > 2) {
                return redirect()->back()->with('error', 'Numéro de devoir invalide.');
            }
            
            // Récupérer TOUTES les épreuves correspondant aux critères
            $exams = TeacherExam::with(['classe', 'subject'])
                ->where('class_id', $request->classe_id)
                ->where('academic_year_id', $activeYear->id)
                ->where('trimestre', $request->trimestre)
                ->where('type', $request->type)
                ->where('numero_evaluation', $request->numero)
                ->orderBy('subject_id')
                ->get();
            
            if ($exams->isEmpty()) {
                return redirect()->back()->with('error', 'Aucune épreuve trouvée pour ces critères.');
            }
            
            // Générer le PDF fusionné
            return $this->mergeExamsPDF($exams, $classe, $request->type, $request->numero, $request->trimestre);
            
        } catch (\Exception $e) {
            Log::error('Erreur downloadCopies: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la génération du PDF.');
        }
    }

    private function mergeExamsPDF($exams, $classe, $type, $numero, $trimestre) {
        try {
            // Initialiser FPDI
            $pdf = new Fpdi();
            $tempFiles = []; // Pour nettoyer plus tard
            
            foreach ($exams as $index => $exam) {
                try {
                    // Télécharger le PDF depuis Cloudinary
                    $pdfContent = file_get_contents($exam->file_url);
                    $tempFile = tempnam(sys_get_temp_dir(), 'exam_') . '_' . $exam->id . '.pdf';
                    file_put_contents($tempFile, $pdfContent);
                    $tempFiles[] = $tempFile;
                    
                    // Obtenir le nombre de pages
                    $pageCount = $this->getPDFPageCount($tempFile);
                    
                    // Ajouter toutes les pages de l'épreuve
                    for ($pageNum = 1; $pageNum <= $pageCount; $pageNum++) {
                        $pdf->setSourceFile($tempFile);
                        $templateId = $pdf->importPage($pageNum);
                        $size = $pdf->getTemplateSize($templateId);
                        
                        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                        $pdf->useTemplate($templateId);
                        
                        // Ajouter un en-tête sur la première page de chaque matière
                        if ($pageNum === 1) {
                            $this->addExamHeader($pdf, $exam);
                        }
                    }
                    
                } catch (\Exception $e) {
                    Log::error("Erreur lors du traitement de l'épreuve ID {$exam->id}: " . $e->getMessage());
                    // Continuer avec les autres épreuves
                }
            }
            
            // Nettoyer les fichiers temporaires
            foreach ($tempFiles as $tempFile) {
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            }

            // Générer le nom du fichier
            $typeLibelle = $type === 'devoir' ? 'Devoirs' : 'Interrogations';
            $classeName = $classe->name;

            // Nettoyer uniquement les caractères interdits par Windows
            $classeName = preg_replace('/[\/\\\\:*?"<>|]/', '', $classeName);
            $classeName = str_replace(' ', '_', $classeName);

            $fileName = sprintf(
                '%s_%s_T%s_N%s_%s.pdf',
                $classeName,
                $typeLibelle,
                $trimestre,
                $numero,
                date('Ymd')
            );

            // Encodage UTF-8 pour les navigateurs modernes
            $encodedFileName = rawurlencode($fileName);

            return response($pdf->Output('S', $fileName))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"$fileName\"; filename*=UTF-8''$encodedFileName");


            
            // Envoyer le PDF au navigateur
            return response($pdf->Output('S', $fileName))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
            
        } catch (\Exception $e) {
            Log::error('Erreur mergeExamsPDF: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Ajoute un en-tête sur la page pour identifier l'épreuve
     */
    private function addExamHeader($pdf, $exam) {
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(41, 128, 185); // Bleu
        $pdf->SetXY(10, 10);
        $pdf->Cell(0, 5, $exam->subject->name . ' - ' . $exam->titre, 0, 0, 'L');
        
        $pdf->SetFont('Helvetica', 'I', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY(10, 15);
        $pdf->Cell(0, 5, 'Enseignant: ' . ($exam->teacher->name ?? 'Non spécifié'), 0, 0, 'L');
    }

    /**
     * Ajoute une page de séparation pour une nouvelle matière
     */
    private function addSubjectHeaderPage($pdf, $subjectName, $examTitle) {
        $pdf->SetFont('Helvetica', 'B', 16);
        $pdf->SetTextColor(52, 73, 94); // Gris foncé
        $pdf->SetY(100);
        $pdf->Cell(0, 10, $subjectName, 0, 1, 'C');
        
        $pdf->SetFont('Helvetica', '', 12);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 10, $examTitle, 0, 1, 'C');
        
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line(50, 130, 250, 130);
    }

    private function getPDFPageCount($filePath) {
        try {
            $pdf = new Fpdi();
            return $pdf->setSourceFile($filePath);
        } catch (\Exception $e) {
            Log::error('Erreur getPDFPageCount: ' . $e->getMessage());
            return 1;
        }
    }

    public function trimestre($classeId, $trimestre){
        if (!is_numeric($classeId)) {
            abort(404);
        }
        
        $classe = Classe::findOrFail($classeId);
        $activeYear = AcademicYear::where('active', true)->first();
        
        if (!in_array($trimestre, [1, 2, 3])) {
            abort(404);
        }
        
        return view('censeur.exams.trimestre', compact('classe', 'trimestre', 'activeYear'));
    }
    
    public function list(Request $request, $classeId, $trimestre, $type, $numero) {
        if (!is_numeric($classeId)) {
            abort(404);
        }
        
        $classe = Classe::findOrFail($classeId);
        $activeYear = AcademicYear::where('active', true)->firstOrFail();
        
        // Vérifications
        if (!in_array($trimestre, [1, 2, 3])) {
            abort(404);
        }
        
        if (!in_array($type, ['devoir', 'interrogation'])) {
            abort(404);
        }
        
        $maxNumero = $type === 'devoir' ? 2 : 5;
        if ($numero < 1 || $numero > $maxNumero) {
            abort(404);
        }
        
        // Requête de base
        $query = TeacherExam::with(['subject', 'teacher'])
            ->where('class_id', $classeId)
            ->where('academic_year_id', $activeYear->id)
            ->where('trimestre', $trimestre)
            ->where('type', $type)
            ->where('numero_evaluation', $numero);
        
        // Filtre par matière
        if ($request->filled('subject_id') && is_numeric($request->subject_id)) {
            $query->where('subject_id', $request->subject_id);
        }
        
        // Tri
        $sort = $request->get('sort', 'recent');
        switch ($sort) {
            case 'recent':
                $query->orderBy('created_at', 'desc');
                break;
            case 'ancien':
                $query->orderBy('created_at', 'asc');
                break;
            case 'matiere':
                $query->join('subjects', 'teacher_exams.subject_id', '=', 'subjects.id')
                      ->orderBy('subjects.name')
                      ->select('teacher_exams.*');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
        
        $exams = $query->paginate(12);
        
        // Récupérer toutes les matières de cette classe
        $subjects = Subject::whereHas('classes', function($q) use ($classeId) {
            $q->where('class_id', $classeId);
        })->orderBy('name')->get();
        
        return view('censeur.exams.list', compact(
            'classe', 
            'trimestre',
            'type', 
            'numero', 
            'exams', 
            'activeYear', 
            'subjects'
        ));
    }
}