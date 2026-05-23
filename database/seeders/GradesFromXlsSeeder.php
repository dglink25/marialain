<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ╔══════════════════════════════════════════════════════════════════════════╗
 * ║                      GradesFromXlsSeeder v2                            ║
 * ╠══════════════════════════════════════════════════════════════════════════╣
 * ║  Importe les notes depuis des fichiers XLS vers les tables `grades`    ║
 * ║  et `conducts`. Scan récursif du dossier database/seeders/data/.        ║
 * ╠══════════════════════════════════════════════════════════════════════════╣
 * ║  STRUCTURE DU DOSSIER ATTENDUE                                          ║
 * ║  database/seeders/data/                                                  ║
 * ║    notes_2024-2025_5ème/                                                 ║
 * ║      Anglais_T1.XLS                                                      ║
 * ║      Conduite_T2.XLS   ...                                               ║
 * ║    notes_2024-2025_6ème/ ...                                             ║
 * ╠══════════════════════════════════════════════════════════════════════════╣
 * ║  STRUCTURE DU FICHIER XLS (colonnes 0-based)                            ║
 * ║  Métadonnées :                                                           ║
 * ║    L3  col21 → Année scolaire  ex: "2024-2025"                          ║
 * ║    L5  col21 → Classe          ex: "5ème"                               ║
 * ║    L7  col21 → Trimestre       ex: 1                                    ║
 * ║    L9  col6  → Matière         ex: "ANGLAIS"                            ║
 * ║    L11 col6  → Coefficient     ex: 1                                    ║
 * ║  Notes (à partir de L12) :                                              ║
 * ║    col0  → N° (vide = ligne continuation du nom précédent)             ║
 * ║    col2  → Nom et Prénoms                                               ║
 * ║    col7  → Interro 1   col8  → Interro 2   col10 → Interro 3          ║
 * ║    col11 → Interro 4   col12 → Interro 5                               ║
 * ║    col18 → Devoir 1                                                      ║
 * ║    col21 → Devoir 2 (réel si moy_recalc_sans_D2 ≠ col27)              ║
 * ║    col27 → Moyenne calculée (IGNORÉE)                                   ║
 * ╠══════════════════════════════════════════════════════════════════════════╣
 * ║  CAS SPÉCIAUX GÉRÉS                                                     ║
 * ║  • Noms sur 2 lignes : col0 vide + col2 non vide → continuation         ║
 * ║  • CONDUITE → table `conducts`, pas `grades`                            ║
 * ║  • D2 ambigu : recalcul sans D2 pour valider si note réelle             ║
 * ║  • Matching élève 4 passes (exact → last+fn1 → last → partiel)         ║
 * ║  • Idempotent : INSERT / UPDATE (loggé) / SKIP                          ║
 * ║  • Rapport CSV dans storage/logs/grades_import_YYYY-MM-DD_HHmmss.csv   ║
 * ╠══════════════════════════════════════════════════════════════════════════╣
 * ║  DÉPENDANCE                                                              ║
 * ║    composer require phpoffice/phpspreadsheet                             ║
 * ║  USAGE                                                                   ║
 * ║    php artisan db:seed --class=GradesFromXlsSeeder                      ║
 * ╚══════════════════════════════════════════════════════════════════════════╝
 */
class GradesFromXlsSeeder extends Seeder
{
    // ── Configuration ────────────────────────────────────────────────────────
    private const DATA_DIR       = 'database/seeders/data';
    private const FIRST_DATA_ROW = 12;   // 0-based

    // Colonnes métadonnées [row, col] (0-based)
    private const M_YEAR    = [3,  21];
    private const M_CLASS   = [5,  21];
    private const M_TRI     = [7,  21];
    private const M_SUBJECT = [9,  6];
    private const M_COEF    = [11, 6];

    // Colonnes données (0-based)
    private const C_NUM = 0;
    private const C_NAME = 2;
    private const C_I1  = 7;
    private const C_I2  = 8;
    private const C_I3  = 10;
    private const C_I4  = 11;
    private const C_I5  = 12;
    private const C_D1  = 18;
    private const C_D2  = 21;
    private const C_MOY = 27;

    private const INTERRO_COLS = [
        1 => self::C_I1,
        2 => self::C_I2,
        3 => self::C_I3,
        4 => self::C_I4,
        5 => self::C_I5,
    ];

    // Tolérance pour la détection du D2 ambigu
    private const D2_TOLERANCE = 0.01;

