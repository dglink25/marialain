<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class HistoricalStudentsSeeder extends Seeder{
    // ── Correspondance nom-de-fichier → academic_year_id ─────────────────────
    private const FILES = [
        '2017-2018.csv'      => 10,
        '2018-2019.csv'      => 9,
        '2019-2020.csv'      => 8,
        '2020-2021.csv'      => 7,
        '2021-2022.csv'      => 6,
        '2022-2023.csv'      => 5,
        '2023-2024.csv'      => 4,
        'liste_2024-2025.csv'=> 3,
    ];

    /**
     * Normalisation des noms de classes issus du CSV vers les noms canoniques
     * de la table `classes`.  Tous les tokens sont nettoyés (espaces, accents
     * qui peuvent varier selon la saisie).
     */
    private const CLASS_MAP = [
        // ── Terminale ─────────────────────────────────────────────────────────
        'Tle A2'    => 'TleAB',
        'Tle B'     => 'TleAB',
        'TleAB'     => 'TleAB',
        'Tle AB'    => 'TleAB',
        'Tle D'     => 'TleCD',
        'TleCD'     => 'TleCD',
        'Tle CD'    => 'TleCD',
        // ── Première ─────────────────────────────────────────────────────────
        '1ère A'    => '1èreAB',
        '1ère B'    => '1èreAB',
        '1ère AB'   => '1èreAB',
        '1èreAB'    => '1èreAB',
        '1ere AB'   => '1èreAB',
        '1ère C'    => '1èreCD',
        '1ère D'    => '1èreCD',
        '1ère CD'   => '1èreCD',
        '1èreC'     => '1èreCD',
        '1èreCD'    => '1èreCD',
        '1ereCD'    => '1èreCD',
        '1ere CD'   => '1èreCD',
        // ── Seconde ──────────────────────────────────────────────────────────
        '2nde A'    => '2ndeAB',
        '2nde B'    => '2ndeAB',
        '2nde AB'   => '2ndeAB',
        '2ndeAB'    => '2ndeAB',
        '2nde C'    => '2ndeCD',
        '2nde D'    => '2ndeCD',
        '2nde CD'   => '2ndeCD',
        '2ndeC'     => '2ndeCD',
        '2ndeCD'    => '2ndeCD',
        // ── Collège ──────────────────────────────────────────────────────────
        '3ème'      => '3ème',
        '3eme'      => '3ème',
        '3ème MC'   => '3ème',
        '3ème ML'   => '3ème',
        '4ème'      => '4ème',
        '4eme'      => '4ème',
        '4ème A'    => '4ème',
        '4ème B'    => '4ème',
        '5ème'      => '5ème',
        '5eme'      => '5ème',
        '6ème'      => '6ème',
        '6eme'      => '6ème',
        // ── Primaire ─────────────────────────────────────────────────────────
        'CI-A'      => 'CI',
        'CI-B'      => 'CI',
        'CI'        => 'CI',
        'CE1'       => 'CE1',
        'CE2'       => 'CE2',
        'CM1'       => 'CM1',
        'CM2'       => 'CM2',
        'CP'        => 'CP',
        // ── Maternelle ───────────────────────────────────────────────────────
        'Maternelle Grande Section'  => 'Maternelle Grande Section',
        'Maternelle Petite Section'  => 'Maternelle Petite Section',
        'Maternelle grande section'  => 'Maternelle Grande Section',
        'Maternelle petite section'  => 'Maternelle Petite Section',
        // ── À ignorer ────────────────────────────────────────────────────────
        'ABANDON'   => null,
    ];

    // ── Compteurs globaux ─────────────────────────────────────────────────────
    private int $studentsCreated  = 0;
    private int $studentsMatched  = 0;
    private int $recordsCreated   = 0;
    private int $recordsSkipped   = 0;
    private int $rowsSkipped      = 0;

    /** IDs des élèves créés par CE seeder (pour la mise à jour finale). */
    private array $seederStudentIds = [];

    /**
     * Cache (canonical_class_name, academic_year_id) → class_id
     * Alimenté une seule fois depuis la base au départ.
     */
    private array $classCache = [];

    // ─────────────────────────────────────────────────────────────────────────
    public function run(): void
    {
        $dataDir = database_path('data');

        $this->command->info('════════════════════════════════════════════════════════');
        $this->command->info('  HistoricalStudentsSeeder — démarrage');
        $this->command->info('════════════════════════════════════════════════════════');

        // ── 1. Pré-charger le cache des classes ───────────────────────────────
        $this->loadClassCache();

        // ── 2. Traiter chaque fichier dans l'ordre chronologique ──────────────
        foreach (self::FILES as $filename => $yearId) {
            $filePath = $dataDir . DIRECTORY_SEPARATOR . $filename;

            if (!file_exists($filePath)) {
                $this->command->warn("  [SKIP] Fichier introuvable : {$filePath}");
                continue;
            }

            $this->command->info("\n   Traitement : {$filename}  (année_id={$yearId})");
            $this->processFile($filePath, $yearId);
        }

        // ── 3. Mise à jour finale classe/année pour les nouveaux élèves ───────
        $this->command->info("\n   Mise à jour class_id / academic_year_id des nouveaux élèves…");
        $this->updateNewStudentsLastClassYear();

        // ── 4. Résumé ─────────────────────────────────────────────────────────
        $this->command->info('');
        $this->command->info('════════════════════════════════════════════════════════');
        $this->command->info('  RÉSUMÉ FINAL');
        $this->command->info('════════════════════════════════════════════════════════');
        $this->command->info("  Nouveaux élèves créés dans students           : {$this->studentsCreated}");
        $this->command->info("  Élèves existants retrouvés (non modifiés)     : {$this->studentsMatched}");
        $this->command->info("  Records créés dans student_academic_records   : {$this->recordsCreated}");
        $this->command->info("  Records déjà existants (ignorés)              : {$this->recordsSkipped}");
        $this->command->info("  Lignes CSV ignorées (classe/données invalides): {$this->rowsSkipped}");
        $this->command->info('════════════════════════════════════════════════════════');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  TRAITEMENT D'UN FICHIER
    // ─────────────────────────────────────────────────────────────────────────

    private function processFile(string $filePath, int $yearId): void
    {
        $rows = $this->parseCsv($filePath);

        if (empty($rows)) {
            $this->command->warn('    Fichier vide ou non parsable.');
            return;
        }

        $bar = $this->command->getOutput()->createProgressBar(count($rows));
        $bar->start();

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $this->processRow($row, $yearId);
                $bar->advance();
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error("\n    ERREUR — rollback : " . $e->getMessage());
            Log::error('[HistoricalStudentsSeeder] ' . $e->getMessage(), [
                'file'    => $filePath,
                'year_id' => $yearId,
                'trace'   => $e->getTraceAsString(),
            ]);
        }

        $bar->finish();
        $this->command->info('');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  TRAITEMENT D'UNE LIGNE
    // ─────────────────────────────────────────────────────────────────────────

    private function processRow(array $row, int $yearId): void
    {
        // ── Extraire et nettoyer les champs ───────────────────────────────────
        $lastName  = $this->normalizeName($row['last_name'] ?? '');
        $firstName = $this->normalizeName($row['first_name'] ?? '');
        $gender    = $this->normalizeGender($row['gender'] ?? 'M');
        $phone     = $this->normalizePhone($row['phone'] ?? '');
        $rawClass  = trim($row['class'] ?? '');

        // Ignorer les lignes sans nom ou prénom significatifs
        if ($lastName === '' || $firstName === '') {
            $this->rowsSkipped++;
            return;
        }

        // ── Résoudre la classe ────────────────────────────────────────────────
        $classId = $this->resolveClassId($rawClass, $yearId);

        if ($classId === null) {
            // Classe inconnue ou ABANDON → on ignore silencieusement
            $this->rowsSkipped++;
            return;
        }

        // ── Chercher l'élève existant (nom + premier prénom) ──────────────────
        $firstPrenom = $this->extractFirstPrenom($firstName);
        $studentId   = $this->findStudent($lastName, $firstPrenom);

        if ($studentId === null) {
            // ── Créer le nouvel élève ─────────────────────────────────────────
            $studentId = $this->createStudent($lastName, $firstName, $gender, $phone);
        } else {
            $this->studentsMatched++;
        }

        // ── Créer le record si absent ─────────────────────────────────────────
        $this->upsertAcademicRecord($studentId, $yearId, $classId, $gender, $phone);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  CRÉATION D'UN ÉLÈVE
    // ─────────────────────────────────────────────────────────────────────────

    private function createStudent(
        string $lastName,
        string $firstName,
        string $gender,
        string $phone
    ): int {
        $matricule = $this->generateMatricule();

        // class_id / academic_year_id seront mis à jour à la fin
        $id = DB::table('students')->insertGetId([
            'last_name'           => $lastName,
            'first_name'          => $firstName,
            'gender'              => $gender,
            'num_educ'            => $matricule,
            'registration_number' => $matricule,
            'birth_date'          => '2000-01-01',   // placeholder
            'age'                 => 15,
            'parent_phone'        => $phone ?: null,
            'school_fees_paid'    => 0,
            'amount_paid'         => 0,
            'school_fees'         => 0,
            'total_fees'          => 0,
            'fully_paid'          => false,
            'validated'           => false,
            'is_validated'        => false,
            'entity_id'           => 3,              // défaut lycée; mis à jour après
            'class_id'            => null,           // mis à jour après
            'academic_year_id'    => null,           // mis à jour après
            'registration_type'   => 're_registration',
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        $this->studentsCreated++;
        $this->seederStudentIds[] = $id;

        return $id;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  UPSERT DU RECORD ACADÉMIQUE
    // ─────────────────────────────────────────────────────────────────────────

    private function upsertAcademicRecord(
        int    $studentId,
        int    $yearId,
        int    $classId,
        string $gender,
        string $phone
    ): void {
        // Vérifier si le record existe déjà
        $exists = DB::table('student_academic_records')
            ->where('student_id', $studentId)
            ->where('academic_year_id', $yearId)
            ->exists();

        if ($exists) {
            $this->recordsSkipped++;
            return;
        }

        // Récupérer les infos de l'élève pour le snapshot
        $student = DB::table('students')->where('id', $studentId)->first();

        // Récupérer l'entity_id depuis la classe
        $classe = DB::table('classes')->where('id', $classId)->first();
        $entityId = $classe ? $classe->entity_id : ($student->entity_id ?? 3);

        DB::table('student_academic_records')->insert([
            'student_id'           => $studentId,
            'academic_year_id'     => $yearId,
            'class_id'             => $classId,
            'entity_id'            => $entityId,
            'first_name'           => $student->first_name,
            'last_name'            => $student->last_name,
            'birth_date'           => $student->birth_date ?? '2000-01-01',
            'birth_place'          => $student->birth_place ?? null,
            'gender'               => $gender,
            'num_educ'             => $student->num_educ,
            'parent_full_name'     => $student->parent_full_name ?? null,
            'parent_email'         => $student->parent_email ?? null,
            'parent_phone'         => $phone ?: ($student->parent_phone ?? null),
            'registration_type'    => $student->registration_type ?? 're_registration',
            'total_fees'           => $student->total_fees ?? 0,
            'amount_paid'          => $student->amount_paid ?? 0,
            'moy_trimestre_1'      => null,
            'moy_trimestre_2'      => null,
            'moy_trimestre_3'      => null,
            'moy_annuelle'         => null,
            'rang_annuel'          => null,
            'statut_deliberation'  => 'pending',
            'next_class_id'        => null,
            'next_academic_year_id'=> null,
            'is_validated'         => (bool)($student->is_validated ?? false),
            'archived_at'          => now(),
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        $this->recordsCreated++;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  MISE À JOUR FINALE : dernière classe/année pour les nouveaux élèves
    // ─────────────────────────────────────────────────────────────────────────

    private function updateNewStudentsLastClassYear(): void
    {
        if (empty($this->seederStudentIds)) {
            $this->command->info('    Aucun nouvel élève à mettre à jour.');
            return;
        }

        $updated = 0;

        foreach ($this->seederStudentIds as $studentId) {
            // Trouver le record avec l'academic_year_id le plus récent (ID max)
            $lastRecord = DB::table('student_academic_records')
                ->where('student_id', $studentId)
                ->orderByDesc('academic_year_id')
                ->first();

            if (!$lastRecord) {
                continue;
            }

            // Récupérer entity_id depuis la classe
            $classe = DB::table('classes')->where('id', $lastRecord->class_id)->first();
            $entityId = $classe ? $classe->entity_id : 3;

            DB::table('students')
                ->where('id', $studentId)
                ->update([
                    'class_id'         => $lastRecord->class_id,
                    'academic_year_id' => $lastRecord->academic_year_id,
                    'entity_id'        => $entityId,
                    'updated_at'       => now(),
                ]);

            $updated++;
        }

        $this->command->info("    {$updated} nouveaux élèves mis à jour (class_id / academic_year_id).");
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HELPERS — PARSING CSV
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Parse un fichier CSV encodé en ISO-8859-1 (latin-1) avec séparateur ';'.
     * Retourne un tableau de tableaux associatifs normalisés.
     */
    private function parseCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return [];
        }

        $rows    = [];
        $headers = null;

        while (($line = fgets($handle)) !== false) {
            // Convertir de latin-1 vers UTF-8
            $line = mb_convert_encoding($line, 'UTF-8', 'ISO-8859-1');
            $line = rtrim($line, "\r\n");

            if ($line === '') {
                continue;
            }

            $cols = str_getcsv($line, ';');

            if ($headers === null) {
                // Première ligne = en-têtes
                $headers = array_map('trim', $cols);
                continue;
            }

            // Padding pour correspondre au nombre de colonnes
            while (count($cols) < count($headers)) {
                $cols[] = '';
            }

            $assoc = array_combine($headers, array_slice($cols, 0, count($headers)));

            // Normaliser vers des clés internes fixes
            $rows[] = [
                'last_name'  => trim($assoc['Noms']              ?? ''),
                'first_name' => trim($assoc['Prénoms']           ?? $assoc['Pr?noms']  ?? ''),
                'gender'     => trim($assoc['Sexe (M/F)']        ?? 'M'),
                'phone'      => trim($assoc['Tél Apprenant']     ?? $assoc['T?l Apprenant'] ?? ''),
                'class'      => trim($assoc['Classe']            ?? ''),
            ];
        }

        fclose($handle);
        return $rows;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HELPERS — NORMALISATION
    // ─────────────────────────────────────────────────────────────────────────

    private function normalizeName(string $value): string
    {
        return trim($value);
    }

    /**
     * Extrait le premier token d'un prénom composé pour la recherche.
     * Ex : "Marie Bernise" → "Marie"  |  "M.Josué Lévi" → "M.Josué"
     */
    private function extractFirstPrenom(string $firstName): string
    {
        // Retirer les espaces et prendre le premier mot
        $parts = preg_split('/\s+/', trim($firstName), 2);
        return $parts[0] ?? $firstName;
    }

    private function normalizeGender(string $raw): string
    {
        $raw = strtoupper(trim($raw));
        return in_array($raw, ['M', 'F']) ? $raw : 'M';
    }

    private function normalizePhone(string $raw): string
    {
        return preg_replace('/[^0-9+]/', '', trim($raw));
    }

    /**
     * Normalise la chaîne de classe en retirant les variantes d'encodage résiduelles
     * puis cherche dans CLASS_MAP.
     */
    private function normalizeClassName(string $raw): ?string
    {
        $cleaned = trim($raw);

        // Corriger les artefacts d'encodage courants dans ces CSV
        $cleaned = str_replace(
            ["\xe8", "\xe9", "\xea", "\xc8", "\xc9"],
            ['è',    'é',    'ê',    'È',    'É'],
            $cleaned
        );

        // Cherche d'abord la valeur brute
        if (array_key_exists($cleaned, self::CLASS_MAP)) {
            return self::CLASS_MAP[$cleaned];
        }

        // Cherche en retirant les espaces de fin
        $trimmed = rtrim($cleaned);
        if (array_key_exists($trimmed, self::CLASS_MAP)) {
            return self::CLASS_MAP[$trimmed];
        }

        // Tentative insensible à la casse
        foreach (self::CLASS_MAP as $key => $val) {
            if (mb_strtolower($key) === mb_strtolower($trimmed)) {
                return $val;
            }
        }

        return null; // Classe non reconnue
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HELPERS — BASE DE DONNÉES
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Charge tout le cache (canonical_name, year_id) → class_id depuis la DB.
     */
    private function loadClassCache(): void
    {
        $rows = DB::table('classes')
            ->select('id', 'name', 'academic_year_id')
            ->get();

        foreach ($rows as $row) {
            $key = $row->name . '||' . $row->academic_year_id;
            $this->classCache[$key] = $row->id;
        }

        $this->command->info("  Cache classes chargé : " . count($this->classCache) . " entrées.");
    }

    /**
     * Résout le class_id à partir du nom brut du CSV et de l'année académique.
     * Retourne null si la classe est inconnue ou à ignorer (ABANDON, …).
     */
    private function resolveClassId(string $rawClass, int $yearId): ?int
    {
        $canonical = $this->normalizeClassName($rawClass);

        if ($canonical === null) {
            return null;
        }

        $key = $canonical . '||' . $yearId;

        return $this->classCache[$key] ?? null;
    }

    /**
     * Recherche un élève par (last_name, premier prénom) — insensible à la casse,
     * insensible aux accents grâce à ILIKE / LOWER sur PostgreSQL.
     *
     * Retourne l'ID de l'élève ou null si non trouvé.
     */
    private function findStudent(string $lastName, string $firstPrenom): ?int
    {
        // Recherche stricte d'abord
        $row = DB::table('students')
            ->whereRaw('LOWER(last_name)  = LOWER(?)', [$lastName])
            ->whereRaw('LOWER(first_name) LIKE LOWER(?)', [$firstPrenom . '%'])
            ->select('id')
            ->first();

        return $row?->id;
    }

    /**
     * Génère un matricule unique à 12 chiffres.
     * Format : YYMMDD + 6 chiffres aléatoires, unicité vérifiée en base.
     */
    private function generateMatricule(): string
    {
        do {
            $prefix    = date('ymd');               // 6 chiffres date
            $suffix    = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $matricule = $prefix . $suffix;          // 12 chiffres

            $exists = DB::table('students')
                ->where('num_educ', $matricule)
                ->orWhere('registration_number', $matricule)
                ->exists();

        } while ($exists);

        return $matricule;
    }
}