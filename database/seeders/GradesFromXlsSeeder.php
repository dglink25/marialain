<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class GradesFromXlsSeeder extends Seeder
{
    // ── Configuration ────────────────────────────────────────────────────────
    private const DATA_DIR       = 'database/seeders/data';
    private const FIRST_DATA_ROW = 12;

    // Colonnes métadonnées [row, col] (0-based)
    private const M_YEAR    = [3,  21];
    private const M_CLASS   = [5,  21];
    private const M_TRI     = [7,  21];
    private const M_SUBJECT = [9,  6];
    private const M_COEF    = [11, 6];

    // Colonnes données (0-based)
    private const C_NUM  = 0;
    private const C_NAME = 2;
    private const C_I1   = 7;
    private const C_I2   = 8;
    private const C_I3   = 10;
    private const C_I4   = 11;
    private const C_I5   = 12;
    private const C_D1   = 18;
    private const C_D2   = 21;
    private const C_MOY  = 27;

    private const INTERRO_COLS = [
        1 => self::C_I1,
        2 => self::C_I2,
        3 => self::C_I3,
        4 => self::C_I4,
        5 => self::C_I5,
    ];

    private const D2_TOLERANCE = 0.01;

    // ── État global ───────────────────────────────────────────────────────────
    private int   $gInserted  = 0;
    private int   $gUpdated   = 0;
    private int   $gSkipped   = 0;
    private int   $gErrors    = 0;
    private array $gUnmatched = [];
    private array $gDone      = [];
    private array $gFailed    = [];
    private array $csvRows    = [];
    private string $startedAt;

    /** Collection des matières BD – mise à jour dynamiquement si création auto */
    private \Illuminate\Support\Collection $allSubjects;

    // ─────────────────────────────────────────────────────────────────────────
    //  POINT D'ENTRÉE
    // ─────────────────────────────────────────────────────────────────────────
    public function run(): void
    {
        $this->startedAt = now()->format('Y-m-d_His');
        $this->banner('GradesFromXlsSeeder v3 – Import des notes');

        $dataDir = base_path(self::DATA_DIR);

        if (!is_dir($dataDir)) {
            $this->command->error("Dossier introuvable : {$dataDir}");
            $this->command->line('  → Créez-le et déposez-y vos sous-dossiers de fichiers XLS.');
            return;
        }

        $files = $this->scanFiles($dataDir);

        if (empty($files)) {
            $this->command->warn("Aucun fichier XLS/xlsx dans : {$dataDir}");
            return;
        }

        $this->command->line("  📁 <comment>{$dataDir}</comment>");
        $this->command->line('  📄 <info>' . count($files) . ' fichier(s) trouvé(s)</info>');
        $this->command->line('');

        // Charger les référentiels une seule fois
        $allYears          = DB::table('academic_years')->get()->keyBy('name');
        $this->allSubjects = DB::table('subjects')->get();

        $this->command->line(
            '  Référentiels BD : <info>' . $allYears->count()
            . ' années</info>, <info>' . $this->allSubjects->count() . ' matières</info>'
        );
        $this->command->line('');

        foreach ($files as $file) {
            $this->processFile($file, $allYears);
        }

        $this->writeCsvReport();
        $this->printSummary();
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  TRAITEMENT D'UN FICHIER
    // ─────────────────────────────────────────────────────────────────────────
    private function processFile(
        string $path,
        \Illuminate\Support\Collection $allYears
    ): void {
        $relPath = $this->relPath($path);
        $fname   = basename($path);

        $this->command->line("  ┌─ 📄 <comment>{$relPath}</comment>");

        try {
            $rows = $this->readXls($path);

            if (count($rows) < self::FIRST_DATA_ROW + 1) {
                $this->fail($fname, $relPath, 'Fichier vide ou trop court');
                return;
            }

            $meta = $this->extractMeta($rows, $fname);
            if ($meta === null) {
                $this->gFailed[] = ['file' => $relPath, 'reason' => 'Métadonnées invalides'];
                return;
            }

            $this->command->line("  │  <fg=cyan>Année</>     : {$meta['year_name']}");
            $this->command->line("  │  <fg=cyan>Classe(s)</> : {$meta['class_name']}");
            $this->command->line("  │  <fg=cyan>Trimestre</> : {$meta['trimestre']}");
            $this->command->line("  │  <fg=cyan>Matière</>   : {$meta['subject_name']} (coef {$meta['coef']})");

            // ── Résolution de l'année ─────────────────────────────────────────
            $year = $allYears->get($meta['year_name']);
            if (!$year) {
                $this->fail($fname, $relPath, "Année « {$meta['year_name']} » introuvable en BD");
                return;
            }

            $classes = $this->resolveClasses($meta['class_name'], $year->id);

            if (empty($classes)) {
                // Classe introuvable : la créer automatiquement en BD
                $newClass = $this->createMissingClass($meta['class_name'], $year->id);
                if ($newClass === null) {
                    $this->fail(
                        $fname, $relPath,
                        "Classe « {$meta['class_name']} » introuvable et impossible à créer"
                    );
                    return;
                }
                $this->command->line(
                    "  │  <fg=cyan>➕ Classe créée</> : <comment>{$newClass->name}</comment>"
                    . " (id={$newClass->id} entity_id={$newClass->entity_id})"
                );
                $classes = [$newClass];
            }

            if (count($classes) > 1) {
                $names = implode(', ', array_map(fn($c) => $c->name, $classes));
                $this->command->line("  │  <fg=yellow>⚡ Multi-classe</> : {$names}");
            }

            // ── Résolution de la matière ──────────────────────────────────────
            $subject    = $this->resolveSubject($meta['subject_name']);
            $isConduite = (strtoupper(trim($meta['subject_name'])) === 'CONDUITE');

            if (!$subject) {
                $this->fail(
                    $fname, $relPath,
                    "Matière « {$meta['subject_name']} » introuvable en BD"
                );
                return;
            }

            // ── Fusion des noms multilignes (une seule fois) ──────────────────
            $mergedRows = $this->mergeMultilineNames($rows);

            // ── Import dans chaque classe résolue ─────────────────────────────
            $totalStats = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'unmatched' => 0];

            foreach ($classes as $class) {
                if (!$isConduite) {
                    $this->syncCoefficient($class->id, $subject->id, $year->id, $meta['coef']);
                }

                $index = $this->buildStudentIndex($class->id, $year->id);

                if (empty($index)) {
                    $this->command->line(
                        "  │  <fg=yellow>⚠</> Aucun élève pour {$class->name} → ignoré"
                    );
                    continue;
                }

                $uniqueStudents = count(array_unique(array_map(fn($s) => $s->id, $index)));

                if (count($classes) > 1) {
                    $this->command->line(
                        "  │  <fg=cyan>{$class->name}</> : {$uniqueStudents} élève(s)"
                    );
                } else {
                    $this->command->line("  │  Élèves BD   : <info>{$uniqueStudents}</info>");
                }

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

                foreach ($totalStats as $k => $_) {
                    $totalStats[$k] += $stats[$k];
                }
            }

            $this->command->line(sprintf(
                '  └─ <info>✓</info> Inséré: <info>%d</info>'
                . ' | Mis à jour: <comment>%d</comment>'
                . ' | Ignoré: %d | Non trouvé: <fg=red>%d</>',
                $totalStats['inserted'], $totalStats['updated'],
                $totalStats['skipped'],  $totalStats['unmatched']
            ));
            $this->command->line('');

            $this->gInserted += $totalStats['inserted'];
            $this->gUpdated  += $totalStats['updated'];
            $this->gSkipped  += $totalStats['skipped'];
            $this->gDone[]    = $relPath;

        } catch (\Throwable $e) {
            $this->command->line("  └─ <fg=red>✗ ERREUR</> : " . $e->getMessage());
            $this->command->line('');
            Log::error("GradesFromXlsSeeder [{$fname}]: " . $e->getMessage(), [
                'file'  => $path,
                'trace' => $e->getTraceAsString(),
            ]);
            $this->gFailed[] = ['file' => $relPath, 'reason' => $e->getMessage()];
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
    
    private function resolveClasses(string $xlsName, int $yearId): array
    {
        $allClasses = DB::table('classes')
            ->where('academic_year_id', $yearId)
            ->get();

        $nXls = $this->normalize($xlsName);

        // ── Passe 1 : exact normalisé ─────────────────────────────────────────
        $exact = $allClasses->first(fn($c) => $this->normalize($c->name) === $nXls);
        if ($exact) {
            return [$exact];
        }

        // ── Passe 2 : décomposition préfixe + variants ────────────────────────
        [$prefix, $variants] = $this->splitPrefixVariants($xlsName);

        if (!empty($variants)) {
            $found = [];
            foreach ($variants as $variant) {
                $match = null;
                $candidates = $prefix !== ''
                    ? ["{$prefix}{$variant}", "{$prefix} {$variant}"]
                    : [$variant];

                foreach ($candidates as $cand) {
                    $match = $allClasses->first(
                        fn($c) => $this->normalize($c->name) === $this->normalize($cand)
                    );
                    if ($match) break;
                }

                if (!$match) {
                    $nVar    = $this->normalize($variant);
                    $nPrefix = $this->normalize($prefix);
                    $match   = $allClasses->first(function ($c) use ($nVar, $nPrefix) {
                        $nC     = $this->normalize($c->name);
                        $nCFlat = str_replace(' ', '', $nC);
                        $suffix = str_replace(' ', '', $nVar);
                        return str_ends_with($nCFlat, $suffix)
                            && ($nPrefix === '' || str_starts_with($nC, $nPrefix));
                    });
                }

                if ($match && !in_array($match->id, array_column($found, 'id'))) {
                    $found[] = $match;
                }
            }

            if (!empty($found)) {
                return $found;
            }
        }

     
        $mappedName = $this->resolveBySerieMapping($xlsName);

        if ($mappedName !== null) {
            $candidate = $allClasses->first(
                fn($c) => $this->normalize($c->name) === $this->normalize($mappedName)
            );

            if ($candidate) {
                // Vérifier que cette classe a bien des élèves (sinon mauvais mapping)
                $hasStudents = DB::table('student_academic_records')
                    ->where('class_id',         $candidate->id)
                    ->where('academic_year_id', $yearId)
                    ->exists();

                if ($hasStudents) {
                    $this->command->line(
                        "  │  <fg=cyan>↪ Alias série</> : « {$xlsName} » → <comment>{$candidate->name}</comment>"
                    );
                    return [$candidate];
                }
            }
        }

        // ── Passe 4 : fuzzy ───────────────────────────────────────────────────
        $fuzzy = $allClasses->filter(
            fn($c) => str_contains($this->normalize($c->name), $nXls)
                   || str_contains($nXls, $this->normalize($c->name))
        )->values()->all();

        if (!empty($fuzzy)) {
            return $fuzzy;
        }

        // ── Passe 5 : rien trouvé → retourner vide (l'appelant créera la classe)
        return [];
    }

    
    private function resolveBySerieMapping(string $xlsName): ?string
    {
        // Correspondances séries → groupe
        $serieToGroup = [
            'A'  => 'AB', 'A1' => 'AB', 'A2' => 'AB', 'A3' => 'AB', 'A4' => 'AB',
            'B'  => 'AB', 'B1' => 'AB', 'B2' => 'AB',
            'C'  => 'CD', 'C1' => 'CD', 'C2' => 'CD',
            'D'  => 'CD', "D'" => 'CD', 'D1' => 'CD', 'D2' => 'CD',
        ];

        // Niveaux reconnus et leur forme normalisée BD
        $niveaux = [
            '/^(tle|terminale)/i'  => 'Tle',
            '/^1[eè]re/i'          => '1ère',
            '/^2nde/i'             => '2nde',
            '/^3[eè]me/i'          => '3ème',
            '/^4[eè]me/i'          => '4ème',
            '/^5[eè]me/i'          => '5ème',
            '/^6[eè]me/i'          => '6ème',
        ];

        $name = trim($xlsName);

        // Identifier le niveau
        $niveauBD = null;
        foreach ($niveaux as $pattern => $bd) {
            if (preg_match($pattern, $name)) {
                $niveauBD = $bd;
                break;
            }
        }

        if ($niveauBD === null) {
            return null;
        }

        // Extraire la partie série (ce qui suit le niveau)
        $serieRaw = trim(preg_replace('/^(tle|terminale|1[eè]re|2nde|3[eè]me|4[eè]me|5[eè]me|6[eè]me)\s*/i', '', $name));

        if ($serieRaw === '') {
            return null;
        }

        // Résoudre le groupe
        $group = $serieToGroup[$serieRaw] ?? $serieToGroup[strtoupper($serieRaw)] ?? null;

        if ($group === null) {
            // Tentative par première lettre
            $first = strtoupper($serieRaw[0] ?? '');
            if (in_array($first, ['A', 'B'])) $group = 'AB';
            elseif (in_array($first, ['C', 'D'])) $group = 'CD';
        }

        if ($group === null) {
            return null;
        }

        return "{$niveauBD}{$group}";
    }

    /**
     * Décompose un nom de classe XLS en [préfixe, [variants]].
     *
     * "2nde CD/ PF"  → ["2nde", ["CD", "PF"]]
     * "1ère CD/ AB"  → ["1ère", ["CD", "AB"]]
     * "Tle AB"       → ["Tle",  ["AB"]]
     * "2ndeCD"       → ["",     ["2ndeCD"]]
     */
    private function splitPrefixVariants(string $xlsName): array
    {
        $frags = array_values(array_filter(
            array_map('trim', preg_split('/[\/,]/', $xlsName)),
            fn($f) => $f !== ''
        ));

        if (count($frags) >= 2) {
            $firstWords = explode(' ', trim($frags[0]));
            if (count($firstWords) >= 2) {
                $prefix   = implode(' ', array_slice($firstWords, 0, -1));
                $v1       = end($firstWords);
                $variants = array_merge([$v1], array_slice($frags, 1));
                return [$prefix, $variants];
            }
            return ['', $frags];
        }

        // Pas de / : tenter "Tle AB" → ["Tle", ["AB"]]
        $words = explode(' ', trim($xlsName));
        if (count($words) >= 2) {
            $prefix  = implode(' ', array_slice($words, 0, -1));
            $variant = end($words);
            return [$prefix, [$variant]];
        }

        return ['', [$xlsName]];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  CRÉATION AUTOMATIQUE D'UNE CLASSE MANQUANTE
    //
    //  Utilisé UNIQUEMENT si aucune des 4 passes précédentes n'a trouvé
    //  de classe avec des élèves. Crée la classe en BD en copiant les frais
    //  d'une classe similaire de même année et même entité.
    // ─────────────────────────────────────────────────────────────────────────
    private function createMissingClass(string $name, int $yearId): ?object
    {
        $upper = strtoupper($name);
        if (str_contains($upper, 'MATERNELLE') || str_contains($upper, 'PRE')) {
            $entityId = 1;
        } elseif (preg_match('/\b(CP|CI|CE1|CE2|CM1|CM2)\b/', $upper)) {
            $entityId = 2;
        } else {
            $entityId = 3;
        }

        $template = DB::table('classes')
            ->where('academic_year_id', $yearId)
            ->where('entity_id', $entityId)
            ->first()
            ?? DB::table('classes')
                ->where('academic_year_id', $yearId)
                ->first();

        $newId = DB::table('classes')->insertGetId([
            'name'                => trim($name),
            'entity_id'           => $entityId,
            'academic_year_id'    => $yearId,
            'teacher_id'          => null,
            'school_fees'         => $template?->school_fees         ?? '0.00',
            'registration_fee'    => $template?->registration_fee    ?? '0.00',
            're_registration_fee' => $template?->re_registration_fee ?? '0.00',
            'description'         => null,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        return DB::table('classes')->find($newId);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  FUSION DES NOMS SUR PLUSIEURS LIGNES
    // ─────────────────────────────────────────────────────────────────────────
    private function mergeMultilineNames(array $rows): array
    {
        $merged = [];
        $lastDataIdx = null;

        foreach ($rows as $ri => $row) {
            if ($ri < self::FIRST_DATA_ROW) {
                $merged[] = $row;
                continue;
            }

            $numVal  = $row[self::C_NUM]  ?? null;
            $nameVal = $row[self::C_NAME] ?? null;
            $numStr  = trim((string)$numVal);
            $nameStr = trim((string)$nameVal);

            if ($numStr === '' && $nameStr === '') {
                continue;
            }

            $hasAnyNote = false;
            foreach ([self::C_I1, self::C_I2, self::C_I3, self::C_I4, self::C_I5,
                      self::C_D1, self::C_D2, self::C_MOY] as $c) {
                if ($this->toFloat($row[$c] ?? null) !== null) {
                    $hasAnyNote = true;
                    break;
                }
            }

            if ($numStr === '' && $nameStr !== '' && !$hasAnyNote && $lastDataIdx !== null) {
                $merged[$lastDataIdx][self::C_NAME] =
                    trim((string)$merged[$lastDataIdx][self::C_NAME]) . ' ' . $nameStr;
                continue;
            }

            if ($numStr !== '') {
                $merged[]    = $row;
                $lastDataIdx = array_key_last($merged);
            }
        }

        return $merged;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  INDEX DES ÉLÈVES D'UNE CLASSE
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
        $index    = [];

        foreach ($students as $s) {
            $ln  = $this->normalize($s->last_name);
            $fn  = $this->normalize($s->first_name);
            $fn1 = explode(' ', $fn)[0] ?? '';

            foreach ([
                0 => "{$ln} {$fn}",
                1 => "{$ln} {$fn1}",
                2 => $ln,
            ] as $prio => $key) {
                if (!isset($index[$key]) || $prio < $index[$key]['prio']) {
                    $index[$key] = ['s' => $s, 'prio' => $prio];
                }
            }
        }

        return array_map(fn($v) => $v['s'], $index);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  IMPORT GRADES
    // ─────────────────────────────────────────────────────────────────────────
    private function importGrades(
        array  $rows,
        array  $index,
        int    $classId,
        int    $subjectId,
        int    $yearId,
        int    $trimestre,
        string $relPath
    ): array {
        $inserted = $updated = $skipped = $unmatched = 0;
        $now      = now();

        foreach ($rows as $ri => $row) {
            if ($ri < self::FIRST_DATA_ROW) continue;

            $num  = trim((string)($row[self::C_NUM]  ?? ''));
            $name = trim((string)($row[self::C_NAME] ?? ''));

            if ($num === '' || !is_numeric($num)) continue;
            if ($name === '') continue;
            if (stripos($name, 'Imprimé') !== false) break;

            $student = $this->matchStudent($name, $index);

            if (!$student) {
                // Un élève non trouvé dans cette classe peut appartenir à une autre
                // classe résolue du même XLS → on ne le compte pas en erreur globale ici,
                // le compteur "unmatched" est par classe
                $unmatched++;
                $this->addCsvRow($relPath, $ri+1, $name, '—', '—', '—', '—', 'NON_TROUVÉ');
                continue;
            }

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
                    $relPath, $ri+1, $name,
                    $student->last_name . ' ' . $student->first_name,
                    $note['type'], (string)$note['seq'], (string)$note['val'],
                    strtoupper($action)
                );
            }
        }

        return compact('inserted', 'updated', 'skipped', 'unmatched');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  IMPORT CONDUITES
    // ─────────────────────────────────────────────────────────────────────────
    private function importConduites(
        array  $rows,
        array  $index,
        int    $classId,
        int    $yearId,
        int    $trimestre,
        string $relPath
    ): array {
        $inserted = $updated = $skipped = $unmatched = 0;
        $now      = now();

        foreach ($rows as $ri => $row) {
            if ($ri < self::FIRST_DATA_ROW) continue;

            $num  = trim((string)($row[self::C_NUM]  ?? ''));
            $name = trim((string)($row[self::C_NAME] ?? ''));

            if ($num === '' || !is_numeric($num)) continue;
            if ($name === '') continue;
            if (stripos($name, 'Imprimé') !== false) break;

            $student = $this->matchStudent($name, $index);

            if (!$student) {
                $unmatched++;
                $this->addCsvRow($relPath, $ri+1, $name, '—', 'conduite', '—', '—', 'NON_TROUVÉ');
                continue;
            }

            $grade = $this->toFloat($row[self::C_D2] ?? null)
                  ?? $this->toFloat($row[self::C_MOY] ?? null);

            if ($grade === null) {
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
                $relPath, $ri+1, $name,
                $student->last_name . ' ' . $student->first_name,
                'conduite', '—', (string)$grade, strtoupper($action)
            );
        }

        return compact('inserted', 'updated', 'skipped', 'unmatched');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  EXTRACTION DES NOTES D'UNE LIGNE
    // ─────────────────────────────────────────────────────────────────────────
    private function extractGradeNotes(array $row): array
    {
        $notes = [];

        foreach (self::INTERRO_COLS as $seq => $col) {
            $v = $this->toFloat($row[$col] ?? null);
            if ($v !== null) {
                $notes[] = ['type' => 'interrogation', 'seq' => $seq, 'val' => $v];
            }
        }

        $d1 = $this->toFloat($row[self::C_D1] ?? null);
        if ($d1 !== null) {
            $notes[] = ['type' => 'devoir', 'seq' => 1, 'val' => $d1];
        }

        // D2 : réel si moy recalculée sans D2 ≠ moy XLS
        $d2raw  = $this->toFloat($row[self::C_D2] ?? null);
        $moyXls = $this->toFloat($row[self::C_MOY] ?? null);

        if ($d2raw !== null) {
            $interroVals = array_values(array_filter(
                array_map(fn($c) => $this->toFloat($row[$c] ?? null), self::INTERRO_COLS),
                fn($v) => $v !== null
            ));
            $moyI = !empty($interroVals)
                ? array_sum($interroVals) / count($interroVals)
                : null;
            $notesSansD2 = array_filter([$moyI, $d1], fn($v) => $v !== null);
            $moyRecalc   = !empty($notesSansD2)
                ? array_sum($notesSansD2) / count($notesSansD2)
                : null;

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

        if (abs((float)$existing->value - $val) < 0.001) {
            return 'skipped';
        }

        $this->command->line(sprintf(
            '  │  <comment>↻</comment> %s seq%d étudiant#%d : %s → %s',
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
            // entity_id depuis student_academic_records, fallback sur classes
            $record   = DB::table('student_academic_records')
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
            '  │  <comment>↻ conduite</comment> étudiant#%d : %s → %s',
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
            DB::table('class_teacher_subject')
                ->where('id', $existing->id)
                ->update(['coefficient' => $coef, 'updated_at' => now()]);
            return;
        }

        // Relation absente : chercher un teacher_id dans d'autres années
        // pour la même classe (même nom) et même matière
        $teacherId = DB::table('class_teacher_subject as cts')
            ->join('classes as c', 'cts.class_id', '=', 'c.id')
            ->join('classes as c2', function ($j) use ($classId) {
                $j->on('c2.name', '=', 'c.name')->where('c2.id', '=', $classId);
            })
            ->where('cts.subject_id', $subjectId)
            ->whereNotNull('cts.teacher_id')
            ->orderByDesc('cts.academic_year_id')
            ->value('cts.teacher_id');

        if ($teacherId === null) {
            // Aucun teacher trouvé pour cette classe/matière dans toutes les années.
            // Chercher n'importe quel enseignant qui enseigne cette matière.
            $teacherId = DB::table('class_teacher_subject')
                ->where('subject_id', $subjectId)
                ->whereNotNull('teacher_id')
                ->orderByDesc('academic_year_id')
                ->value('teacher_id');
        }

        if ($teacherId === null) {
            // En dernier recours: prendre le premier enseignant actif du système
            $teacherId = DB::table('users')
                ->whereNotNull('id')
                ->orderBy('id')
                ->value('id');
        }

        if ($teacherId === null) {
            // Vraiment aucun enseignant en BD — log et continuer sans créer la relation
            $this->command->line(
                '  │  <fg=yellow>⚠</> class_teacher_subject non créée'
                . " (subject_id={$subjectId} class_id={$classId})"
                . ' — aucun enseignant disponible en BD.'
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
    private function resolveSubject(string $name): ?object
    {
        // CONDUITE → objet factice, la table conducts gère tout
        if (strtoupper(trim($name)) === 'CONDUITE') {
            return (object) ['id' => null, 'name' => 'CONDUITE'];
        }

        $n = $this->normalize($name);

        // Passe 1 : correspondance exacte normalisée
        $found = $this->allSubjects->first(fn($s) => $this->normalize($s->name) === $n);
        if ($found) return $found;

        // Passe 2 : l'un contient l'autre
        $found = $this->allSubjects->first(
            fn($s) => str_contains($this->normalize($s->name), $n)
                   || str_contains($n, $this->normalize($s->name))
        );
        if ($found) return $found;

        // Passe 3 : la matière n'existe pas en BD → la créer automatiquement
        // Ex: "LV 1", "LV 2", etc. rencontrées dans des XLS historiques
        $this->command->line(
            "  │  <fg=cyan>➕ Création matière</> : <comment>{$name}</comment>"
        );

        $newId = DB::table('subjects')->insertGetId([
            'name'             => ucwords(strtolower(trim($name))),
            'academic_year_id' => null,
            'classe_id'        => null,
            'coefficient'      => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // Recharger la collection en mémoire pour les fichiers suivants
        $newSubject = DB::table('subjects')->find($newId);
        $this->allSubjects = $this->allSubjects->push($newSubject);

        return $newSubject;
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

        if (isset($index[$n])) return $index[$n];

        if ($fn1 !== '' && isset($index["{$ln} {$fn1}"])) {
            return $index["{$ln} {$fn1}"];
        }

        if ($ln !== '' && isset($index[$ln])) {
            return $index[$ln];
        }

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
    private function normalize(string $s): string
    {
        $s = mb_strtoupper(trim($s), 'UTF-8');
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        return preg_replace('/\s+/', ' ', trim($s));
    }

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

    private function scanFiles(string $dir): array
    {
        $files = [];
        $it    = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($it as $file) {
            if (!$file->isFile()) continue;
            if (in_array(strtolower($file->getExtension()), ['xls', 'xlsx', 'xlsm'])) {
                $files[] = $file->getPathname();
            }
        }
        sort($files);
        return $files;
    }

    private function relPath(string $abs): string
    {
        return str_replace(base_path() . DIRECTORY_SEPARATOR, '', $abs);
    }

    private function fail(string $fname, string $relPath, string $reason): void
    {
        $this->command->line("  └─ <fg=red>✗ {$reason}</>");
        $this->command->line('');
        $this->gFailed[] = ['file' => $relPath, 'reason' => $reason];
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
        if (!is_dir($logDir)) mkdir($logDir, 0755, true);

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

        if (!empty($this->gFailed)) {
            $this->command->line('');
            $this->command->line('  <fg=red>Fichiers en erreur (' . count($this->gFailed) . ') :</fg=red>');
            foreach ($this->gFailed as $f) {
                $this->command->line("    <fg=red>✗</> {$f['file']} → {$f['reason']}");
            }
        }

        $p = fn(int $n) => str_pad((string)$n, 7, ' ', STR_PAD_LEFT);

        $this->command->line('');
        $this->command->line('  ┌────────────────────────────────────┐');
        $this->command->line('  │  Notes insérées     : ' . $p($this->gInserted) . '          │');
        $this->command->line('  │  Notes mises à jour : ' . $p($this->gUpdated)  . '          │');
        $this->command->line('  │  Notes inchangées   : ' . $p($this->gSkipped)  . '          │');
        $this->command->line('  │  Erreurs fichiers   : ' . $p($this->gErrors)   . '          │');
        $this->command->line('  └────────────────────────────────────┘');

        if (!empty($this->gUnmatched)) {
            $this->command->line('');
            $this->command->line('  <fg=yellow>⚠ Élèves non matchés (' . count($this->gUnmatched) . ') :</fg=yellow>');
            foreach ($this->gUnmatched as $u) {
                $this->command->line("    L{$u['row']} [{$u['file']}] → « {$u['name']} »");
            }
        }

        $this->command->line('');
        $this->command->line('  <info>Seeder terminé ✓</info>');
        $this->command->line('');
    }

    private function banner(string $title): void
    {
        $w   = 58;
        $bar = str_repeat('═', $w);
        $pad = str_pad($title, $w, ' ', STR_PAD_BOTH);
        $this->command->line('');
        $this->command->line("  ╔{$bar}╗");
        $this->command->line("  ║{$pad}║");
        $this->command->line("  ╚{$bar}╝");
        $this->command->line('');
    }
}