    // ── État global ───────────────────────────────────────────────────────────
    private int   $gInserted   = 0;
    private int   $gUpdated    = 0;
    private int   $gSkipped    = 0;
    private int   $gErrors     = 0;
    private array $gUnmatched  = [];
    private array $gDone       = [];
    private array $gFailed     = [];

    /** Lignes pour le rapport CSV */
    private array $csvRows = [];

    /** Heure de début (pour le nom du fichier CSV) */
    private string $startedAt;

    // ─────────────────────────────────────────────────────────────────────────
    //  POINT D'ENTRÉE
    // ─────────────────────────────────────────────────────────────────────────
    public function run(): void
    {
        $this->startedAt = now()->format('Y-m-d_His');

        $this->banner('GradesFromXlsSeeder v2 – Import des notes');

        $dataDir = base_path(self::DATA_DIR);

        if (!is_dir($dataDir)) {
            $this->command->error("Dossier introuvable : {$dataDir}");
            $this->command->line("  → Créez-le puis déposez vos sous-dossiers de fichiers XLS.");
            return;
        }

        // Scan récursif
        $files = $this->scanFiles($dataDir);

        if (empty($files)) {
            $this->command->warn("Aucun fichier XLS/xlsx trouvé dans : {$dataDir}");
            return;
        }

        $this->command->line("  📁 <comment>{$dataDir}</comment>");
        $this->command->line('  📄 <info>' . count($files) . ' fichier(s) trouvé(s)</info>');
        $this->command->line('');

        // Charger référentiels une seule fois
        $allYears    = DB::table('academic_years')->get()->keyBy('name');
        $allSubjects = DB::table('subjects')->get();

        $this->command->line(
            '  Référentiels BD : <info>'
            . $allYears->count() . ' années</info>, '
            . '<info>' . $allSubjects->count() . ' matières</info>'
        );
        $this->command->line('');

        // Traiter chaque fichier
        foreach ($files as $file) {
            $this->processFile($file, $allYears, $allSubjects);
        }

        // Écrire le rapport CSV
        $this->writeCsvReport();

        // Afficher le résumé
        $this->printSummary();
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  TRAITEMENT D'UN FICHIER
    // ─────────────────────────────────────────────────────────────────────────
    private function processFile(
        string $path,
        \Illuminate\Support\Collection $allYears,
        \Illuminate\Support\Collection $allSubjects
    ): void {
        $relPath = $this->relPath($path);
        $fname   = basename($path);

        $this->command->line("  ┌─ 📄 <comment>{$relPath}</comment>");

        try {
            // 1. Lecture du fichier
            $rows = $this->readXls($path);

            if (count($rows) < self::FIRST_DATA_ROW + 1) {
                $this->fail($fname, $relPath, 'Fichier vide ou trop court');
                return;
            }

            // 2. Extraction des métadonnées
            $meta = $this->extractMeta($rows, $fname);
            if ($meta === null) {
                $this->failed[] = ['file' => $relPath, 'reason' => 'Métadonnées invalides'];
                return;
            }

            $this->command->line("  │  <fg=cyan>Année</>     : {$meta['year_name']}");
            $this->command->line("  │  <fg=cyan>Classe</>    : {$meta['class_name']}");
            $this->command->line("  │  <fg=cyan>Trimestre</> : {$meta['trimestre']}");
            $this->command->line("  │  <fg=cyan>Matière</>   : {$meta['subject_name']} (coef {$meta['coef']})");

            // 3. Résolution des entités BD
            $year = $allYears->get($meta['year_name']);
            if (!$year) {
                $this->fail($fname, $relPath, "Année « {$meta['year_name']} » introuvable en BD");
                return;
            }

            $class = DB::table('classes')
                ->where('academic_year_id', $year->id)
                ->where('name', $meta['class_name'])
                ->first();

            if (!$class) {
                $this->fail(
                    $fname, $relPath,
                    "Classe « {$meta['class_name']} » introuvable pour {$meta['year_name']}"
                );
                return;
            }

            $subject = $this->resolveSubject($allSubjects, $meta['subject_name']);
            if (!$subject) {
                $this->fail(
                    $fname, $relPath,
                    "Matière « {$meta['subject_name']} » introuvable en BD"
                );
                return;
            }

            $isConduite = (strtoupper(trim($meta['subject_name'])) === 'CONDUITE');

            // 4. Synchroniser le coefficient (seulement pour les vraies matières)
            if (!$isConduite) {
                $this->syncCoefficient($class->id, $subject->id, $year->id, $meta['coef']);
            }

            // 5. Construire l'index des élèves de la classe
            $index = $this->buildStudentIndex($class->id, $year->id);

            if (empty($index)) {
                $this->fail(
                    $fname, $relPath,
                    "Aucun élève dans student_academic_records pour cette classe/année"
                );
                return;
            }

            $uniqueStudents = count(array_unique(array_map(fn($s) => $s->id, $index)));
            $this->command->line("  │  Élèves BD   : <info>{$uniqueStudents}</info>");

            // 6. Fusionner les noms sur plusieurs lignes
            $mergedRows = $this->mergeMultilineNames($rows);

            // 7. Import des notes ou conduites
            if ($isConduite) {
                $stats = $this->importConduites(
                    $mergedRows, $index,
                    $class->id, $year->id, $meta['trimestre'],
                    $relPath
                );
            } else {
                $stats = $this->importGrades(
                    $mergedRows, $index,
                    $class->id, $subject->id, $year->id, $meta['trimestre'],
                    $relPath
                );
            }

            $this->command->line(sprintf(
                '  └─ <info>✓</info> Inséré: <info>%d</info> | Mis à jour: <comment>%d</comment>'
                . ' | Ignoré: %d | Non trouvé: <fg=red>%d</>',
                $stats['inserted'], $stats['updated'], $stats['skipped'], $stats['unmatched']
            ));
            $this->command->line('');

            $this->gInserted += $stats['inserted'];
            $this->gUpdated  += $stats['updated'];
            $this->gSkipped  += $stats['skipped'];
            $this->gDone[]    = $relPath;

        } catch (\Throwable $e) {
            $this->command->line("  └─ <fg=red>✗ ERREUR</> : " . $e->getMessage());
            $this->command->line('');
            Log::error("GradesFromXlsSeeder [{$fname}]: " . $e->getMessage(), [
                'file'  => $path,
                'trace' => $e->getTraceAsString(),
            ]);
            $this->failed[]  = ['file' => $relPath, 'reason' => $e->getMessage()];
            $this->gErrors++;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  LECTURE XLS
    // ─────────────────────────────────────────────────────────────────────────
    private function readXls(string $path): array
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(true);

        $sheet = $reader->load($path)->getActiveSheet();
        $rows  = [];

        foreach ($sheet->getRowIterator() as $rowObj) {
            $it = $rowObj->getCellIterator();
            $it->setIterateOnlyExistingCells(false);

            $row = [];
            foreach ($it as $cell) {
                $row[] = $cell->getValue();
            }

            // Garantir 28 colonnes minimum (index 0..27)
            while (count($row) < 28) {
                $row[] = null;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  EXTRACTION DES MÉTADONNÉES
    // ─────────────────────────────────────────────────────────────────────────
    private function extractMeta(array $rows, string $fname): ?array
    {
        $get = fn(int $r, int $c): ?string =>
            (isset($rows[$r][$c]) && trim((string)$rows[$r][$c]) !== '')
            ? trim((string)$rows[$r][$c])
            : null;

        $yearName    = $get(self::M_YEAR[0],    self::M_YEAR[1]);
        $className   = $get(self::M_CLASS[0],   self::M_CLASS[1]);
        $triRaw      = $get(self::M_TRI[0],     self::M_TRI[1]);
        $subjectName = $get(self::M_SUBJECT[0], self::M_SUBJECT[1]);
        $coefRaw     = $get(self::M_COEF[0],    self::M_COEF[1]);

        $missing = array_filter([
            !$yearName    ? 'Année (L4 col21)'    : null,
            !$className   ? 'Classe (L6 col21)'   : null,
            !$triRaw      ? 'Trimestre (L8 col21)': null,
            !$subjectName ? 'Matière (L10 col6)'  : null,
        ]);

        if (!empty($missing)) {
            foreach ($missing as $m) {
                $this->command->line("  │  <fg=red>✗ Métadonnée manquante : {$m}</>");
            }
            return null;
        }

        $tri = (int) $triRaw;
        if (!in_array($tri, [1, 2, 3], true)) {
            $this->command->line("  │  <fg=red>✗ Trimestre invalide : « {$triRaw} »</>");
            return null;
        }

        return [
            'year_name'    => $yearName,
            'class_name'   => $className,
            'trimestre'    => $tri,
            'subject_name' => strtoupper(trim($subjectName)),
            'coef'         => max(1, (int) ($coefRaw ?? 1)),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  FUSION DES NOMS SUR PLUSIEURS LIGNES
    //  Règle : col0 vide ET col2 non vide → suite du prénom de la ligne précédente
    // ─────────────────────────────────────────────────────────────────────────
    private function mergeMultilineNames(array $rows): array
    {
        $merged = [];
        $lastDataIdx = null;  // index dans $merged de la dernière ligne de données

        foreach ($rows as $ri => $row) {
            if ($ri < self::FIRST_DATA_ROW) {
                $merged[] = $row;
                continue;
            }

            $numVal  = $row[self::C_NUM] ?? null;
            $nameVal = $row[self::C_NAME] ?? null;

            $numStr  = trim((string)$numVal);
            $nameStr = trim((string)$nameVal);

            // Ligne vide → ignorer
            if ($numStr === '' && $nameStr === '') {
                continue;
            }

            // Ligne de continuation : col0 vide, col2 non vide, pas de notes
            $hasAnyNote = false;
            foreach ([self::C_I1, self::C_I2, self::C_I3, self::C_I4, self::C_I5,
                      self::C_D1, self::C_D2, self::C_MOY] as $c) {
                if ($this->toFloat($row[$c] ?? null) !== null) {
                    $hasAnyNote = true;
                    break;
                }
            }

            if ($numStr === '' && $nameStr !== '' && !$hasAnyNote && $lastDataIdx !== null) {
                // Concaténer au nom de la ligne précédente
                $prev = &$merged[$lastDataIdx];
                $prev[self::C_NAME] = trim((string)$prev[self::C_NAME]) . ' ' . $nameStr;
                unset($prev);
                continue;
            }

            // Ligne normale de données
            if ($numStr !== '') {
                $merged[]     = $row;
                $lastDataIdx  = array_key_last($merged);
                continue;
            }

            // Ligne pied de page ou autre → ignorer
        }

        return $merged;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  INDEX DES ÉLÈVES DE LA CLASSE
    // ─────────────────────────────────────────────────────────────────────────
    private function buildStudentIndex(int $classId, int $yearId): array
    {
        $ids = DB::table('student_academic_records')
            ->where('class_id',         $classId)
            ->where('academic_year_id', $yearId)
            ->pluck('student_id')
            ->toArray();

        if (empty($ids)) {
            return [];
        }

        $students = DB::table('students')->whereIn('id', $ids)->get();

        $index = [];

        foreach ($students as $s) {
            $ln  = $this->normalize($s->last_name);
            $fn  = $this->normalize($s->first_name);
            $fn1 = explode(' ', $fn)[0] ?? '';

            // 3 clés par priorité décroissante de précision
            foreach ([
                0 => "{$ln} {$fn}",      // Nom + prénom complet
                1 => "{$ln} {$fn1}",     // Nom + 1er mot prénom
                2 => $ln,                 // Nom seul
            ] as $prio => $key) {
                if (!isset($index[$key]) || $prio < $index[$key]['prio']) {
                    $index[$key] = ['s' => $s, 'prio' => $prio];
                }
            }
        }

        return array_map(fn($v) => $v['s'], $index);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  IMPORT GRADES (table `grades`)
    // ─────────────────────────────────────────────────────────────────────────
    private function importGrades(
        array $rows,
        array $index,
        int   $classId,
        int   $subjectId,
        int   $yearId,
        int   $trimestre,
        string $relPath
    ): array {
        $inserted = $updated = $skipped = $unmatched = 0;
        $now      = now();

        foreach ($rows as $ri => $row) {
            if ($ri < self::FIRST_DATA_ROW) {
                continue;
            }

            // Filtrer les lignes sans numéro valide
            $num = trim((string)($row[self::C_NUM] ?? ''));
            if ($num === '' || !is_numeric($num)) {
                continue;
            }

            $xlsName = trim((string)($row[self::C_NAME] ?? ''));
            if ($xlsName === '') {
                continue;
            }

            // Stopper sur le pied de page "Imprimé le"
            if (stripos($xlsName, 'Imprimé') !== false) {
                break;
            }

            $student = $this->matchStudent($xlsName, $index);

            if (!$student) {
                $this->command->line(
                    "  │  <fg=yellow>⚠ Non trouvé</> L" . ($ri + 1) . " : « {$xlsName} »"
                );
                $this->gUnmatched[] = [
                    'file' => $relPath, 'row' => $ri + 1, 'name' => $xlsName,
                ];
                $unmatched++;

                $this->addCsvRow(
                    $relPath, $ri + 1, $xlsName, '—', '—', '—', '—', 'NON_TROUVÉ'
                );
                continue;
            }

            // Extraire toutes les notes de la ligne
            $notes = $this->extractGradeNotes($row);

            foreach ($notes as $note) {
                $action = $this->upsertGrade(
                    $student->id, $classId, $subjectId, $yearId,
                    $trimestre, $note['type'], $note['seq'], $note['val'], $now
                );

                match ($action) {
                    'inserted' => $inserted++,
                    'updated'  => $updated++,
                    'skipped'  => $skipped++,
                };

                $this->addCsvRow(
                    $relPath, $ri + 1, $xlsName,
                    $student->last_name . ' ' . $student->first_name,
                    $note['type'], (string)$note['seq'], (string)$note['val'],
                    strtoupper($action)
                );
            }
        }

        return compact('inserted', 'updated', 'skipped', 'unmatched');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  IMPORT CONDUITES (table `conducts`)
    // ─────────────────────────────────────────────────────────────────────────
    private function importConduites(
        array $rows,
        array $index,
        int   $classId,
        int   $yearId,
        int   $trimestre,
        string $relPath
    ): array {
        $inserted = $updated = $skipped = $unmatched = 0;
        $now      = now();

        foreach ($rows as $ri => $row) {
            if ($ri < self::FIRST_DATA_ROW) {
                continue;
            }

            $num = trim((string)($row[self::C_NUM] ?? ''));
            if ($num === '' || !is_numeric($num)) {
                continue;
            }

            $xlsName = trim((string)($row[self::C_NAME] ?? ''));
            if ($xlsName === '') {
                continue;
            }

            if (stripos($xlsName, 'Imprimé') !== false) {
                break;
            }

            $student = $this->matchStudent($xlsName, $index);

            if (!$student) {
                $this->command->line(
                    "  │  <fg=yellow>⚠ Non trouvé</> L" . ($ri + 1) . " : « {$xlsName} »"
                );
                $this->gUnmatched[] = [
                    'file' => $relPath, 'row' => $ri + 1, 'name' => $xlsName,
                ];
                $unmatched++;
                $this->addCsvRow($relPath, $ri + 1, $xlsName, '—', 'conduite', '—', '—', 'NON_TROUVÉ');
                continue;
            }

            // La note de conduite est dans col21 (ou col27 en fallback)
            $grade = $this->toFloat($row[self::C_D2] ?? null)
                  ?? $this->toFloat($row[self::C_MOY] ?? null);

            if ($grade === null) {
                // Aucune note → skip silencieux
                $skipped++;
                continue;
            }

            $action = $this->upsertConduite(
                $student->id, $classId, $yearId, $trimestre, $grade, $now
            );

            match ($action) {
                'inserted' => $inserted++,
                'updated'  => $updated++,
                'skipped'  => $skipped++,
            };

            $this->addCsvRow(
                $relPath, $ri + 1, $xlsName,
                $student->last_name . ' ' . $student->first_name,
                'conduite', '—', (string)$grade,
                strtoupper($action)
            );
        }

        return compact('inserted', 'updated', 'skipped', 'unmatched');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  EXTRACTION DES NOTES D'UNE LIGNE (matières normales)
    // ─────────────────────────────────────────────────────────────────────────
    private function extractGradeNotes(array $row): array
    {
        $notes = [];

        // ── Interrogations ───────────────────────────────────────────────────
        foreach (self::INTERRO_COLS as $seq => $col) {
            $v = $this->toFloat($row[$col] ?? null);
            if ($v !== null) {
                $notes[] = ['type' => 'interrogation', 'seq' => $seq, 'val' => $v];
            }
        }

        // ── Devoir 1 ─────────────────────────────────────────────────────────
        $d1 = $this->toFloat($row[self::C_D1] ?? null);
        if ($d1 !== null) {
            $notes[] = ['type' => 'devoir', 'seq' => 1, 'val' => $d1];
        }

        // ── Devoir 2 : règle robuste par recalcul ────────────────────────────
        // col21 est une vraie note D2 si la moyenne recalculée SANS D2
        // (= moyenne des interros + D1 seulement) diffère de col27.
        // Si elles coïncident, col21 est un artefact de formule XLS.
        $d2raw    = $this->toFloat($row[self::C_D2] ?? null);
        $moyXls   = $this->toFloat($row[self::C_MOY] ?? null);

        if ($d2raw !== null) {
            // Calculer la moyenne attendue SANS D2
            $interroVals = array_filter(
                array_map(fn($c) => $this->toFloat($row[$c] ?? null), self::INTERRO_COLS),
                fn($v) => $v !== null
            );
            $moyI = !empty($interroVals)
                ? array_sum($interroVals) / count($interroVals)
                : null;

            $notesSansD2 = array_filter([$moyI, $d1], fn($v) => $v !== null);
            $moyRecalc   = !empty($notesSansD2)
                ? array_sum($notesSansD2) / count($notesSansD2)
                : null;

            // D2 est réel si moy recalculée sans D2 ≠ moy XLS
            $d2Reel = $moyXls === null
                || $moyRecalc === null
                || abs($moyRecalc - $moyXls) > self::D2_TOLERANCE;

            if ($d2Reel) {
                $notes[] = ['type' => 'devoir', 'seq' => 2, 'val' => $d2raw];
            }
        }

        return $notes;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  UPSERT GRADE
    // ─────────────────────────────────────────────────────────────────────────
    private function upsertGrade(
        int    $studentId,
        int    $classId,
        int    $subjectId,
        int    $yearId,
        int    $trimestre,
        string $type,
        int    $seq,
        float  $val,
        \Carbon\Carbon $now
    ): string {
        $existing = DB::table('grades')
            ->where('student_id',       $studentId)
            ->where('class_id',         $classId)
            ->where('subject_id',       $subjectId)
            ->where('academic_year_id', $yearId)
            ->where('trimestre',        $trimestre)
            ->where('type',             $type)
            ->where('sequence',         $seq)
            ->first();

        if (!$existing) {
            DB::table('grades')->insert([
                'student_id'       => $studentId,
                'class_id'         => $classId,
                'subject_id'       => $subjectId,
                'academic_year_id' => $yearId,
                'trimestre'        => $trimestre,
                'type'             => $type,
                'sequence'         => $seq,
                'value'            => $val,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
            return 'inserted';
        }

        // Valeur identique → skip
        if (abs((float)$existing->value - $val) < 0.001) {
            return 'skipped';
        }

        // Valeur différente → mise à jour
        $this->command->line(sprintf(
            '  │  <comment>↻ Update</comment> %s seq%d étudiant#%d : %s → %s',
            $type, $seq, $studentId,
            number_format((float)$existing->value, 2),
            number_format($val, 2)
        ));

        DB::table('grades')
            ->where('id', $existing->id)
            ->update(['value' => $val, 'updated_at' => $now]);

        return 'updated';
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  UPSERT CONDUITE
    // ─────────────────────────────────────────────────────────────────────────
    private function upsertConduite(
        int    $studentId,
        int    $classId,
        int    $yearId,
        int    $trimestre,
        float  $grade,
        \Carbon\Carbon $now
    ): string {
        $existing = DB::table('conducts')
            ->where('student_id',       $studentId)
            ->where('academic_year_id', $yearId)
            ->where('trimestre',        $trimestre)
            ->first();

        if (!$existing) {
            // Récupérer entity_id depuis student_academic_records
            // (colonne NOT NULL dans la table conducts)
            $record = DB::table('student_academic_records')
                ->where('student_id',       $studentId)
                ->where('class_id',         $classId)
                ->where('academic_year_id', $yearId)
                ->first();

            $entityId = $record?->entity_id
                ?? DB::table('classes')->where('id', $classId)->value('entity_id');

            if ($entityId === null) {
                throw new \RuntimeException(
                    "entity_id introuvable pour student_id={$studentId} class_id={$classId}"
                );
            }

            DB::table('conducts')->insert([
                'student_id'       => $studentId,
                'academic_year_id' => $yearId,
                'trimestre'        => $trimestre,
                'grade'            => $grade,
                'entity_id'        => $entityId,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
            return 'inserted';
        }

        if (abs((float)$existing->grade - $grade) < 0.001) {
            return 'skipped';
        }

        $this->command->line(sprintf(
            '  │  <comment>↻ Conduite update</comment> étudiant#%d : %s → %s',
            $studentId,
            number_format((float)$existing->grade, 2),
            number_format($grade, 2)
        ));

        DB::table('conducts')
            ->where('id', $existing->id)
            ->update(['grade' => $grade, 'updated_at' => $now]);

        return 'updated';
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  SYNCHRONISATION DU COEFFICIENT
    // ─────────────────────────────────────────────────────────────────────────
    private function syncCoefficient(int $classId, int $subjectId, int $yearId, int $coef): void
    {
        $existing = DB::table('class_teacher_subject')
            ->where('class_id',         $classId)
            ->where('subject_id',       $subjectId)
            ->where('academic_year_id', $yearId)
            ->first();

        if ($existing) {
            // Relation existante → mettre à jour le coefficient uniquement
            DB::table('class_teacher_subject')
                ->where('id', $existing->id)
                ->update(['coefficient' => $coef, 'updated_at' => now()]);
            return;
        }

        // Vérifier si teacher_id est nullable dans la table
        // En inspectant la contrainte BD : on tente d'abord de trouver un teacher
        // via l'année active (classe du même nom) pour ne pas laisser null
        $teacherId = DB::table('class_teacher_subject')
            ->join('classes', 'class_teacher_subject.class_id', '=', 'classes.id')
            ->join('classes as c2', function ($join) use ($classId) {
                $join->on('c2.name', '=', 'classes.name')
                     ->where('c2.id', '=', $classId);
            })
            ->where('class_teacher_subject.subject_id', $subjectId)
            ->whereNotNull('class_teacher_subject.teacher_id')
            ->orderByDesc('class_teacher_subject.academic_year_id')
            ->value('class_teacher_subject.teacher_id');

        if ($teacherId === null) {
            // teacher_id NOT NULL en BD et aucun teacher trouvé :
            // on ne crée PAS la relation pour éviter la violation de contrainte.
            // Les notes seront quand même insérées (syncCoefficient n'est pas bloquant).
            $this->command->line(
                '  │  <comment>⚠ class_teacher_subject non créée</comment>'
                . " (subject_id={$subjectId} class_id={$classId})"
                . ' → aucun teacher_id disponible. Assignez un enseignant manuellement.'
            );
            return;
        }

        DB::table('class_teacher_subject')->insert([
            'class_id'         => $classId,
            'subject_id'       => $subjectId,
            'academic_year_id' => $yearId,
            'teacher_id'       => $teacherId,
            'coefficient'      => $coef,
            'amount_brut'      => '0.00',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  RÉSOLUTION DE LA MATIÈRE
    // ─────────────────────────────────────────────────────────────────────────
    private function resolveSubject(\Illuminate\Support\Collection $subjects, string $name): ?object
    {
        $n = $this->normalize($name);

        // Passe 1 : correspondance exacte normalisée
        $found = $subjects->first(fn($s) => $this->normalize($s->name) === $n);
        if ($found) return $found;

        // Passe 2 : l'un contient l'autre
        $found = $subjects->first(
            fn($s) => str_contains($this->normalize($s->name), $n)
                   || str_contains($n, $this->normalize($s->name))
        );
        if ($found) return $found;

        // Pour CONDUITE, pas de matière BD obligatoire
        // (le seeder utilise la table conducts directement)
        if ($n === 'CONDUITE') {
            // Retourner un objet factice pour ne pas bloquer
            return (object) ['id' => null, 'name' => 'CONDUITE'];
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  MATCHING ÉLÈVE (4 passes)
    // ─────────────────────────────────────────────────────────────────────────
    private function matchStudent(string $xlsName, array $index): ?object
    {
        $n     = $this->normalize($xlsName);
        $parts = explode(' ', $n);
        $ln    = $parts[0] ?? '';
        $fn1   = $parts[1] ?? '';

        // Passe 1 : nom complet normalisé
        if (isset($index[$n])) return $index[$n];

        // Passe 2 : last + 1er mot prénom
        if ($fn1 !== '' && isset($index["{$ln} {$fn1}"])) {
            return $index["{$ln} {$fn1}"];
        }

        // Passe 3 : last name seul
        if ($ln !== '' && isset($index[$ln])) {
            return $index[$ln];
        }

        // Passe 4 : parcours partiel avec double vérification last+fn1
        foreach ($index as $key => $student) {
            $kp = explode(' ', $key);
            if (
                count($kp) >= 2
                && $kp[0] === $ln
                && $fn1 !== ''
                && str_starts_with($kp[1] ?? '', $fn1)
            ) {
                return $student;
            }
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  UTILITAIRES
    // ─────────────────────────────────────────────────────────────────────────

    /** Normalise : MAJUSCULES, sans accents, espaces simples. */
    private function normalize(string $s): string
    {
        $s = mb_strtoupper(trim($s), 'UTF-8');
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        return preg_replace('/\s+/', ' ', trim($s));
    }

    /** Convertit en float [0, 20] ou null. */
    private function toFloat(mixed $v): ?float
    {
        if ($v === null || $v === false || $v === '') return null;

        $s = trim(str_replace(',', '.', (string)$v));

        if (in_array(strtolower($s), ['', 'nan', 'null', '-', 'n/a', '#n/a', '#div/0!'], true)) {
            return null;
        }

        if (!is_numeric($s)) return null;

        $f = (float)$s;
        return ($f >= 0.0 && $f <= 20.0) ? round($f, 4) : null;
    }

    /** Scan récursif des fichiers XLS/xlsx, trié par chemin. */
    private function scanFiles(string $dir): array
    {
        $files = [];

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($it as $file) {
            if (!$file->isFile()) continue;
            if (in_array(strtolower($file->getExtension()), ['xls', 'xlsx', 'xlsm'])) {
                $files[] = $file->getPathname();
            }
        }

        sort($files); // ordre alphabétique → déterministe
        return $files;
    }

    /** Chemin relatif pour l'affichage. */
    private function relPath(string $abs): string
    {
        return str_replace(base_path() . DIRECTORY_SEPARATOR, '', $abs);
    }

    private function fail(string $fname, string $relPath, string $reason): void
    {
        $this->command->line("  └─ <fg=red>✗ {$reason}</>");
        $this->command->line('');
        $this->failed[]  = ['file' => $relPath, 'reason' => $reason];
        $this->gErrors++;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  RAPPORT CSV
    // ─────────────────────────────────────────────────────────────────────────
    private function addCsvRow(
        string $file, int $line, string $xlsName, string $bdName,
        string $type, string $seq, string $val, string $action
    ): void {
        $this->csvRows[] = [$file, $line, $xlsName, $bdName, $type, $seq, $val, $action];
    }

    private function writeCsvReport(): void
    {
        if (empty($this->csvRows)) return;

        $logDir = storage_path('logs');
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $csvPath = $logDir . '/grades_import_' . $this->startedAt . '.csv';

        $fp = fopen($csvPath, 'w');
        if (!$fp) {
            $this->command->line("  <fg=red>⚠ Impossible d'écrire le rapport CSV : {$csvPath}</>");
            return;
        }

        fputcsv($fp, ['Fichier', 'Ligne', 'Nom XLS', 'Nom BD', 'Type', 'Séquence', 'Valeur', 'Action']);
        foreach ($this->csvRows as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);

        $this->command->line("  📋 Rapport CSV : <comment>{$csvPath}</comment>");
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  RÉSUMÉ FINAL
    // ─────────────────────────────────────────────────────────────────────────
    private function printSummary(): void
    {
        $this->command->line('');
        $this->banner('RAPPORT FINAL');

        if (!empty($this->gDone)) {
            $this->command->line('  <info>Fichiers traités (' . count($this->gDone) . ') :</info>');
            foreach ($this->gDone as $f) {
                $this->command->line("    <info>✓</info> {$f}");
            }
        }

        if (!empty($this->failed)) {
            $this->command->line('');
            $this->command->line('  <fg=red>Fichiers en erreur (' . count($this->failed) . ') :</fg=red>');
            foreach ($this->failed as $f) {
                $this->command->line("    <fg=red>✗</> {$f['file']} → {$f['reason']}");
            }
        }

        $p = fn(int $n) => str_pad((string)$n, 6, ' ', STR_PAD_LEFT);

        $this->command->line('');
        $this->command->line('  ┌──────────────────────────────────────┐');
        $this->command->line('  │  Notes insérées     : ' . $p($this->gInserted) . '           │');
        $this->command->line('  │  Notes mises à jour : ' . $p($this->gUpdated)  . '           │');
        $this->command->line('  │  Notes inchangées   : ' . $p($this->gSkipped)  . '           │');
        $this->command->line('  │  Erreurs fichiers   : ' . $p($this->gErrors)   . '           │');
        $this->command->line('  └──────────────────────────────────────┘');

        if (!empty($this->gUnmatched)) {
            $this->command->line('');
            $this->command->line(
                '  <fg=yellow>⚠ Élèves non matchés ('
                . count($this->gUnmatched) . ') :</fg=yellow>'
            );
            foreach ($this->gUnmatched as $u) {
                $this->command->line(
                    "    L{$u['row']} [{$u['file']}] → « {$u['name']} »"
                );
            }
            $this->command->line('');
            $this->command->line(
                '  <comment>→ Ces élèves n\'existent pas dans student_academic_records</comment>'
            );
            $this->command->line(
                '  <comment>  pour la classe/année correspondante. Vérifiez et relancez.</comment>'
            );
        }

        $this->command->line('');
        $this->command->line('  <info>Seeder terminé ✓</info>');
        $this->command->line('');
    }

    private function banner(string $title): void {
        $w    = 58;
        $line = str_repeat('═', $w);
        $pad  = str_pad($title, $w, ' ', STR_PAD_BOTH);
        $this->command->line('');
        $this->command->line("  ╔{$line}╗");
        $this->command->line("  ║{$pad}║");
        $this->command->line("  ╚{$line}╝");
        $this->command->line('');
    }
}