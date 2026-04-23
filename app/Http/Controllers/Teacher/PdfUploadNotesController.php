<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Grade;
use App\Models\AcademicYear;
use App\Models\ClassTeacherSubject;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfUploadNotesController extends Controller
{
    // ─── Constantes ───────────────────────────────────────────────────────────
    private const MAX_PDF_SIZE_MB = 10;
    private const NOTE_MIN        = 0;
    private const NOTE_MAX        = 20;
    private const INTERRO_COUNT   = 5;
    private const DEVOIR_COUNT    = 2;

    // ─────────────────────────────────────────────────────────────────────────
    //  UPLOAD — analyse le PDF et stocke le résultat en session
    // ─────────────────────────────────────────────────────────────────────────
    public function upload(Request $request, int $classId, int $subjectId, int $trimestre)
    {
        // ── 1. Validation du fichier ──────────────────────────────────────────
        $request->validate([
            'pdf_fiche' => [
                'required',
                'file',
                'mimes:pdf',
                'max:' . (self::MAX_PDF_SIZE_MB * 1024),
            ],
        ], [
            'pdf_fiche.required' => 'Veuillez sélectionner un fichier PDF.',
            'pdf_fiche.mimes'    => 'Le fichier doit être au format PDF.',
            'pdf_fiche.max'      => 'Le fichier ne doit pas dépasser ' . self::MAX_PDF_SIZE_MB . ' Mo.',
        ]);

        // ── 2. Sécurité — vérifier que l'enseignant est autorisé ─────────────
        $teacher    = Auth::user();
        $activeYear = AcademicYear::where('active', true)->firstOrFail();

        $pivot = ClassTeacherSubject::where('class_id',        $classId)
            ->where('subject_id',       $subjectId)
            ->where('academic_year_id', $activeYear->id)
            ->where('teacher_id',       $teacher->id)
            ->first();

        if (! $pivot) {
            return back()->with('error', 'Accès refusé : vous n\'êtes pas autorisé à modifier les notes de cette matière/classe.');
        }

  
        $classe = Classe::findOrFail($classId);

        $students = Student::where('class_id',        $classId)
            ->where('academic_year_id', $activeYear->id)
            ->where('is_validated',     1)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'Aucun élève validé trouvé pour cette classe dans l\'année académique active.');
        }

        $subject = Subject::findOrFail($subjectId);

        // Vérifier que la matière appartient à cette classe / année
        $subjectBelongs = ClassTeacherSubject::where('class_id',        $classId)
            ->where('subject_id',       $subjectId)
            ->where('academic_year_id', $activeYear->id)
            ->exists();

        if (! $subjectBelongs) {
            return back()->with('error', 'Cette matière n\'est pas assignée à cette classe pour l\'année académique active.');
        }

        // ── 4. Enregistrement temporaire + extraction texte ───────────────────
        $pdfPath    = $request->file('pdf_fiche')->store('uploads/notes_pdf_temp', 'local');
        $pdfAbsPath = Storage::disk('local')->path($pdfPath);
        $rawText    = '';
        $pdfOk      = false;

        try {
            // -layout conserve la mise en page tabulaire
            $cmd     = 'pdftotext -layout ' . escapeshellarg($pdfAbsPath) . ' -';
            $rawText = shell_exec($cmd) ?? '';
            $pdfOk   = strlen(trim($rawText)) > 0;
        } catch (\Throwable $e) {
            Log::error('PdfUploadNotesController::upload pdftotext error: ' . $e->getMessage());
        } finally {
            Storage::disk('local')->delete($pdfPath); // nettoyage immédiat
        }

        if (! $pdfOk) {
            return back()->with('error', 'Impossible de lire le contenu du PDF. Assurez-vous que le fichier contient du texte sélectionnable (non scanné image).');
        }

        // ── 5. Parsing du PDF ─────────────────────────────────────────────────
        $parsedRows = $this->parsePdfText($rawText, $students);

        // ── 6. Vérification des notes déjà présentes en base ─────────────────
        $existingGrades = Grade::where('class_id',        $classId)
            ->where('subject_id',       $subjectId)
            ->where('trimestre',        $trimestre)
            ->where('academic_year_id', $activeYear->id)
            ->get()
            ->groupBy(fn($g) => $g->student_id . '_' . $g->type . '_' . $g->sequence);

        // ── 7. Construction du tableau de vérification ───────────────────────
        $result = $this->buildVerificationTable($students, $parsedRows, $existingGrades, $classId, $activeYear->id);

        // ── 8. Retour en session (pour affichage modal côté blade) ────────────
        return back()->with([
            'upload_result'  => $result,
            'upload_classId' => $classId,
            'upload_subjId'  => $subjectId,
            'upload_trim'    => $trimestre,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  SAVE — persiste les notes en base et génère le PDF récapitulatif
    // ─────────────────────────────────────────────────────────────────────────
    public function save(Request $request, int $classId, int $subjectId, int $trimestre)
    {
        $teacher    = Auth::user();
        $activeYear = AcademicYear::where('active', true)->firstOrFail();

        // ── Sécurité ──────────────────────────────────────────────────────────
        $pivot = ClassTeacherSubject::where('class_id',        $classId)
            ->where('subject_id',       $subjectId)
            ->where('academic_year_id', $activeYear->id)
            ->where('teacher_id',       $teacher->id)
            ->first();

        if (! $pivot) {
            return back()->with('error', 'Accès refusé.');
        }

        // ── Décoder les notes JSON ────────────────────────────────────────────
        $notesJson = $request->input('notes_json', '');
        if (empty($notesJson)) {
            return back()->with('error', 'Aucune donnée à sauvegarder.');
        }

        $notesData = json_decode($notesJson, true);
        if (! is_array($notesData) || empty($notesData)) {
            return back()->with('error', 'Format de données invalide.');
        }

        $allowedStudentIds = Student::where('class_id',        $classId)
            ->where('academic_year_id', $activeYear->id)
            ->where('is_validated',     1)
            ->pluck('id')
            ->toArray();

        // ── Validation stricte avant toute écriture ───────────────────────────
        foreach ($notesData as $item) {
            $studentId = (int) ($item['student_id'] ?? 0);
            $type      = $item['type'] ?? '';
            $sequence  = (int) ($item['sequence'] ?? 0);
            $val       = $item['value'] ?? null;

            // Vérifier que l'élève appartient bien à cette classe / année
            if (! in_array($studentId, $allowedStudentIds, true)) {
                return back()->with('error', "Élève ID {$studentId} non autorisé pour cette classe/année. Sauvegarde annulée.");
            }

            if (! in_array($type, ['interrogation', 'devoir'], true)) {
                return back()->with('error', "Type de note invalide : {$type}.");
            }

            if ($type === 'interrogation' && ($sequence < 1 || $sequence > self::INTERRO_COUNT)) {
                return back()->with('error', "Séquence interrogation invalide : {$sequence}.");
            }

            if ($type === 'devoir' && ($sequence < 1 || $sequence > self::DEVOIR_COUNT)) {
                return back()->with('error', "Séquence devoir invalide : {$sequence}.");
            }

            if ($val !== null && ($val < self::NOTE_MIN || $val > self::NOTE_MAX)) {
                return back()->with('error', "Note hors plage détectée : {$val}. Plage autorisée : 0–20.");
            }
        }

        // ── Sauvegarde en transaction ─────────────────────────────────────────
        DB::beginTransaction();
        try {
            foreach ($notesData as $item) {
                $studentId = (int) $item['student_id'];
                $type      = $item['type'];
                $sequence  = (int) $item['sequence'];
                $val       = $item['value'];

                if ($val === null) {
                    continue; // ne pas écraser une note existante par null
                }

                Grade::updateOrCreate(
                    [
                        'student_id'       => $studentId,
                        'subject_id'       => $subjectId,
                        'class_id'         => $classId,
                        'academic_year_id' => $activeYear->id,
                        'trimestre'        => $trimestre,
                        'type'             => $type,
                        'sequence'         => $sequence,
                    ],
                    [
                        'value'      => (float) $val,
                        'updated_by' => $teacher->id,
                        'updated_at' => now(),
                    ]
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PdfUploadNotesController::save DB error: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la sauvegarde en base de données. Veuillez réessayer.');
        }

        // ── Génération PDF récapitulatif ──────────────────────────────────────
        try {
            $classe  = Classe::findOrFail($classId);
            $subject = Subject::findOrFail($subjectId);

            $studentsForPdf = Student::where('class_id',        $classId)
                ->where('academic_year_id', $activeYear->id)
                ->where('is_validated',     1)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            $listeEleves = [];
            foreach ($studentsForPdf as $student) {
                $grades = Grade::where('student_id',       $student->id)
                    ->where('subject_id',       $subjectId)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('trimestre',        $trimestre)
                    ->get();

                $interros = [1 => null, 2 => null, 3 => null, 4 => null, 5 => null];
                $devoirs  = [1 => null, 2 => null];

                foreach ($grades as $g) {
                    if ($g->type === 'interrogation' && isset($interros[$g->sequence])) {
                        $interros[$g->sequence] = $g->value;
                    } elseif ($g->type === 'devoir' && isset($devoirs[$g->sequence])) {
                        $devoirs[$g->sequence] = $g->value;
                    }
                }

                $listeEleves[] = [
                    'student'  => $student,
                    'interros' => $interros,
                    'devoirs'  => $devoirs,
                ];
            }

            $pdf = Pdf::loadView('censeur.notes.pdf.liste_eleves_pdf', [
                'classe'       => $classe,
                'subject'      => $subject,
                'subjectPivot' => $pivot,
                'trimestre'    => $trimestre,
                'activeYear'   => $activeYear,
                'listeEleves'  => $listeEleves,
                'dateDownload' => now()->locale('fr')->isoFormat('D MMMM YYYY'),
            ])->setPaper('a4', 'landscape');

            $filename = 'Liste_Eleves_'
                . str_replace([' ', '/'], '_', $classe->name)
                . '_'
                . str_replace([' ', '/'], '_', $subject->name)
                . "_T{$trimestre}.pdf";

            return $pdf->download($filename);

        } catch (\Throwable $e) {
            Log::error('PdfUploadNotesController::save PDF gen error: ' . $e->getMessage());
            return redirect()
                ->route('teacher.classes.notes.trimestres.subject', [$classId, $subjectId])
                ->with('success', 'Notes sauvegardées avec succès. (La génération du PDF récapitulatif a échoué.)');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  PARSING — extrait les données du texte PDF
    // ─────────────────────────────────────────────────────────────────────────
    /**
     * Analyse le texte brut du PDF et retourne un tableau indexé par num_educ.
     * On utilise num_educ (= le matricule côté BDD) pour la correspondance.
     * Le PDF appelle cette colonne "N° Matricule" mais c'est bien num_educ en base.
     */
    private function parsePdfText(string $rawText, $students): array
    {
        $rows = [];

        // Construire l'index num_educ → Student (uniquement les élèves de la requête)
        $matriculeIndex = [];
        foreach ($students as $s) {
            $numEduc = trim((string) ($s->num_educ ?? ''));
            if ($numEduc !== '') {
                $matriculeIndex[$numEduc] = $s;
            }
        }

        $lines = explode("\n", $rawText);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // Chercher un matricule dans la ligne (séquence de 9 à 15 chiffres)
            if (! preg_match('/\b(\d{9,15})\b/', $line, $mMatch)) {
                continue;
            }

            $matricule      = $mMatch[1];
            $afterMatricule = substr($line, strpos($line, $matricule) + strlen($matricule));

            // Séparer les colonnes (plusieurs espaces ou tabulations)
            $tokens = preg_split('/\s{2,}|\t/', trim($afterMatricule), -1, PREG_SPLIT_NO_EMPTY);

            // Localiser le token sexe (M ou F seul)
            $sexeIdx    = null;
            foreach ($tokens as $idx => $tok) {
                if (preg_match('/^[MFmf]$/u', trim($tok))) {
                    $sexeIdx = $idx;
                    break;
                }
            }

            $nom        = null;
            $prenom     = null;
            $sexe       = null;
            $noteTokens = [];

            if ($sexeIdx !== null) {
                $nom        = $tokens[0] ?? null;
                $prenom     = $tokens[1] ?? null;
                $sexe       = strtoupper(trim($tokens[$sexeIdx]));
                $noteTokens = array_slice($tokens, $sexeIdx + 1);
            } else {
                // Fallback : récupérer les tokens qui ressemblent à des notes ou à des tirets
                foreach ($tokens as $tok) {
                    $cleaned = $this->parseNote($tok);
                    if ($cleaned !== null || in_array(trim($tok), ['—', '-', '--', '', 'null'], true)) {
                        $noteTokens[] = $tok;
                    }
                }
            }

            // Parser 7 colonnes : I1 I2 I3 I4 I5 D1 D2
            $interros = [1 => null, 2 => null, 3 => null, 4 => null, 5 => null];
            $devoirs  = [1 => null, 2 => null];

            for ($i = 0; $i < min(count($noteTokens), 7); $i++) {
                $note = $this->parseNote($noteTokens[$i]);
                if ($i < 5) {
                    $interros[$i + 1] = $note;
                } else {
                    $devoirs[$i - 4] = $note;
                }
            }

            $rows[$matricule] = [
                'matricule' => $matricule,
                'nom'       => $nom,
                'prenom'    => $prenom,
                'sexe'      => $sexe,
                'interros'  => $interros,
                'devoirs'   => $devoirs,
                'matched'   => isset($matriculeIndex[$matricule]),
                'student'   => $matriculeIndex[$matricule] ?? null,
            ];
        }

        return $rows;
    }

    /**
     * Convertit un token texte en float ou null.
     * Gère : "—", "-", "", "19,5", "19.5", "0", "20"
     */
    private function parseNote(string $token): ?float
    {
        $t = trim($token);

        if (in_array($t, ['—', '-', '--', '', 'null', 'N/A', 'n/a', '*'], true)) {
            return null;
        }

        $t = str_replace(',', '.', $t);

        if (! is_numeric($t)) {
            return null;
        }

        $val = (float) $t;

        if ($val < self::NOTE_MIN || $val > self::NOTE_MAX) {
            return null; // hors plage → non lisible
        }

        return $val;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  TABLEAU DE VÉRIFICATION
    // ─────────────────────────────────────────────────────────────────────────
    /**
     * Construit le tableau de vérification avec :
     * - conformité de la liste (nb élèves BDD vs PDF, matricules manquants/inconnus)
     * - statut de chaque cellule (ok / empty / conflict / no_pdf_data)
     * - vérification du nom (divergence BDD vs PDF)
     *
     * Sécurité renforcée : on vérifie que les élèves du PDF appartiennent
     * bien à la classe ET à l'année académique active.
     */
    private function buildVerificationTable(
        $students,
        array $parsedRows,
        $existingGrades,
        int $classId,
        int $academicYearId
    ): array {
        $globalOk = true;

        // Matricules de référence BDD (num_educ)
        $dbMatricules  = $students->map(fn($s) => (string) ($s->num_educ ?? ''))->filter()->values()->toArray();
        $pdfMatricules = array_keys($parsedRows);

        // Matricules du PDF qui ne correspondent à AUCUN élève de cette classe/année
        $unknownInDb  = array_diff($pdfMatricules, $dbMatricules);

        // Élèves BDD absents du PDF
        $missingInPdf = array_diff($dbMatricules, $pdfMatricules);

        $conformite = [
            'total_bdd'      => count($dbMatricules),
            'total_pdf'      => count($pdfMatricules),
            'missing_in_pdf' => array_values($missingInPdf),
            'unknown_in_db'  => array_values($unknownInDb),
            'is_conforming'  => empty($missingInPdf) && empty($unknownInDb),
        ];

        if (! $conformite['is_conforming']) {
            $globalOk = false;
        }

        $rows = [];

        foreach ($students as $student) {
            $mat    = (string) ($student->num_educ ?? '');
            $pdfRow = $parsedRows[$mat] ?? null;

            $interros  = [];
            $devoirs   = [];

            for ($i = 1; $i <= self::INTERRO_COUNT; $i++) {
                $pdfVal   = $pdfRow['interros'][$i] ?? null;
                $key      = $student->id . '_interrogation_' . $i;
                $hasExist = $existingGrades->has($key);
                $existing = $hasExist ? $existingGrades[$key]->first() : null;
                $status   = $this->cellStatus($pdfVal, $hasExist, $pdfRow !== null);

                if (! in_array($status, ['ok', 'empty'], true)) {
                    $globalOk = false;
                }

                $interros[$i] = [
                    'value'    => $pdfVal,
                    'status'   => $status,
                    'existing' => $existing?->value,
                ];
            }

            for ($i = 1; $i <= self::DEVOIR_COUNT; $i++) {
                $pdfVal   = $pdfRow['devoirs'][$i] ?? null;
                $key      = $student->id . '_devoir_' . $i;
                $hasExist = $existingGrades->has($key);
                $existing = $hasExist ? $existingGrades[$key]->first() : null;
                $status   = $this->cellStatus($pdfVal, $hasExist, $pdfRow !== null);

                if (! in_array($status, ['ok', 'empty'], true)) {
                    $globalOk = false;
                }

                $devoirs[$i] = [
                    'value'    => $pdfVal,
                    'status'   => $status,
                    'existing' => $existing?->value,
                ];
            }

            // Vérification divergence de nom
            $nameMatch = true;
            if ($pdfRow && ! empty($pdfRow['nom'])) {
                $pdfNom = strtoupper(trim($pdfRow['nom']));
                $dbNom  = strtoupper(trim($student->last_name));
                if ($pdfNom !== '' && $dbNom !== '' && $pdfNom !== $dbNom) {
                    $nameMatch = false;
                    $globalOk  = false;
                }
            }

            $rows[] = [
                'student'    => $student,
                'found_pdf'  => $pdfRow !== null,
                'name_match' => $nameMatch,
                'pdf_nom'    => $pdfRow['nom'] ?? null,
                'pdf_prenom' => $pdfRow['prenom'] ?? null,
                'interros'   => $interros,
                'devoirs'    => $devoirs,
            ];
        }

        return [
            'rows'       => $rows,
            'conformite' => $conformite,
            'global_ok'  => $globalOk,
        ];
    }

    private function cellStatus(?float $pdfVal, bool $hasExisting, bool $foundInPdf): string
    {
        if (! $foundInPdf) {
            return 'no_pdf_data';
        }

        if ($hasExisting) {
            return 'conflict';
        }

        if ($pdfVal === null) {
            return 'empty';
        }

        return 'ok';
    }
}