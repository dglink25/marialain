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
            
            // Récupérer l'épreuve avec ses relations
            $exam = TeacherExam::with(['classe', 'subject'])
                ->where('class_id', $classeId)
                ->where('academic_year_id', $activeYear->id)
                ->where('trimestre', $trimestre)
                ->where('type', $type)
                ->where('numero_evaluation', $numero)
                ->first();
            
            if (!$exam) {
                return redirect()->back()->with('error', 'Aucune épreuve trouvée pour ces critères.');
            }
            
            // Compter les élèves
            $studentCount = Student::where('class_id', $classeId)
                ->where('academic_year_id', $activeYear->id)
                ->count();
            
            // Télécharger le PDF original et générer les copies
            return $this->generateCopiesPDF($exam, $studentCount);
            
        } catch (\Exception $e) {
            Log::error('Erreur downloadAll: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la génération du PDF.');
        }
    }
 
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
            
            // Récupérer l'épreuve avec ses relations
            $exam = TeacherExam::with(['classe', 'subject'])
                ->where('class_id', $request->classe_id)
                ->where('academic_year_id', $activeYear->id)
                ->where('trimestre', $request->trimestre)
                ->where('type', $request->type)
                ->where('numero_evaluation', $request->numero)
                ->first();
            
            if (!$exam) {
                return redirect()->back()->with('error', 'Aucune épreuve trouvée pour ces critères.');
            }
            
            // Compter les élèves
            $studentCount = Student::where('class_id', $request->classe_id)
                ->where('academic_year_id', $activeYear->id)
                ->count();
            
            // Générer le PDF avec les copies
            return $this->generateCopiesPDF($exam, $studentCount);
            
        } catch (\Exception $e) {
            Log::error('Erreur downloadCopies: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la génération du PDF.');
        }
    }

    private function generateCopiesPDF($exam, $studentCount) {
        try {
            // Vérifier que la relation classe est chargée
            if (!$exam->relationLoaded('classe')) {
                $exam->load('classe');
            }
            
            // Télécharger le PDF original depuis Cloudinary
            $pdfContent = file_get_contents($exam->file_url);
            $tempFile = tempnam(sys_get_temp_dir(), 'exam_') . '.pdf';
            file_put_contents($tempFile, $pdfContent);
            
            // Initialiser FPDI
            $pdf = new Fpdi();
            
            // Calculer le nombre total de copies (épreuve + 1 copie par élève)
            $totalCopies = $studentCount + 1; // +1 pour l'enseignant
            
            // Obtenir le nombre de pages du document original
            $pageCount = $this->getPDFPageCount($tempFile);
            
            // Pour chaque copie nécessaire
            for ($copy = 1; $copy <= $totalCopies; $copy++) {
                // Ajouter toutes les pages de l'épreuve
                for ($pageNum = 1; $pageNum <= $pageCount; $pageNum++) {
                    $pdf->setSourceFile($tempFile);
                    $templateId = $pdf->importPage($pageNum);
                    $size = $pdf->getTemplateSize($templateId);
                    
                    // Ajouter la page
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);
                    
                    // Ajouter un numéro de copie sur la première page de chaque copie
                    if ($pageNum === 1) {
                        $pdf->SetFont('Helvetica', 'I', 8);
                        $pdf->SetTextColor(150, 150, 150);
                        $pdf->SetXY(10, 10);
                        $pdf->Cell(0, 5, 'Copie N°: ' . $copy . ' / ' . $totalCopies, 0, 0, 'R');
                    }
                }
            }
            
            // Nettoyer le fichier temporaire
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            
            // Générer le nom du fichier
            $classeName = $exam->classe ? $exam->classe->name : 'classe';
            $fileName = sprintf(
                '%s_%s_T%s_N%s_%scopies.pdf',
                str_replace(' ', '_', $classeName),
                $exam->type,
                $exam->trimestre,
                $exam->numero_evaluation,
                $totalCopies
            );
            
            // Envoyer le PDF au navigateur
            return response($pdf->Output('S', $fileName))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
            
        } catch (\Exception $e) {
            Log::error('Erreur generateCopiesPDF: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getPDFPageCount($filePath) {
        try {
            $pdf = new Fpdi();
            return $pdf->setSourceFile($filePath);
        } catch (\Exception $e) {
            Log::error('Erreur getPDFPageCount: ' . $e->getMessage());
            return 1; // Par défaut, retourner 1 page
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