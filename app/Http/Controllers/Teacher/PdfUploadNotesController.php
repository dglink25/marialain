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

    // =========================================================================
    //  UPLOAD
    // =========================================================================
    public function upload(Request $request, int $classId, int $subjectId, int $trimestre)
    {
        $request->validate([
            'pdf_fiche' => ['required','file','mimes:pdf','max:'.(self::MAX_PDF_SIZE_MB*1024)],
        ], [
            'pdf_fiche.required' => 'Veuillez sélectionner un fichier PDF.',
            'pdf_fiche.mimes'    => 'Le fichier doit être au format PDF.',
            'pdf_fiche.max'      => 'Le fichier ne doit pas dépasser '.self::MAX_PDF_SIZE_MB.' Mo.',
        ]);

        $teacher    = Auth::user();
        $activeYear = AcademicYear::where('active', true)->firstOrFail();

        $pivot = ClassTeacherSubject::where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $activeYear->id)
            ->where('teacher_id', $teacher->id)
            ->first();

        if (!$pivot) {
            return back()->with('error', "Accès refusé : vous n'êtes pas autorisé à modifier les notes de cette matière/classe.");
        }

        $students = Student::where('class_id', $classId)
            ->where('academic_year_id', $activeYear->id)
            ->where('is_validated', 1)
            ->orderBy('last_name')->orderBy('first_name')
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', "Aucun élève validé trouvé pour cette classe dans l'année académique active.");
        }

        if (!ClassTeacherSubject::where('class_id', $classId)->where('subject_id', $subjectId)->where('academic_year_id', $activeYear->id)->exists()) {
            return back()->with('error', "Cette matière n'est pas assignée à cette classe pour l'année académique active.");
        }

        $pdfPath    = $request->file('pdf_fiche')->store('uploads/notes_pdf_temp', 'local');
        $pdfAbsPath = Storage::disk('local')->path($pdfPath);
        $parsedRows = [];
        $noteImages = [];

        try {
            // ── Stratégie 1 : PDF texte natif (pdftotext) ────────────────────
            $rawText = shell_exec('pdftotext -layout '.escapeshellarg($pdfAbsPath).' - 2>/dev/null') ?? '';

            if ($this->isUsableText($rawText)) {
                $parsedRows = $this->parsePdfText($rawText, $students);
            }

            // ── Stratégie 2 : PDF scanné (Tesseract OCR via Python) ───────────
            if (empty($parsedRows)) {
                [$parsedRows, $noteImages] = $this->parsePdfScanned($pdfAbsPath, $students);
            }

        } catch (\Throwable $e) {
            Log::error('PdfUploadNotes::upload - '.$e->getMessage());
            Storage::disk('local')->delete($pdfPath);
            return back()->with('error', "Erreur lors de l'analyse du PDF : ".$e->getMessage());
        } finally {
            Storage::disk('local')->delete($pdfPath);
        }

        if (empty($parsedRows)) {
            return back()->with('error',
                "Impossible d'identifier des élèves dans ce PDF. ".
                "Vérifiez que le fichier contient le tableau de notes avec les matricules."
            );
        }

        $existingGrades = Grade::where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->where('trimestre', $trimestre)
            ->where('academic_year_id', $activeYear->id)
            ->get()
            ->groupBy(fn($g) => $g->student_id.'_'.$g->type.'_'.$g->sequence);

        $result = $this->buildVerificationTable(
            $students, $parsedRows, $existingGrades, $classId, $activeYear->id, $noteImages
        );

        return back()->with([
            'upload_result'  => $result,
            'upload_classId' => $classId,
            'upload_subjId'  => $subjectId,
            'upload_trim'    => $trimestre,
        ]);
    }

    // =========================================================================
    //  SAVE
    // =========================================================================
    public function save(Request $request, int $classId, int $subjectId, int $trimestre)
    {
        $teacher    = Auth::user();
        $activeYear = AcademicYear::where('active', true)->firstOrFail();

        $pivot = ClassTeacherSubject::where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $activeYear->id)
            ->where('teacher_id', $teacher->id)
            ->first();

        if (!$pivot) return back()->with('error', 'Accès refusé.');

        $notesJson = $request->input('notes_json', '');
        if (empty($notesJson)) return back()->with('error', 'Aucune donnée à sauvegarder.');

        $notesData = json_decode($notesJson, true);
        if (!is_array($notesData) || empty($notesData)) return back()->with('error', 'Format de données invalide.');

        $allowedIds = Student::where('class_id', $classId)
            ->where('academic_year_id', $activeYear->id)
            ->where('is_validated', 1)
            ->pluck('id')->toArray();

        foreach ($notesData as $item) {
            $sid  = (int)($item['student_id'] ?? 0);
            $type = $item['type'] ?? '';
            $seq  = (int)($item['sequence'] ?? 0);
            $val  = $item['value'] ?? null;

            if (!in_array($sid, $allowedIds, true))
                return back()->with('error', "Élève ID {$sid} non autorisé. Sauvegarde annulée.");
            if (!in_array($type, ['interrogation','devoir'], true))
                return back()->with('error', "Type invalide : {$type}.");
            if ($type==='interrogation' && ($seq<1||$seq>self::INTERRO_COUNT))
                return back()->with('error', "Séquence interrogation invalide : {$seq}.");
            if ($type==='devoir' && ($seq<1||$seq>self::DEVOIR_COUNT))
                return back()->with('error', "Séquence devoir invalide : {$seq}.");
            if ($val!==null && ($val<self::NOTE_MIN||$val>self::NOTE_MAX))
                return back()->with('error', "Note hors plage : {$val}. Autorisée : 0–20.");
        }

        DB::beginTransaction();
        try {
            foreach ($notesData as $item) {
                if (($item['value'] ?? null) === null) continue;
                Grade::updateOrCreate(
                    [
                        'student_id'       => (int)$item['student_id'],
                        'subject_id'       => $subjectId,
                        'class_id'         => $classId,
                        'academic_year_id' => $activeYear->id,
                        'trimestre'        => $trimestre,
                        'type'             => $item['type'],
                        'sequence'         => (int)$item['sequence'],
                    ],
                    ['value'=>(float)$item['value'],'updated_by'=>$teacher->id,'updated_at'=>now()]
                );
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PdfUploadNotes::save - '.$e->getMessage());
            return back()->with('error', 'Erreur lors de la sauvegarde. Veuillez réessayer.');
        }

        try {
            $classe  = Classe::findOrFail($classId);
            $subject = Subject::findOrFail($subjectId);
            $studs   = Student::where('class_id',$classId)->where('academic_year_id',$activeYear->id)
                        ->where('is_validated',1)->orderBy('last_name')->orderBy('first_name')->get();

            $listeEleves = [];
            foreach ($studs as $s) {
                $gs = Grade::where('student_id',$s->id)->where('subject_id',$subjectId)
                      ->where('academic_year_id',$activeYear->id)->where('trimestre',$trimestre)->get();
                $interros=[1=>null,2=>null,3=>null,4=>null,5=>null];
                $devoirs =[1=>null,2=>null];
                foreach ($gs as $g) {
                    if ($g->type==='interrogation'&&isset($interros[$g->sequence])) $interros[$g->sequence]=$g->value;
                    elseif($g->type==='devoir'&&isset($devoirs[$g->sequence]))      $devoirs[$g->sequence]=$g->value;
                }
                $listeEleves[]=['student'=>$s,'interros'=>$interros,'devoirs'=>$devoirs];
            }

            $pdf = Pdf::loadView('censeur.notes.pdf.liste_eleves_pdf', [
                'classe'=>$classe,'subject'=>$subject,'subjectPivot'=>$pivot,
                'trimestre'=>$trimestre,'activeYear'=>$activeYear,
                'listeEleves'=>$listeEleves,
                'dateDownload'=>now()->locale('fr')->isoFormat('D MMMM YYYY'),
            ])->setPaper('a4','landscape');

            $fn = 'Liste_Eleves_'.str_replace([' ','/'],'_',$classe->name).'_'
                .str_replace([' ','/'],'_',$subject->name)."_T{$trimestre}.pdf";

            return $pdf->download($fn);

        } catch (\Throwable $e) {
            Log::error('PdfUploadNotes::save PDF gen - '.$e->getMessage());
            return redirect()->route('teacher.classes.notes.trimestres.subject',[$classId,$subjectId])
                ->with('success','Notes sauvegardées. (La génération du PDF récapitulatif a échoué.)');
        }
    }

    // =========================================================================
    //  PARSING — PDF TEXTE NATIF
    // =========================================================================

    private function isUsableText(string $text): bool
    {
        return strlen(trim($text)) >= 30 && (bool)preg_match('/\b\d{9,15}\b/', $text);
    }

    private function parsePdfText(string $rawText, $students): array
    {
        $rows  = [];
        $index = $this->buildMatriculeIndex($students);

        foreach (explode("\n", $rawText) as $line) {
            $line = trim($line);
            if (!$line) continue;
            if (!preg_match('/\b(\d{9,15})\b/', $line, $m)) continue;

            $mat    = $m[1];
            $after  = substr($line, strpos($line, $mat) + strlen($mat));
            $tokens = preg_split('/\s+|\t/', trim($after), -1, PREG_SPLIT_NO_EMPTY);

            $sexeIdx = null;
            foreach ($tokens as $i => $tok) {
                if (preg_match('/^[MFmf]$/u', trim($tok))) { $sexeIdx = $i; break; }
            }

            $nom=$prenom=$sexe=null; $noteTokens=[];
            if ($sexeIdx !== null) {
                $nom        = $tokens[0] ?? null;
                $prenom     = implode(' ', array_slice($tokens, 1, max(0,$sexeIdx-1))) ?: null;
                $sexe       = strtoupper(trim($tokens[$sexeIdx]));
                $noteTokens = array_slice($tokens, $sexeIdx+1);
            } else {
                foreach ($tokens as $tok) {
                    if ($this->parseNote($tok)!==null || in_array(trim($tok),['—','-','--',''],true))
                        $noteTokens[]=$tok;
                }
            }

            $interros=[1=>null,2=>null,3=>null,4=>null,5=>null];
            $devoirs =[1=>null,2=>null];
            for ($i=0;$i<min(count($noteTokens),7);$i++) {
                $note = $this->parseNote($noteTokens[$i]);
                if ($i<5) $interros[$i+1]=$note;
                else      $devoirs[$i-4] =$note;
            }

            $rows[$mat]=['matricule'=>$mat,'nom'=>$nom,'prenom'=>$prenom,'sexe'=>$sexe,
                         'interros'=>$interros,'devoirs'=>$devoirs,
                         'matched'=>isset($index[$mat]),'student'=>$index[$mat]??null];
        }
        return $rows;
    }

    // =========================================================================
    //  PARSING — PDF SCANNÉ (Tesseract via Python)
    //
    //  ARCHITECTURE :
    //  • pdftoppm convertit la page en PNG haute résolution
    //  • Script Python (Tesseract image_to_data) extrait les tokens avec positions
    //  • Regroupement par bande Y → lignes d'élèves
    //  • Matricules et noms lus sur le texte imprimé (confiance ≥ 96%)
    //  • Détection automatique de la grille (lignes verticales noires)
    //  • Notes : OCR multi-pass + vote majoritaire (confidence ≥ 30%)
    //    → null si trop ambigu (l'enseignant saisit manuellement dans le modal)
    //  • Cellules de notes exportées en base64 pour affichage dans le modal
    // =========================================================================

    private function parsePdfScanned(string $pdfAbsPath, $students): array
    {
        // 1. PDF → PNG
        $tmp    = sys_get_temp_dir().'/notes_ocr_'.uniqid();
        shell_exec(sprintf('pdftoppm -r 300 -png -f 1 -l 1 %s %s 2>/dev/null',
            escapeshellarg($pdfAbsPath), escapeshellarg($tmp)));

        $imgPath = null;
        foreach (['-01.png','-1.png','-001.png'] as $s) {
            if (file_exists($tmp.$s)) { $imgPath=$tmp.$s; break; }
        }
        if (!$imgPath) {
            $files = glob($tmp.'*.png') ?: [];
            $imgPath = $files[0] ?? null;
        }
        if (!$imgPath) throw new \RuntimeException('pdftoppm n\'a pas généré d\'image.');

        try {
            // 2. Détecter la rotation
            $angle = $this->detectBestRotation($imgPath);

            // 3. Script Python principal
            $script     = $this->buildOcrScript($imgPath, $angle, $students);
            $scriptFile = $tmp.'_script.py';
            file_put_contents($scriptFile, $script);
            $jsonOut = shell_exec('python3 '.escapeshellarg($scriptFile).' 2>/dev/null');
            @unlink($scriptFile);

            $data = json_decode($jsonOut ?? '', true);
            if (!is_array($data)) return [[], []];

            // 4. Convertir en format interne
            $index      = $this->buildMatriculeIndex($students);
            $parsedRows = [];
            $noteImages = [];

            foreach ($data['students'] ?? [] as $s) {
                $mat = trim((string)($s['matricule'] ?? ''));
                if (!$mat) continue;

                $interros=[1=>$this->safeNote($s['I1']??null),2=>$this->safeNote($s['I2']??null),
                           3=>$this->safeNote($s['I3']??null),4=>$this->safeNote($s['I4']??null),
                           5=>$this->safeNote($s['I5']??null)];
                $devoirs =[1=>$this->safeNote($s['D1']??null),2=>$this->safeNote($s['D2']??null)];

                $parsedRows[$mat]=[
                    'matricule'=>$mat,'nom'=>$s['nom']??null,'prenom'=>$s['prenom']??null,'sexe'=>$s['sexe']??null,
                    'interros'=>$interros,'devoirs'=>$devoirs,
                    'matched'=>isset($index[$mat]),'student'=>$index[$mat]??null,
                ];

                if (!empty($s['note_images'])) $noteImages[$mat]=$s['note_images'];
            }

            return [$parsedRows, $noteImages];

        } finally {
            foreach (glob($tmp.'*.png') ?: [] as $f) @unlink($f);
        }
    }

    // ─── Détection de la rotation optimale ───────────────────────────────────
    private function detectBestRotation(string $imgPath): int
    {
        $script = tempnam(sys_get_temp_dir(),'ocr_rot').'.py';
        file_put_contents($script, <<<PYTHON
import sys
from PIL import Image
import pytesseract

KEYWORDS = ['CLASSE','ELEVE','MATRICULE','NOM','DEVOIR','TRIMESTRE',
            'COEFFICIENT','LISTE','ALPHABETIQUE','PHYSIQUE','CHIMIE',
            'TECHNOLOGIE','EFFECTIF','PRENOMS','SEXE']

img = Image.open(r"{$imgPath}")
best_angle, best_score = 0, 0
for angle in [0, 90, 180, 270]:
    r = img.rotate(angle, expand=True) if angle else img
    w, h = r.size
    sample = r.crop((w//4, h//4, 3*w//4, 3*h//4)).resize((500,350))
    text = pytesseract.image_to_string(sample, lang='fra+eng', config='--psm 6').upper()
    score = sum(1 for k in KEYWORDS if k in text)
    if score > best_score:
        best_score = score
        best_angle = angle
print(best_angle)
PYTHON);
        $out = shell_exec('python3 '.escapeshellarg($script).' 2>/dev/null');
        @unlink($script);
        $angle = (int)trim($out ?? '0');
        return in_array($angle,[0,90,180,270]) ? $angle : 0;
    }

    // ─── Script Python OCR principal ─────────────────────────────────────────
    private function buildOcrScript(string $imgPath, int $angle, $students): string
    {
        $knownMats = json_encode(
            $students->map(fn($s)=>(string)($s->num_educ??''))->filter()->values()->toArray()
        );

        // Colonnes de notes par défaut (fallback si grille non détectée)
        // Valeurs calibrées sur fiche A4 paysage à 300 DPI, image 3000px de large
        $fallbackJson = json_encode([
            'I1'=>[1622,1787],'I2'=>[1787,1953],'I3'=>[1953,2121],
            'I4'=>[2121,2290],'I5'=>[2290,2459],'D1'=>[2459,2629],'D2'=>[2629,2802],
        ]);

        return <<<PYTHON
#!/usr/bin/env python3
import json, re, base64, sys
from PIL import Image, ImageEnhance, ImageOps
import pytesseract
import numpy as np
from collections import defaultdict, Counter
from io import BytesIO

KNOWN_MATS   = {$knownMats}
FALLBACK_COLS= {$fallbackJson}
Y_TOL        = 25   # tolérance regroupement vertical (px)
CELL_INSET   = 6    # marge à l'intérieur des traits du tableau
NOTE_MIN, NOTE_MAX = 0, 20

# ── Chargement + rotation ────────────────────────────────────────────────────
img = Image.open(r"{$imgPath}")
if {$angle}:
    img = img.rotate({$angle}, expand=True)
W, H = img.size

# ── OCR full-image (texte imprimé) ───────────────────────────────────────────
data = pytesseract.image_to_data(
    img, lang='fra+eng', config='--psm 6 --oem 3',
    output_type=pytesseract.Output.DICT
)

# ── Regroupement des tokens par bande Y ──────────────────────────────────────
groups = defaultdict(list)
for i, text in enumerate(data['text']):
    if not text.strip(): continue
    if int(data['conf'][i]) < 40: continue
    cy = data['top'][i] + data['height'][i] // 2
    placed = False
    for gy in list(groups.keys()):
        if abs(cy - gy) <= Y_TOL:
            groups[gy].append({'t': text, 'x': data['left'][i],
                               'y': data['top'][i], 'h': data['height'][i]})
            placed = True
            break
    if not placed:
        groups[cy].append({'t': text, 'x': data['left'][i],
                           'y': data['top'][i], 'h': data['height'][i]})

# ── Extraction des lignes d'élèves ───────────────────────────────────────────
MAT_RE = re.compile(r'^\d{9,15}$')

student_rows = []
for gy in sorted(groups.keys()):
    toks = sorted(groups[gy], key=lambda t: t['x'])
    
    # Chercher un matricule
    mat = None
    mat_x = 0
    for tk in toks:
        clean = re.sub(r'[^\d]', '', tk['t'])
        if MAT_RE.match(clean):
            mat = clean; mat_x = tk['x']; break
    if not mat:
        continue

    # Nom / prénom / sexe
    nom = None; prenom_parts = []; sexe = None
    for tk in toks:
        if tk['x'] <= mat_x: continue
        txt = re.sub(r'^[|\s;:,.]+|[|\s;:,.]+$', '', tk['t'])
        if not txt: continue
        if nom is None and re.match(r'^[A-ZÉÈÀÂÊÎÔÙÛÇ]{2,}', txt):
            nom = txt; continue
        if re.match(r'^[MFmf]$', txt) and sexe is None:
            sexe = txt.upper(); break
        elif nom and sexe is None and re.match(r'[A-ZÉÈÀa-zéèàâêîôùûç]', txt):
            prenom_parts.append(txt)

    # Bornes Y de cette ligne
    ys  = [tk['y'] for tk in toks]
    hhs = [tk['h'] for tk in toks]
    y_top = max(0,    min(ys) - 4)
    y_bot = min(H, max(y+h for y,h in zip(ys,hhs)) + 4)

    student_rows.append({
        'matricule': mat, 'nom': nom,
        'prenom': ' '.join(prenom_parts), 'sexe': sexe,
        'y_top': y_top, 'y_bot': y_bot,
    })

# ── Détection automatique des colonnes de notes ──────────────────────────────
def detect_note_cols(img_arr, y_rows):
    """Détecte les colonnes de notes via lignes verticales noires du tableau."""
    if not y_rows:
        return FALLBACK_COLS
    y1 = max(0,   min(r['y_top'] for r in y_rows) - 30)
    y2 = min(img_arr.shape[0], max(r['y_bot'] for r in y_rows) + 20)
    
    region = img_arr[y1:y2, :] if img_arr.ndim == 2 else \
             np.mean(img_arr[y1:y2, :, :3], axis=2)
    
    dark_col = np.mean(region < 80, axis=0)
    
    lines = []
    in_l = False
    for x, v in enumerate(dark_col):
        if v > 0.18 and not in_l:  in_l = True;  start = x
        elif v <= 0.18 and in_l:   in_l = False; lines.append((start+x)//2)
    
    # Fusionner les traits doubles (épaisseur ≤ 20px)
    merged = []
    for x in lines:
        if merged and x - merged[-1] < 20:
            merged[-1] = (merged[-1] + x) // 2
        else:
            merged.append(x)
    
    # Il faut au moins 8 lignes verticales pour 7 colonnes de notes
    if len(merged) >= 8:
        # Les 7 derniers intervalles = I1..I5, D1, D2
        cols_x = list(zip(merged[-8:-1], merged[-7:]))
        if len(cols_x) == 7:
            names = ['I1','I2','I3','I4','I5','D1','D2']
            return {n: [int(x1), int(x2)] for n, (x1,x2) in zip(names, cols_x)}
    
    return FALLBACK_COLS

img_arr   = np.array(img.convert('L'))
note_cols = detect_note_cols(img_arr, student_rows)

# ── OCR cellule de note (chiffre manuscrit 0-20) ─────────────────────────────
def ocr_note_cell(img, x1, y1, x2, y2):
    """
    Tente de lire un chiffre manuscrit dans une cellule de tableau.
    Retourne float (0-20) ou None si la lecture est trop incertaine.
    Stratégie : multi-scale, multi-threshold, vote majoritaire.
    Seuil de confiance : ≥ 3 votes concordants représentant ≥ 30% des lectures.
    """
    ci = CELL_INSET
    bx1, by1 = max(0,x1+ci),        max(0,y1+ci)
    bx2, by2 = min(img.width,x2-ci),min(img.height,y2-ci)
    if bx2 <= bx1 or by2 <= by1:
        return None

    cell = img.crop((bx1, by1, bx2, by2))
    cw, ch = cell.size
    reads  = []

    for scale in [6, 9, 12]:
        big  = cell.resize((cw*scale, ch*scale), Image.LANCZOS)
        gray = np.array(big.convert('L'))
        mean = gray.mean()

        for factor in [0.78, 0.92, 1.0, 1.10, 1.22]:
            thr = int(np.clip(mean * factor, 30, 220))
            bi  = Image.fromarray(np.where(gray < thr, 0, 255).astype(np.uint8))
            brd = ImageOps.expand(bi, border=12, fill=255)

            for psm in [7, 8]:
                for oem in [0, 1]:
                    cfg = f'--psm {psm} --oem {oem} -c tessedit_char_whitelist=0123456789'
                    try:
                        t = re.sub(r'[^\d]', '',
                                   pytesseract.image_to_string(brd, config=cfg).strip())
                        if 1 <= len(t) <= 2:
                            v = int(t)
                            if NOTE_MIN <= v <= NOTE_MAX:
                                reads.append(v)
                    except Exception:
                        pass

    if not reads:
        return None

    c = Counter(reads)
    best, votes = c.most_common(1)[0]
    total = sum(c.values())
    if votes >= 3 and (votes / total) >= 0.30:
        return float(best)
    return None

# ── Export cellule en base64 (pour affichage dans le modal) ──────────────────
def cell_to_b64(img, x1, y1, x2, y2, zoom=3):
    ci = CELL_INSET
    bx1, by1 = max(0,x1+ci),         max(0,y1+ci)
    bx2, by2 = min(img.width,x2-ci), min(img.height,y2-ci)
    if bx2 <= bx1 or by2 <= by1:
        return ''
    cell = img.crop((bx1, by1, bx2, by2))
    cw, ch = cell.size
    if cw < 2 or ch < 2:
        return ''
    zoomed = cell.resize((cw*zoom, ch*zoom), Image.LANCZOS)
    buf = BytesIO()
    zoomed.save(buf, format='PNG', optimize=True)
    return base64.b64encode(buf.getvalue()).decode()

# ── Assemblage du résultat ────────────────────────────────────────────────────
result_students = []

for row in student_rows:
    mat   = row['matricule']
    y_top = row['y_top']
    y_bot = row['y_bot']
    notes = {}
    imgs  = {}

    for col_name, (x1, x2) in note_cols.items():
        notes[col_name] = ocr_note_cell(img, x1, y_top, x2, y_bot)
        imgs[col_name]  = cell_to_b64(img, x1, y_top, x2, y_bot)

    result_students.append({
        'matricule': mat,
        'nom':       row['nom'],
        'prenom':    row['prenom'],
        'sexe':      row['sexe'],
        **{k: notes.get(k) for k in ['I1','I2','I3','I4','I5','D1','D2']},
        'note_images': imgs,
    })

print(json.dumps({'students': result_students, 'note_cols': note_cols}))
PYTHON;
    }

    // =========================================================================
    //  TABLEAU DE VÉRIFICATION
    // =========================================================================

    private function buildVerificationTable(
        $students, array $parsedRows, $existingGrades,
        int $classId, int $academicYearId, array $noteImages = []
    ): array {
        $globalOk = true;

        $dbMats   = $students->map(fn($s)=>(string)($s->num_educ??''))->filter()->values()->toArray();
        $pdfMats  = array_keys($parsedRows);

        $unknownInDb  = array_diff($pdfMats, $dbMats);
        $missingInPdf = array_diff($dbMats, $pdfMats);

        $conformite = [
            'total_bdd'      => count($dbMats),
            'total_pdf'      => count($pdfMats),
            'missing_in_pdf' => array_values($missingInPdf),
            'unknown_in_db'  => array_values($unknownInDb),
            'is_conforming'  => empty($missingInPdf) && empty($unknownInDb),
        ];
        if (!$conformite['is_conforming']) $globalOk = false;

        $rows = [];
        foreach ($students as $student) {
            $mat    = (string)($student->num_educ ?? '');
            $pdfRow = $parsedRows[$mat] ?? null;

            $interros=[]; $devoirs=[];

            for ($i=1;$i<=self::INTERRO_COUNT;$i++) {
                $pdfVal   = $pdfRow['interros'][$i] ?? null;
                $key      = $student->id.'_interrogation_'.$i;
                $hasExist = $existingGrades->has($key);
                $existing = $hasExist ? $existingGrades[$key]->first() : null;
                $status   = $this->cellStatus($pdfVal, $hasExist, $pdfRow!==null);
                if (!in_array($status,['ok','empty'],true)) $globalOk=false;
                $interros[$i]=[
                    'value'=>$pdfVal,'status'=>$status,'existing'=>$existing?->value,
                    'img'=>$noteImages[$mat]['I'.$i]??null,
                ];
            }

            for ($i=1;$i<=self::DEVOIR_COUNT;$i++) {
                $pdfVal   = $pdfRow['devoirs'][$i] ?? null;
                $key      = $student->id.'_devoir_'.$i;
                $hasExist = $existingGrades->has($key);
                $existing = $hasExist ? $existingGrades[$key]->first() : null;
                $status   = $this->cellStatus($pdfVal, $hasExist, $pdfRow!==null);
                if (!in_array($status,['ok','empty'],true)) $globalOk=false;
                $devoirs[$i]=[
                    'value'=>$pdfVal,'status'=>$status,'existing'=>$existing?->value,
                    'img'=>$noteImages[$mat]['D'.$i]??null,
                ];
            }

            $nameMatch = true;
            if ($pdfRow && !empty($pdfRow['nom'])) {
                $pdfNom = strtoupper(trim($pdfRow['nom']));
                $dbNom  = strtoupper(trim($student->last_name));
                if ($pdfNom!=='' && $dbNom!=='' && $pdfNom!==$dbNom) {
                    $nameMatch=false; $globalOk=false;
                }
            }

            $rows[]=[
                'student'=>$student,'found_pdf'=>$pdfRow!==null,
                'name_match'=>$nameMatch,'pdf_nom'=>$pdfRow['nom']??null,
                'pdf_prenom'=>$pdfRow['prenom']??null,
                'interros'=>$interros,'devoirs'=>$devoirs,
            ];
        }

        return [
            'rows'       => $rows,
            'conformite' => $conformite,
            'global_ok'  => $globalOk,
            'is_scanned' => !empty($noteImages),
        ];
    }

    private function cellStatus(?float $pdfVal, bool $hasExisting, bool $foundInPdf): string
    {
        if (!$foundInPdf) return 'no_pdf_data';
        if ($hasExisting) return 'conflict';
        if ($pdfVal===null) return 'empty';
        return 'ok';
    }

    // =========================================================================
    //  HELPERS
    // =========================================================================

    private function buildMatriculeIndex($students): array
    {
        $idx=[];
        foreach ($students as $s) {
            $n=trim((string)($s->num_educ??''));
            if ($n!=='') $idx[$n]=$s;
        }
        return $idx;
    }

    private function parseNote(string $token): ?float
    {
        $t=trim($token);
        if (in_array($t,['—','-','--','','null','N/A','n/a','*'],true)) return null;
        $t=str_replace(',','.',$t);
        if (!is_numeric($t)) return null;
        $v=(float)$t;
        return ($v>=self::NOTE_MIN && $v<=self::NOTE_MAX) ? $v : null;
    }

    private function safeNote($val): ?float
    {
        if ($val===null || $val==='') return null;
        $f=(float)$val;
        return ($f>=self::NOTE_MIN && $f<=self::NOTE_MAX) ? $f : null;
    }
}