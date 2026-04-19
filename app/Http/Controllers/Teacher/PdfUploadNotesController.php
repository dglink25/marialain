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
    private const MAX_PDF_SIZE_MB = 10;
    private const NOTE_MIN        = 0;
    private const NOTE_MAX        = 20;
    private const INTERRO_COUNT   = 5;
    private const DEVOIR_COUNT    = 2;

    // ─────────────────────────────────────────────────────────────────────────
    //  UPLOAD
    // ─────────────────────────────────────────────────────────────────────────
    public function upload(Request $request, int $classId, int $subjectId, int $trimestre)
    {
        $request->validate([
            'pdf_fiche' => [
                'required', 'file', 'mimes:pdf',
                'max:' . (self::MAX_PDF_SIZE_MB * 1024),
            ],
        ], [
            'pdf_fiche.required' => 'Veuillez sélectionner un fichier PDF.',
            'pdf_fiche.mimes'    => 'Le fichier doit être au format PDF.',
            'pdf_fiche.max'      => 'Le fichier ne doit pas dépasser ' . self::MAX_PDF_SIZE_MB . ' Mo.',
        ]);

        // ── Sécurité ──────────────────────────────────────────────────────────
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

        // Vérifier que la matière appartient à cette classe / année
        if (! ClassTeacherSubject::where('class_id', $classId)
                ->where('subject_id', $subjectId)
                ->where('academic_year_id', $activeYear->id)->exists()) {
            return back()->with('error', 'Cette matière n\'est pas assignée à cette classe pour l\'année académique active.');
        }

        // ── Élèves validés (filtrés class_id + academic_year_id) ─────────────
        $students = Student::where('class_id',        $classId)
            ->where('academic_year_id', $activeYear->id)
            ->where('is_validated',     1)
            ->orderBy('last_name')->orderBy('first_name')
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'Aucun élève validé trouvé pour cette classe dans l\'année académique active.');
        }

        // ── Sauvegarde temporaire du PDF ──────────────────────────────────────
        $pdfPath    = $request->file('pdf_fiche')->store('uploads/notes_pdf_temp', 'local');
        $pdfAbsPath = Storage::disk('local')->path($pdfPath);

        try {
            // ── Détection du type de PDF ──────────────────────────────────────
            [$rawText, $mode] = $this->detectAndExtract($pdfAbsPath);

            if ($rawText === null && $mode === 'error') {
                return back()->with('error',
                    'Impossible de lire le PDF. Vérifiez que pdftotext (poppler-utils) ' .
                    'et tesseract-ocr sont installés sur le serveur.');
            }

            // ── Parsing selon le mode ─────────────────────────────────────────
            if ($mode === 'digital') {
                $parsedRows = $this->parseDigitalPdf($rawText, $students);
            } else {
                $parsedRows = $this->parseScannedPdf($pdfAbsPath, $students);
            }

        } finally {
            Storage::disk('local')->delete($pdfPath);
        }

        // ── Notes existantes en base ──────────────────────────────────────────
        $existingGrades = Grade::where('class_id',        $classId)
            ->where('subject_id',       $subjectId)
            ->where('trimestre',        $trimestre)
            ->where('academic_year_id', $activeYear->id)
            ->get()
            ->groupBy(fn($g) => $g->student_id . '_' . $g->type . '_' . $g->sequence);

        $result = $this->buildVerificationTable($students, $parsedRows, $existingGrades, $mode);

        return back()->with([
            'upload_result'  => $result,
            'upload_classId' => $classId,
            'upload_subjId'  => $subjectId,
            'upload_trim'    => $trimestre,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  SAVE
    // ─────────────────────────────────────────────────────────────────────────
    public function save(Request $request, int $classId, int $subjectId, int $trimestre)
    {
        $teacher    = Auth::user();
        $activeYear = AcademicYear::where('active', true)->firstOrFail();

        $pivot = ClassTeacherSubject::where('class_id',        $classId)
            ->where('subject_id',       $subjectId)
            ->where('academic_year_id', $activeYear->id)
            ->where('teacher_id',       $teacher->id)
            ->first();

        if (! $pivot) {
            return back()->with('error', 'Accès refusé.');
        }

        $notesJson = $request->input('notes_json', '');
        if (empty($notesJson)) {
            return back()->with('error', 'Aucune donnée à sauvegarder.');
        }

        $notesData = json_decode($notesJson, true);
        if (! is_array($notesData) || empty($notesData)) {
            return back()->with('error', 'Format de données invalide.');
        }

        // IDs élèves autorisés pour cette classe + année
        $allowedIds = Student::where('class_id',        $classId)
            ->where('academic_year_id', $activeYear->id)
            ->where('is_validated',     1)
            ->pluck('id')->toArray();

        foreach ($notesData as $item) {
            $sid  = (int) ($item['student_id'] ?? 0);
            $type = $item['type'] ?? '';
            $seq  = (int) ($item['sequence'] ?? 0);
            $val  = $item['value'] ?? null;

            if (! in_array($sid, $allowedIds, true)) {
                return back()->with('error', "Élève ID {$sid} non autorisé. Sauvegarde annulée.");
            }
            if (! in_array($type, ['interrogation', 'devoir'], true)) {
                return back()->with('error', "Type de note invalide : {$type}.");
            }
            if ($type === 'interrogation' && ($seq < 1 || $seq > self::INTERRO_COUNT)) {
                return back()->with('error', "Séquence interrogation invalide : {$seq}.");
            }
            if ($type === 'devoir' && ($seq < 1 || $seq > self::DEVOIR_COUNT)) {
                return back()->with('error', "Séquence devoir invalide : {$seq}.");
            }
            if ($val !== null && ($val < self::NOTE_MIN || $val > self::NOTE_MAX)) {
                return back()->with('error', "Note hors plage : {$val}. Plage autorisée : 0–20.");
            }
        }

        DB::beginTransaction();
        try {
            foreach ($notesData as $item) {
                $val = $item['value'];
                if ($val === null) continue;

                Grade::updateOrCreate(
                    [
                        'student_id'       => (int) $item['student_id'],
                        'subject_id'       => $subjectId,
                        'class_id'         => $classId,
                        'academic_year_id' => $activeYear->id,
                        'trimestre'        => $trimestre,
                        'type'             => $item['type'],
                        'sequence'         => (int) $item['sequence'],
                    ],
                    ['value' => (float) $val, 'updated_by' => $teacher->id, 'updated_at' => now()]
                );
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PdfUploadNotesController::save DB error: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la sauvegarde. Veuillez réessayer.');
        }

        // Génération PDF récapitulatif
        try {
            $classe         = Classe::findOrFail($classId);
            $subject        = Subject::findOrFail($subjectId);
            $studentsForPdf = Student::where('class_id', $classId)
                ->where('academic_year_id', $activeYear->id)
                ->where('is_validated', 1)
                ->orderBy('last_name')->orderBy('first_name')->get();

            $listeEleves = [];
            foreach ($studentsForPdf as $student) {
                $grades   = Grade::where('student_id', $student->id)
                    ->where('subject_id', $subjectId)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('trimestre', $trimestre)->get();

                $interros = [1 => null, 2 => null, 3 => null, 4 => null, 5 => null];
                $devoirs  = [1 => null, 2 => null];

                foreach ($grades as $g) {
                    if ($g->type === 'interrogation' && isset($interros[$g->sequence])) {
                        $interros[$g->sequence] = $g->value;
                    } elseif ($g->type === 'devoir' && isset($devoirs[$g->sequence])) {
                        $devoirs[$g->sequence] = $g->value;
                    }
                }
                $listeEleves[] = ['student' => $student, 'interros' => $interros, 'devoirs' => $devoirs];
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
                . str_replace([' ', '/'], '_', $classe->name) . '_'
                . str_replace([' ', '/'], '_', $subject->name) . "_T{$trimestre}.pdf";

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            Log::error('PdfUploadNotesController::save PDF gen error: ' . $e->getMessage());
            return redirect()
                ->route('teacher.classes.notes.trimestres.subject', [$classId, $subjectId])
                ->with('success', 'Notes sauvegardées. (La génération du récapitulatif PDF a échoué.)');
        }
    }

    // =========================================================================
    //  DÉTECTION DU TYPE DE PDF
    // =========================================================================

    /**
     * Étape 1 : tente pdftotext -layout.
     * Si le résultat contient des matricules sur des lignes "propres" (< 400 chars)
     * accompagnés d'au moins un nom en majuscules → PDF numérique.
     * Sinon → PDF scanné (image) → retourne [null, 'scanned'] pour déclencher l'OCR.
     *
     * @return array{string|null, 'digital'|'scanned'|'error'}
     */
    private function detectAndExtract(string $pdfAbsPath): array
    {
        // Vérifier que pdftotext existe
        if (! trim(shell_exec('which pdftotext 2>/dev/null') ?? '')) {
            // Pas de pdftotext → essayer directement OCR
            if (trim(shell_exec('which tesseract 2>/dev/null') ?? '')) {
                return [null, 'scanned'];
            }
            return [null, 'error'];
        }

        $rawText = shell_exec('pdftotext -layout ' . escapeshellarg($pdfAbsPath) . ' - 2>/dev/null') ?? '';

        if ($this->isDigitalPdfText($rawText)) {
            return [$rawText, 'digital'];
        }

        // Vérifier que tesseract est disponible pour le fallback OCR
        if (! trim(shell_exec('which tesseract 2>/dev/null') ?? '')) {
            // Pas de tesseract → utiliser pdftotext dégradé si on a quelque chose
            if (strlen(trim($rawText)) > 0) {
                Log::warning('PdfUploadNotesController: tesseract absent, fallback pdftotext dégradé.');
                return [$rawText, 'digital']; // tentera quand même
            }
            return [null, 'error'];
        }

        return [null, 'scanned'];
    }

    /**
     * Détermine si le texte pdftotext est d'un PDF numérique.
     * Critère : au moins 1 ligne < 400 chars contenant un matricule (9-15 chiffres)
     * ET au moins 3 lettres majuscules consécutives (= nom lisible).
     */
    private function isDigitalPdfText(string $rawText): bool
    {
        if (strlen(trim($rawText)) === 0) return false;

        foreach (explode("\n", $rawText) as $line) {
            $line = trim($line);
            if (strlen($line) > 400) continue; // ligne trop longue = artefact de scan rotatif
            if (preg_match('/\b\d{9,15}\b/', $line) && preg_match('/[A-ZÀ-Ü]{3,}/', $line)) {
                return true;
            }
        }
        return false;
    }

    // =========================================================================
    //  PARSING PDF NUMÉRIQUE (pdftotext -layout)
    // =========================================================================

    /**
     * Parse le texte brut d'un PDF numérique généré par l'application.
     *
     * Format de ligne attendu (pdftotext -layout préserve l'alignement) :
     *   N°   MATRICULE      NOM     Prénom(s)     Sexe    I1   I2   I3   I4   I5   D1   D2
     *
     * Note importante :
     *   - "N° Matricule" dans le PDF = colonne `num_educ` dans la base de données.
     *   - Ne jamais fusionner ces deux dénominations dans la gestion d'erreur.
     */
    private function parseDigitalPdf(string $rawText, $students): array
    {
        $rows           = [];
        $matriculeIndex = $this->buildMatriculeIndex($students);

        foreach (explode("\n", $rawText) as $line) {
            $line = trim($line);
            if ($line === '' || strlen($line) > 400) continue;

            if (! preg_match('/\b(\d{9,15})\b/', $line, $mMatch)) continue;

            $matricule      = $mMatch[1];
            $afterMatricule = substr($line, strpos($line, $matricule) + strlen($matricule));

            // Découper par 2+ espaces / tabulations
            $tokens = preg_split('/\s{2,}|\t/', trim($afterMatricule), -1, PREG_SPLIT_NO_EMPTY);
            $tokens = array_values(array_filter($tokens, fn($t) => trim($t) !== ''));

            // Trouver le token Sexe (M ou F seul)
            $sexeIdx = null;
            foreach ($tokens as $i => $tok) {
                if (preg_match('/^[MFmf]$/u', trim($tok))) {
                    $sexeIdx = $i;
                    break;
                }
            }

            $nom    = null;
            $prenom = null;
            $noteTokens = [];

            if ($sexeIdx !== null) {
                $nom        = trim($tokens[0] ?? '');
                $prenom     = trim($tokens[1] ?? '');
                $noteTokens = array_slice($tokens, $sexeIdx + 1);
            } else {
                // Fallback : prendre les tokens qui ressemblent à des notes ou tirets
                foreach ($tokens as $tok) {
                    if ($this->parseNote($tok) !== null
                        || in_array(trim($tok), ['—', '–', '-', '--', ''], true)) {
                        $noteTokens[] = $tok;
                    }
                }
            }

            [$interros, $devoirs] = $this->tokenizeNotes($noteTokens);

            $rows[$matricule] = [
                'matricule'   => $matricule,
                'nom'         => $nom,
                'prenom'      => $prenom,
                'interros'    => $interros,
                'devoirs'     => $devoirs,
                'matched'     => isset($matriculeIndex[$matricule]),
                'student'     => $matriculeIndex[$matricule] ?? null,
                'ocr_mode'    => 'digital',
                'low_quality' => false,
            ];
        }

        return $rows;
    }

    // =========================================================================
    //  PARSING PDF SCANNÉ (Tesseract OCR + coordonnées TSV)
    // =========================================================================

    /**
     * Pipeline OCR complet pour un PDF scanné / image :
     *
     * 1. pdftoppm → image PNG 300 dpi (page 1)
     * 2. Rotation 90° CCW si l'image est portrait mais le contenu paysage
     * 3. Tesseract --psm 1 (auto-orient) avec preserve_interword_spaces
     *    → texte structuré pour identifier les élèves
     * 4. Tesseract --psm 1 TSV → coordonnées de chaque mot
     * 5. Détection des colonnes de notes via la position X de la colonne Sexe
     * 6. Attribution des notes par proximité X dans la bande Y de chaque élève
     *
     * Limites connues :
     * - Les notes MANUSCRITES ont une faible fiabilité OCR.
     * - Le résultat est présenté avec le statut 'low_quality' : l'enseignant
     *   DOIT vérifier visuellement chaque cellule avant de sauvegarder.
     */
    private function parseScannedPdf(string $pdfAbsPath, $students): array
    {
        $tmpDir = sys_get_temp_dir() . '/pdf_ocr_' . uniqid('', true);
        @mkdir($tmpDir, 0755, true);

        try {
            // ── 1. PDF → image PNG ────────────────────────────────────────────
            $imgBase = $tmpDir . '/page';
            shell_exec(sprintf(
                'pdftoppm -r 300 -png -f 1 -l 1 %s %s 2>/dev/null',
                escapeshellarg($pdfAbsPath), escapeshellarg($imgBase)
            ));

            // Trouver le fichier généré (page-1.png ou page-01.png)
            $imgPath = null;
            foreach (glob($tmpDir . '/page*.png') as $f) {
                $imgPath = $f;
                break;
            }

            if (! $imgPath || ! file_exists($imgPath)) {
                Log::warning('PdfUploadNotesController::parseScannedPdf: pdftoppm n\'a pas généré d\'image.');
                return [];
            }

            // ── 2. Rotation si portrait (contenu paysage scanné en portrait) ──
            [$imgW, $imgH] = @getimagesize($imgPath) ?: [0, 0];
            if ($imgH > $imgW) {
                $rotated = $tmpDir . '/page_rotated.png';
                shell_exec(sprintf('convert -rotate 90 %s %s 2>/dev/null',
                    escapeshellarg($imgPath), escapeshellarg($rotated)));
                if (file_exists($rotated)) {
                    $imgPath = $rotated;
                    [$imgW, $imgH] = @getimagesize($imgPath) ?: [$imgH, $imgW];
                }
            }

            // ── 3. Tesseract : texte avec espaces préservés ───────────────────
            $textOut = shell_exec(sprintf(
                'tesseract %s stdout -l fra --psm 1 -c preserve_interword_spaces=1 2>/dev/null',
                escapeshellarg($imgPath)
            )) ?? '';

            // ── 4. Tesseract : TSV avec coordonnées ───────────────────────────
            $tsvBase = $tmpDir . '/ocr';
            shell_exec(sprintf(
                'tesseract %s %s -l fra --psm 1 tsv 2>/dev/null',
                escapeshellarg($imgPath), escapeshellarg($tsvBase)
            ));
            $tsvWords = $this->loadTsvWords($tsvBase . '.tsv');

            // ── 5. Détecter les colonnes de notes ─────────────────────────────
            $noteColumns = $this->detectNoteColumns($tsvWords, $imgW);

            // ── 6. Extraire les lignes élèves ─────────────────────────────────
            $matriculeIndex = $this->buildMatriculeIndex($students);
            return $this->extractScannedRows($textOut, $tsvWords, $matriculeIndex, $noteColumns);

        } finally {
            // Nettoyage
            foreach (glob($tmpDir . '/*') as $f) @unlink($f);
            @rmdir($tmpDir);
        }
    }

    /**
     * Charge le TSV Tesseract et retourne les mots (level=5) avec leurs coordonnées.
     */
    private function loadTsvWords(string $tsvPath): array
    {
        if (! file_exists($tsvPath)) return [];

        $words  = [];
        $lines  = file($tsvPath, FILE_IGNORE_NEW_LINES);
        if (empty($lines)) return [];

        $header = explode("\t", array_shift($lines));

        foreach ($lines as $line) {
            $parts = explode("\t", $line);
            if (count($parts) < 12) continue;
            $row  = array_combine($header, $parts);
            if ((int) ($row['level'] ?? 0) !== 5) continue;
            $text = trim($row['text'] ?? '');
            if ($text === '') continue;

            $words[] = [
                'left'   => (int) ($row['left'] ?? 0),
                'top'    => (int) ($row['top'] ?? 0),
                'width'  => (int) ($row['width'] ?? 0),
                'height' => (int) ($row['height'] ?? 10),
                'conf'   => (float) ($row['conf'] ?? 0),
                'text'   => $text,
            ];
        }

        return $words;
    }

    /**
     * Détecte les centres X des 7 colonnes de notes (I1–I5, D1–D2)
     * en utilisant la colonne Sexe comme ancre de droite.
     *
     * Algorithme :
     * 1. Trouver tous les mots "M" ou "F" seuls (conf > 60) = valeurs Sexe
     * 2. Prendre leur médiane de (left + width) = bord droit de la col. Sexe
     * 3. Les notes occupent l'espace de sexe_right à ~96% de la largeur image
     * 4. Diviser cet espace en 7 colonnes égales
     */
    private function detectNoteColumns(array $tsvWords, int $imgWidth): array
    {
        $sexeWords = array_filter($tsvWords, fn($w) =>
            preg_match('/^[MFmf]$/u', $w['text']) && $w['conf'] > 60
        );

        $sexeRightX = null;
        if (! empty($sexeWords)) {
            $rights = array_map(fn($w) => $w['left'] + $w['width'], array_values($sexeWords));
            sort($rights);
            $sexeRightX = $rights[(int) (count($rights) / 2)];
        }

        // Fallback : les notes commencent à ~52% de la largeur
        if ($sexeRightX === null) {
            $sexeRightX = (int) ($imgWidth * 0.52);
        }

        $notesStart = $sexeRightX + (int) ($imgWidth * 0.01);
        $notesEnd   = (int) ($imgWidth * 0.96);
        $colCount   = self::INTERRO_COUNT + self::DEVOIR_COUNT; // 7
        $colWidth   = ($notesEnd - $notesStart) / $colCount;
        $halfW      = (int) ($colWidth * 0.45);

        $colNames = ['I1', 'I2', 'I3', 'I4', 'I5', 'D1', 'D2'];
        $columns  = [];

        for ($i = 0; $i < $colCount; $i++) {
            $cx             = (int) ($notesStart + $colWidth * $i + $colWidth / 2);
            $columns[$colNames[$i]] = ['cx' => $cx, 'half_w' => $halfW];
        }

        return $columns;
    }

    /**
     * Extrait les lignes élèves depuis le texte OCR + TSV.
     *
     * Pour chaque matricule trouvé dans le texte OCR :
     * 1. Localise le mot dans le TSV → récupère le centre Y de la ligne
     * 2. Pour chaque colonne de notes, cherche le mot TSV le plus proche
     *    du centre X de la colonne dans la bande Y ±35px de l'élève
     * 3. Essaie de parser le mot trouvé comme une note numérique
     */
    private function extractScannedRows(
        string $textOut,
        array  $tsvWords,
        array  $matriculeIndex,
        array  $noteColumns
    ): array {
        $rows = [];

        foreach (explode("\n", $textOut) as $line) {
            $line = trim($line);
            if ($line === '') continue;

            if (! preg_match('/\b(\d{9,15})\b/', $line, $mMatch)) continue;

            $matricule = $mMatch[1];
            if (isset($rows[$matricule])) continue; // doublon

            // ── Nom + prénom depuis la ligne OCR ─────────────────────────────
            [$nom, $prenom] = $this->extractNomPrenomFromLine($line, $matricule);

            // ── Centre Y de la ligne dans le TSV ─────────────────────────────
            $lineY = $this->findLineY($matricule, $line, $tsvWords);

            // ── Notes par position X ──────────────────────────────────────────
            $interros   = [1 => null, 2 => null, 3 => null, 4 => null, 5 => null];
            $devoirs    = [1 => null, 2 => null];
            $lowQuality = ($lineY === null || empty($noteColumns));

            if (! $lowQuality) {
                $bandY   = 35;
                $rowBand = array_filter($tsvWords, function ($w) use ($lineY, $bandY) {
                    $cy = $w['top'] + (int) ($w['height'] / 2);
                    return abs($cy - $lineY) <= $bandY;
                });

                foreach ($noteColumns as $colName => ['cx' => $cx, 'half_w' => $hw]) {
                    $best     = null;
                    $bestDist = PHP_INT_MAX;

                    foreach ($rowBand as $w) {
                        $wcx  = $w['left'] + (int) ($w['width'] / 2);
                        $dist = abs($wcx - $cx);
                        if ($dist <= $hw && $dist < $bestDist) {
                            $bestDist = $dist;
                            $best     = $w;
                        }
                    }

                    if ($best !== null) {
                        $noteVal = $this->parseNote($best['text']);
                        if ($noteVal === null) $lowQuality = true;

                        if (str_starts_with($colName, 'I')) {
                            $interros[(int) substr($colName, 1)] = $noteVal;
                        } else {
                            $devoirs[(int) substr($colName, 1)]  = $noteVal;
                        }
                    } else {
                        $lowQuality = true;
                    }
                }
            }

            $rows[$matricule] = [
                'matricule'   => $matricule,
                'nom'         => $nom,
                'prenom'      => $prenom,
                'interros'    => $interros,
                'devoirs'     => $devoirs,
                'matched'     => isset($matriculeIndex[$matricule]),
                'student'     => $matriculeIndex[$matricule] ?? null,
                'ocr_mode'    => 'scanned',
                'low_quality' => $lowQuality,
            ];
        }

        return $rows;
    }

    /**
     * Trouve le centre Y d'une ligne élève dans le TSV.
     * Essaie d'abord avec le matricule, puis avec les premiers mots de la ligne.
     */
    private function findLineY(string $matricule, string $line, array $tsvWords): ?int
    {
        // 1. Chercher le matricule complet dans le TSV
        foreach ($tsvWords as $w) {
            if (strpos($w['text'], $matricule) !== false || $w['text'] === $matricule) {
                return $w['top'] + (int) ($w['height'] / 2);
            }
        }

        // 2. Chercher un fragment du matricule (OCR peut segmenter)
        $fragment = substr($matricule, 0, 6);
        foreach ($tsvWords as $w) {
            if (strpos($w['text'], $fragment) !== false && $w['conf'] > 70) {
                return $w['top'] + (int) ($w['height'] / 2);
            }
        }

        // 3. Chercher les premiers mots de la ligne dans le TSV
        $words = array_slice(array_filter(preg_split('/\s+/', $line), fn($w) => strlen(trim($w)) >= 3), 0, 3);
        foreach ($words as $word) {
            $word = trim($word);
            if (preg_match('/^\d/', $word)) continue; // ignorer les chiffres
            foreach ($tsvWords as $w) {
                if (strpos($w['text'], $word) !== false && $w['conf'] > 70) {
                    return $w['top'] + (int) ($w['height'] / 2);
                }
            }
        }

        return null;
    }

    /**
     * Extrait nom + prénom depuis une ligne OCR.
     * Format attendu après le matricule : "NOM  Prénom(s)  M/F  notes..."
     */
    private function extractNomPrenomFromLine(string $line, string $matricule): array
    {
        $after  = substr($line, strpos($line, $matricule) + strlen($matricule));
        $tokens = preg_split('/\s{2,}|\t/', trim($after), -1, PREG_SPLIT_NO_EMPTY);
        $tokens = array_values(array_filter($tokens, fn($t) => trim($t) !== ''));

        $nom    = null;
        $prenom = null;

        foreach ($tokens as $i => $tok) {
            $t = trim($tok);
            if ($nom === null) {
                // Premier token majoritairement en majuscules = nom de famille
                // Nettoyer les artefacts OCR (virgules, chiffres isolés) au début
                $cleaned = preg_replace('/^[^A-ZÀ-Ü]+/u', '', strtoupper($t));
                if (strlen($cleaned) >= 2 && preg_match('/[A-ZÀ-Ü]{2,}/u', $cleaned)) {
                    $nom = $cleaned;
                    continue;
                }
            }
            if ($nom !== null && $prenom === null && ! preg_match('/^[MFmf]$/u', $t)) {
                $prenom = $t;
                break;
            }
        }

        return [$nom, $prenom];
    }

    // =========================================================================
    //  HELPERS COMMUNS
    // =========================================================================

    /**
     * Construit l'index num_educ → Student.
     *
     * IMPORTANT : dans le PDF, la colonne est intitulée "N° Matricule",
     * mais dans la base de données elle s'appelle `num_educ`.
     * On n'utilise QUE num_educ pour la correspondance BDD.
     */
    private function buildMatriculeIndex($students): array
    {
        $index = [];
        foreach ($students as $s) {
            $numEduc = trim((string) ($s->num_educ ?? ''));
            if ($numEduc !== '') {
                $index[$numEduc] = $s;
            }
        }
        return $index;
    }

    /**
     * Convertit un tableau de tokens bruts en tableaux interros/devoirs.
     */
    private function tokenizeNotes(array $tokens): array
    {
        $interros = [1 => null, 2 => null, 3 => null, 4 => null, 5 => null];
        $devoirs  = [1 => null, 2 => null];

        foreach (array_slice($tokens, 0, 7) as $i => $tok) {
            $note = $this->parseNote($tok);
            if ($i < 5) {
                $interros[$i + 1] = $note;
            } else {
                $devoirs[$i - 4]  = $note;
            }
        }

        return [$interros, $devoirs];
    }

    /**
     * Convertit un token texte en float (0–20) ou null.
     *
     * Gère :
     * - Tirets de toute sorte : "—", "–", "-", "--" → null (case vide)
     * - Virgule décimale française : "19,5" → 19.5
     * - Artefacts OCR autour du chiffre : "07" → 7.0
     * - Valeurs hors plage → null
     */
    private function parseNote(string $token): ?float
    {
        $t = trim($token);

        if (in_array($t, ['—', '–', '-', '--', '', 'null', 'N/A', 'n/a', '*', '.', '..'], true)) {
            return null;
        }

        $t = str_replace(',', '.', $t);
        // Nettoyer les caractères non numériques (artefacts OCR) en gardant chiffres, point, signe
        $cleaned = preg_replace('/[^0-9.\-]/', '', $t);

        if ($cleaned === '' || $cleaned === '.' || $cleaned === '-') {
            return null;
        }

        if (! is_numeric($cleaned)) {
            return null;
        }

        $val = (float) $cleaned;

        return ($val >= self::NOTE_MIN && $val <= self::NOTE_MAX) ? $val : null;
    }

    // =========================================================================
    //  TABLEAU DE VÉRIFICATION
    // =========================================================================

    /**
     * Construit le tableau de vérification complet.
     *
     * Statuts de cellule :
     *   'ok'          → note valide, aucun conflit
     *   'empty'       → case vide dans le PDF (normal)
     *   'conflict'    → une note existe déjà en base pour cette cellule
     *   'no_pdf_data' → élève absent du PDF (non trouvé par OCR)
     *   'low_quality' → OCR de faible confiance (PDF scanné) — à vérifier visuellement
     *
     * Sécurité :
     *   Seuls les élèves de $students (déjà filtrés class_id + academic_year_id + is_validated)
     *   sont traités. Tout matricule PDF non présent dans $students → "inconnu en base".
     */
    private function buildVerificationTable(
        $students,
        array $parsedRows,
        $existingGrades,
        string $mode
    ): array {
        $globalOk = true;

        // Matricules BDD (num_educ)
        $dbMatricules  = $students->map(fn($s) => (string) ($s->num_educ ?? ''))->filter()->values()->toArray();
        $pdfMatricules = array_keys($parsedRows);

        // Matricules inconnus (dans le PDF mais pas dans cette classe/année en BDD)
        $unknownInDb  = array_values(array_diff($pdfMatricules, $dbMatricules));
        // Élèves absents du PDF (en BDD mais non trouvés dans le PDF)
        $missingInPdf = array_values(array_diff($dbMatricules, $pdfMatricules));

        $conformite = [
            'total_bdd'      => count($dbMatricules),
            'total_pdf'      => count($pdfMatricules),
            'missing_in_pdf' => $missingInPdf,
            'unknown_in_db'  => $unknownInDb,
            'is_conforming'  => empty($missingInPdf) && empty($unknownInDb),
            'mode'           => $mode,
        ];

        if (! $conformite['is_conforming']) {
            $globalOk = false;
        }

        $rows = [];

        foreach ($students as $student) {
            $mat        = (string) ($student->num_educ ?? '');
            $pdfRow     = $parsedRows[$mat] ?? null;
            $lowQuality = (bool) ($pdfRow['low_quality'] ?? false);

            $interros = [];
            $devoirs  = [];

            for ($i = 1; $i <= self::INTERRO_COUNT; $i++) {
                $pdfVal   = $pdfRow['interros'][$i] ?? null;
                $key      = $student->id . '_interrogation_' . $i;
                $hasExist = $existingGrades->has($key);
                $existing = $hasExist ? $existingGrades[$key]->first() : null;
                $status   = $this->cellStatus($pdfVal, $hasExist, $pdfRow !== null, $lowQuality);

                if (! in_array($status, ['ok', 'empty', 'low_quality'], true)) {
                    $globalOk = false;
                }

                $interros[$i] = ['value' => $pdfVal, 'status' => $status, 'existing' => $existing?->value];
            }

            for ($i = 1; $i <= self::DEVOIR_COUNT; $i++) {
                $pdfVal   = $pdfRow['devoirs'][$i] ?? null;
                $key      = $student->id . '_devoir_' . $i;
                $hasExist = $existingGrades->has($key);
                $existing = $hasExist ? $existingGrades[$key]->first() : null;
                $status   = $this->cellStatus($pdfVal, $hasExist, $pdfRow !== null, $lowQuality);

                if (! in_array($status, ['ok', 'empty', 'low_quality'], true)) {
                    $globalOk = false;
                }

                $devoirs[$i] = ['value' => $pdfVal, 'status' => $status, 'existing' => $existing?->value];
            }

            // Vérification divergence de nom (ignorée si OCR faible confiance)
            $nameMatch = true;
            if ($pdfRow && ! empty($pdfRow['nom']) && ! $lowQuality) {
                $pdfNom = strtoupper(trim((string) $pdfRow['nom']));
                $dbNom  = strtoupper(trim($student->last_name));
                if ($pdfNom !== '' && $dbNom !== '' && $pdfNom !== $dbNom) {
                    $nameMatch = false;
                    $globalOk  = false;
                }
            }

            $rows[] = [
                'student'     => $student,
                'found_pdf'   => $pdfRow !== null,
                'name_match'  => $nameMatch,
                'pdf_nom'     => $pdfRow['nom']    ?? null,
                'pdf_prenom'  => $pdfRow['prenom'] ?? null,
                'low_quality' => $lowQuality,
                'interros'    => $interros,
                'devoirs'     => $devoirs,
            ];
        }

        // Pour un PDF scanné, si les seuls problèmes sont low_quality
        // (pas de conflits, pas d'élèves manquants), on autorise la sauvegarde
        // en laissant l'enseignant confirmer
        if ($mode === 'scanned') {
            $hasBlockingErrors = false;
            foreach ($rows as $row) {
                if (! $row['found_pdf']) { $hasBlockingErrors = true; break; }
                foreach (array_merge($row['interros'], $row['devoirs']) as $cell) {
                    if ($cell['status'] === 'conflict') { $hasBlockingErrors = true; break 2; }
                }
            }
            if (! $hasBlockingErrors && $conformite['is_conforming']) {
                $globalOk = true;
            }
        }

        return [
            'rows'       => $rows,
            'conformite' => $conformite,
            'global_ok'  => $globalOk,
        ];
    }

    /**
     * Détermine le statut d'une cellule de note.
     */
    private function cellStatus(
        ?float $pdfVal,
        bool   $hasExisting,
        bool   $foundInPdf,
        bool   $lowQuality = false
    ): string {
        if (! $foundInPdf)   return 'no_pdf_data';
        if ($hasExisting)    return 'conflict';
        if ($lowQuality)     return 'low_quality';
        if ($pdfVal === null) return 'empty';
        return 'ok';
    }
}