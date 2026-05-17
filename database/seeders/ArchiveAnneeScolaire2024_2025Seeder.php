<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Seeder : Rattrapage de l'année académique 2024-2025
 *
 * Ce seeder :
 *  1. S'assure que l'année académique "2024 - 2025" (id=3) existe.
 *  2. Crée (si absentes) les classes du secondaire pour cette année.
 *  3. Insère les 140 apprenants de la liste CSV dans leurs classes
 *     — en sautant tout élève dont le num_educ est déjà présent en base.
 *
 * Logique de mapping des classes CSV → noms normalisés :
 *   '1ère D'   → 1èreCD   (élève isolé, rattaché à la classe CD)
 *   '1èreC'    → 1èreCD   (idem)
 *   '1èreCD'   → 1èreCD
 *   '2nde CD'  → 2ndeCD
 *   '3ème '    → 3ème
 *   '4ème'     → 4ème
 *   '5ème'     → 5ème
 *   '6ème'     → 6ème
 *   'Tle B'    → TleAB
 *   'Tle D'    → TleCD
 *
 * Les frais de scolarité sont repris depuis les classes équivalentes
 * de l'année 2025-2026 (même montants).
 *
 * Pour retrouver le cursus complet d'un apprenant déjà inscrit en
 * 2025-2026, on lie les deux enregistrements via le même num_educ.
 */
class ArchiveAnneeScolaire2024_2025Seeder extends Seeder
{
    // ─── Constantes ────────────────────────────────────────────────────────────

    /** ID de l'année académique 2024-2025 tel qu'il existe déjà en base */
    private const YEAR_ID = 3;

    /** entity_id du secondaire */
    private const ENTITY_SECONDAIRE = 3;

    // ─── Mapping CSV → nom normalisé de classe ─────────────────────────────────
    private const CLASS_NAME_MAP = [
        '1ère D'   => '1èreCD',
        '1èreC'    => '1èreCD',
        '1èreCD'   => '1èreCD',
        '2nde CD'  => '2ndeCD',
        '3ème '    => '3ème',
        '3ème'     => '3ème',
        '4ème'     => '4ème',
        '5ème'     => '5ème',
        '6ème'     => '6ème',
        'Tle B'    => 'TleAB',
        'Tle D'    => 'TleCD',
    ];

    /**
     * Frais par classe normalisée.
     * Repris de l'année 2025-2026 (même politique tarifaire).
     */
    private const CLASS_FEES = [
        '6ème'   => ['school_fees' => 95000,  'registration_fee' => 10000, 're_registration_fee' => 5000],
        '5ème'   => ['school_fees' => 95000,  'registration_fee' => 10000, 're_registration_fee' => 5000],
        '4ème'   => ['school_fees' => 105000, 'registration_fee' => 10000, 're_registration_fee' => 5000],
        '3ème'   => ['school_fees' => 145000, 'registration_fee' => 10000, 're_registration_fee' => 5000],
        '2ndeCD' => ['school_fees' => 125000, 'registration_fee' => 10000, 're_registration_fee' => 5000],
        '1èreCD' => ['school_fees' => 125000, 'registration_fee' => 10000, 're_registration_fee' => 5000],
        'TleAB'  => ['school_fees' => 165000, 'registration_fee' => 10000, 're_registration_fee' => 5000],
        'TleCD'  => ['school_fees' => 165000, 'registration_fee' => 10000, 're_registration_fee' => 5000],
    ];

    // ─── Données brutes (extraites du fichier liste.csv) ───────────────────────
    // Format : [last_name, first_name, gender, phone, classe_csv]
    private const STUDENTS_RAW = [
        // ── 1ère D (1 élève) ──────────────────────────────────────────────────
        ['KOHONOU',          'M.Josué Lévi',            'M', null,       '1ère D'],

        // ── 1èreC (1 élève) ──────────────────────────────────────────────────
        ['SABIYERIMA',       'bOSSIMA',                 'M', null,       '1èreC'],

        // ── 1èreCD (5 élèves) ────────────────────────────────────────────────
        ['ADANHOUME',        'Chabel',                  'M', null,       '1èreCD'],
        ['DEGAN',            'Grâce',                   'M', '97584874', '1èreCD'],
        ['GNANVI',           'Lucresse',                'F', null,       '1èreCD'],
        ['GOULOLE',          'Lauren',                  'M', null,       '1èreCD'],
        ['KINDE',            'Marie-Alphonsine',        'F', null,       '1èreCD'],

        // ── 2nde CD (15 élèves) ──────────────────────────────────────────────
        ['ADOUNGBE',         'Maéva',                   'F', null,       '2nde CD'],
        ['AHLIN',            'Schanez',                 'F', null,       '2nde CD'],
        ['AWASSI',           'Béni',                    'F', null,       '2nde CD'],
        ['AYEDOUN',          'Merveille',               'F', null,       '2nde CD'],
        ['AYINON',           'Précieux',                'M', null,       '2nde CD'],
        ['BADE',             'Micrette',                'F', null,       '2nde CD'],
        ['CHINCOUN',         'Mauraine Ifè',            'F', null,       '2nde CD'],
        ['FAGNIBO',          'Phil-Terry',              'M', null,       '2nde CD'],
        ['FASSINOU',         'Elcy Pladia',             'F', null,       '2nde CD'],
        ['KOHONOU',          'BLANCHE',                 'F', null,       '2nde CD'],
        ['SEHA',             'Lumière',                 'F', null,       '2nde CD'],
        ['TCHINKOUN',        'Andy',                    'M', null,       '2nde CD'],
        ['VIGAN',            'Gertrude',                'F', '66625228', '2nde CD'],
        ['VODOUNON',         'Elvira',                  'F', null,       '2nde CD'],
        ['WILLIAMS',         'Shalom',                  'M', '96237713', '2nde CD'],

        // ── 3ème (21 élèves) ─────────────────────────────────────────────────
        ['ADAMA',            'Mariam',                  'F', null,       '3ème '],
        ['ANAGOSSI',         'Inès',                    'F', null,       '3ème '],
        ['AROUKO',           'Juliana',                 'M', null,       '3ème '],
        ['CODJO',            'Edwige',                  'F', null,       '3ème '],
        ['DEMAGNON',         'Patrick',                 'M', null,       '3ème '],
        ['DJOSSOU',          'Aubin',                   'M', null,       '3ème '],
        ['DONOU',            'Fréjus',                  'M', null,       '3ème '],
        ['DOSSEH',           'Kenneth',                 'M', null,       '3ème '],
        ['GANKPAN',          'Sylvia R.',               'F', '96301112', '3ème '],
        ['GANKPAN',          'Sylvie M.',               'F', '96301112', '3ème '],
        ['GOMENOU',          'Grâce',                   'F', null,       '3ème '],
        ['GOUDOU',           'Romano',                  'M', null,       '3ème '],
        ['HIDJO',            'Marc Antoine',            'M', null,       '3ème '],
        ['HOUNSOU',          'Derrick',                 'M', null,       '3ème '],
        ['KLICO',            'Destinée',                'F', null,       '3ème '],
        ['KOHONOU',          'David',                   'M', null,       '3ème '],
        ['KOUNDE',           'Immaculée',               'F', null,       '3ème '],
        ['KPAKPO',           'Fleurette',               'F', null,       '3ème '],
        ['LIONFIN',          'Amado Dieu-Donné',        'F', null,       '3ème '],
        ['ODJO',             'Rhonel',                  'M', null,       '3ème '],
        ['SEHA',             'Darell',                  'M', null,       '3ème '],

        // ── 4ème (32 élèves) ─────────────────────────────────────────────────
        ['ABAGLI',           'Ezéchiel',                'M', null,       '4ème'],
        ['ABANTE',           'Amandine',                'F', null,       '4ème'],
        ['ADADJI',           'Jacquelina',              'F', null,       '4ème'],
        ['ADANDE',           'Carelle Divine Yabo',     'F', null,       '4ème'],
        ['AHAMIDE',          'Vital',                   'M', null,       '4ème'],
        ['AHOUANMAGNAGAHOU', 'Hermione',                'F', null,       '4ème'],
        ['ASSOGBA',          'Gracia',                  'F', null,       '4ème'],
        ['AZONDEKON',        'Christiana',              'F', null,       '4ème'],
        ['BADE',             'Sèssi Emmanuella',        'F', null,       '4ème'],
        ['BAH L\'IMAM',      'Falilatou',               'F', '67091861', '4ème'],
        ['BAH-L\'IMAM',      'Faouziath',               'M', null,       '4ème'],
        ['BOCO',             'Isaac',                   'M', null,       '4ème'],
        ['BODJRENOU',        'Josias',                  'M', null,       '4ème'],
        ['DADEHOU',          'Adalric',                 'M', null,       '4ème'],
        ['DETONDJI',         'Fierté',                  'F', null,       '4ème'],
        ['DIDAGBE',          'Alex',                    'M', null,       '4ème'],
        ['DOUKPO',           'Michel',                  'M', '96138159', '4ème'],
        ['EDAYE',            'Yanëlle',                 'F', null,       '4ème'],
        ['GNADEKPA',         'Emmanuel',                'M', '96229244', '4ème'],
        ['GNAHA',            'Marjonelle',              'F', null,       '4ème'],
        ['GNANVI',           'Sylvain',                 'M', null,       '4ème'],
        ['HOUEMABE',         'Clotilde',                'F', null,       '4ème'],
        ['HOUNGUEVOU',       'Loufaz',                  'M', null,       '4ème'],
        ['KANGAN',           'Bricette',                'F', null,       '4ème'],
        ['KINTONOUZA',       'Hillary',                 'F', '61220249', '4ème'],
        ['LASSISSI',         'Zéynab',                  'F', '97968927', '4ème'],
        ['LOKONON',          'Marie-Anne',              'F', null,       '4ème'],
        ['MOUTAÏROU',        'Samir',                   'M', null,       '4ème'],
        ['TOFFOUN',          'Briand',                  'M', '66091063', '4ème'],
        ['TOKPONNON',        'Igor',                    'M', null,       '4ème'],
        ['VIGAN',            'Aubain',                  'M', '63497891', '4ème'],
        ['VODOUNSI',         'Stéphanas',               'M', null,       '4ème'],

        // ── 5ème (27 élèves) ─────────────────────────────────────────────────
        ['ABAGLI',           'Daniella',                'F', null,       '5ème'],
        ['ADADJI',           'Carine',                  'F', null,       '5ème'],
        ['ADADJI',           'Carlos',                  'M', null,       '5ème'],
        ['ADADJI',           'Géraldine',               'F', null,       '5ème'],
        ['AFAFA',            'Faith',                   'F', null,       '5ème'],
        ['AHLIN',            'Astride',                 'F', null,       '5ème'],
        ['ALIOU',            'Nanzif',                  'M', null,       '5ème'],
        ['AVOCE-KOUNOUDJ',   'Christ-Love',             'F', null,       '5ème'],
        ['AYINON',           'Bérekia',                 'F', null,       '5ème'],
        ['AZANDOSSESSI',     'Robert',                  'M', null,       '5ème'],
        ['BACHABI ALIDOU',   'Mouzâhir',                'M', null,       '5ème'],
        ['BADE',             'Nadine',                  'F', null,       '5ème'],
        ['BAH-AGBA',         'Imane Sylvia',            'F', null,       '5ème'],
        ['CHATIGRE',         'Jordy',                   'M', null,       '5ème'],
        ['GOMENOU',          'Majorelle',               'F', null,       '5ème'],
        ['GOUN',             'Emilie',                  'M', null,       '5ème'],
        ['HOUANGNI',         'Rayan Levan Ifedé',       'M', null,       '5ème'],
        ['HOUNTCHONOU',      'Marie-Exaucée',           'F', null,       '5ème'],
        ['KINDE',            'Hermann',                 'M', null,       '5ème'],
        ['KOHLA',            'Leslie',                  'F', '96253444', '5ème'],
        ['KPONON',           'Dotou Grâce',             'F', null,       '5ème'],
        ['SABIYERIMA',       'Werrra',                  'M', null,       '5ème'],
        ['SEHA',             'Isis Maëlia Mystéra',     'F', null,       '5ème'],
        ['SOUNNOUVOU',       'Noble Bertrand',          'M', null,       '5ème'],
        ['TOSSOUKPE',        'Hermione Ginette Calfridath', 'F', null,   '5ème'],
        ['VODOUNSI',         'Sosthène',                'M', null,       '5ème'],
        ['WARIGUI',          'Elsy',                    'F', null,       '5ème'],

        // ── 6ème (27 élèves) ─────────────────────────────────────────────────
        ['ADAMOU',           'Wakiratou',               'F', null,       '6ème'],
        ['AHOUANDOGBO',      'Jean-Eude',               'M', null,       '6ème'],
        ['ANAGOSSI',         'Carole',                  'F', null,       '6ème'],
        ['BATIMON-ALI',      'Ismael',                  'M', null,       '6ème'],
        ['BODJRENOU',        'Onésiphor',               'M', null,       '6ème'],
        ['COCOU',            'Charisma',                'F', null,       '6ème'],
        ['DACLOUNON',        'Michelle',                'F', null,       '6ème'],
        ['DAH-MOROU',        'Noha',                    'F', null,       '6ème'],
        ['DEGAN',            'Omael',                   'M', null,       '6ème'],
        ['FASSINOU',         'Edson',                   'M', null,       '6ème'],
        ['GANKPAN',          'Exaucé',                  'M', null,       '6ème'],
        ['HADJINDE',         'Farole',                  'F', null,       '6ème'],
        ['HOUENOU',          'Olivier',                 'M', null,       '6ème'],
        ['KAKANAKOU',        'Alex',                    'M', null,       '6ème'],
        ['KANGAN',           'Césaire',                 'M', null,       '6ème'],
        ['KLICO',            'Exaucé',                  'M', null,       '6ème'],
        ['LOKOSSOU',         'Junior',                  'M', null,       '6ème'],
        ['METONNOU',         'Exaucé',                  'M', null,       '6ème'],
        ['NOUKONME',         'Louange',                 'F', null,       '6ème'],
        ['ODJO',             'Emmanuel',                'M', null,       '6ème'],
        ['PARAPE',           'Mafaz',                   'M', null,       '6ème'],
        ['SAVI',             'Primaelle',               'F', null,       '6ème'],
        ['TOSSOU',           'Gael',                    'M', null,       '6ème'],
        ['VODONOU',          'Cica',                    'F', null,       '6ème'],
        ['YEHOUME',          'Falonne',                 'F', null,       '6ème'],
        ['ZINSOUGA',         'Keyxnel',                 'M', null,       '6ème'],
        ['ZONON',            'Patrick',                 'M', null,       '6ème'],

        // ── Tle B → TleAB (5 élèves) ─────────────────────────────────────────
        ['ALLIGNITO',        'Théodora',                'F', null,       'Tle B'],
        ['HODONOU',          'Inès',                    'F', null,       'Tle B'],
        ['KOUNDE',           'Ghislaine',               'F', null,       'Tle B'],
        ['ODAH',             'A.Belriche',              'F', null,       'Tle B'],
        ['VODONOU',          'Junior Serge Bignon',     'M', '69588217', 'Tle B'],

        // ── Tle D → TleCD (6 élèves) ─────────────────────────────────────────
        ['ABAGLI',           'David',                   'M', null,       'Tle D'],
        ['ADOUNGBE',         'Schékina',                'F', null,       'Tle D'],
        ['DONOU',            'Sènan Doloresse',         'F', null,       'Tle D'],
        ['KEKEHOUSSOU',      'Arielle',                 'F', null,       'Tle D'],
        ['MOUHAMADOU',       'Manal',                   'M', null,       'Tle D'],
        ['TAKOLODJOU',       'Déo-Gracias',             'M', null,       'Tle D'],
    ];

    // ───────────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        $this->command->info('══════════════════════════════════════════════');
        $this->command->info(' Seeder : Rattrapage 2024-2025');
        $this->command->info('══════════════════════════════════════════════');

        // 1. Vérifier / créer l'année académique 2024-2025
        $year = DB::table('academic_years')->where('id', self::YEAR_ID)->first();

        if (! $year) {
            DB::table('academic_years')->insert([
                'id'         => self::YEAR_ID,
                'name'       => '2024 - 2025',
                'active'     => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $this->command->info('✔ Année académique "2024 - 2025" créée (id=' . self::YEAR_ID . ').');
        } else {
            $this->command->info('✔ Année académique "2024 - 2025" déjà présente (id=' . self::YEAR_ID . ').');
        }

        // 2. Créer les classes pour 2024-2025 si absentes
        $classIdMap = $this->ensureClasses();

        // 3. Insérer les apprenants
        $this->insertStudents($classIdMap);

        $this->command->info('══════════════════════════════════════════════');
        $this->command->info(' Seeder terminé avec succès.');
        $this->command->info('══════════════════════════════════════════════');
    }

    // ─── Étape 2 : créer les classes ───────────────────────────────────────────

    /**
     * S'assure que chaque classe normalisée existe pour l'année 2024-2025.
     * Retourne un map [nom_normalisé => class_id].
     */
    private function ensureClasses(): array
    {
        $classIdMap = [];
        $now        = Carbon::now();

        // Noms normalisés distincts nécessaires pour ce seeder
        $needed = array_unique(array_values(self::CLASS_NAME_MAP));

        foreach ($needed as $name) {
            $fees = self::CLASS_FEES[$name] ?? [
                'school_fees'        => 0,
                'registration_fee'   => 10000,
                're_registration_fee'=> 5000,
            ];

            $existing = DB::table('classes')
                ->where('name',             $name)
                ->where('academic_year_id', self::YEAR_ID)
                ->where('entity_id',        self::ENTITY_SECONDAIRE)
                ->first();

            if ($existing) {
                $classIdMap[$name] = $existing->id;
                $this->command->line("  · Classe «{$name}» déjà présente (id={$existing->id}).");
            } else {
                $id = DB::table('classes')->insertGetId([
                    'name'                => $name,
                    'entity_id'           => self::ENTITY_SECONDAIRE,
                    'academic_year_id'    => self::YEAR_ID,
                    'teacher_id'          => null,
                    'school_fees'         => $fees['school_fees'],
                    'registration_fee'    => $fees['registration_fee'],
                    're_registration_fee' => $fees['re_registration_fee'],
                    'description'         => null,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
                $classIdMap[$name] = $id;
                $this->command->info("  ✔ Classe «{$name}» créée (id={$id}).");
            }
        }

        return $classIdMap;
    }

    // ─── Étape 3 : insérer les apprenants ──────────────────────────────────────

    private function insertStudents(array $classIdMap): void
    {
        $now      = Carbon::now();
        $inserted = 0;
        $skipped  = 0;
        $counter  = 1;

        foreach (self::STUDENTS_RAW as $row) {
            [$lastName, $firstName, $gender, $phone, $classCsv] = $row;

            // Résoudre le nom normalisé de classe
            $className = self::CLASS_NAME_MAP[trim($classCsv)] ?? null;
            if (! $className) {
                $this->command->warn("  ⚠ Classe inconnue «{$classCsv}» pour {$lastName} {$firstName} — ignoré.");
                $skipped++;
                continue;
            }

            $classId = $classIdMap[$className] ?? null;
            if (! $classId) {
                $this->command->warn("  ⚠ ID de classe introuvable pour «{$className}» — ignoré.");
                $skipped++;
                continue;
            }

            // ── Détection de doublon ────────────────────────────────────────
            // Un doublon est détecté si :
            //   - même last_name (insensible à la casse)
            //   - ET au moins un prénom du CSV correspond à un prénom déjà en base
            //     (on découpe les prénoms composés par espace / tiret et teste chaque token)
            if ($this->studentAlreadyExists($lastName, $firstName)) {
                $this->command->line("  · {$lastName} {$firstName} déjà présent en base (doublon détecté) — sauté.");
                $skipped++;
                continue;
            }

            // Générer un num_educ unique : format ARC2425-XXXX
            $numEduc = $this->generateUniqueNumEduc($counter);
            $counter++;

            // Calculer le total_fees (re_registration par défaut pour les archives)
            $fees      = self::CLASS_FEES[$className] ?? ['school_fees' => 0, 'registration_fee' => 10000, 're_registration_fee' => 5000];
            $totalFees = $fees['school_fees'] + $fees['re_registration_fee'];

            DB::table('students')->insert([
                'first_name'           => $firstName,
                'last_name'            => $lastName,
                'birth_date'           => '2000-01-01', // date par défaut (non disponible dans le CSV)
                'birth_place'          => null,
                'age'                  => 20,           // âge par défaut (colonne NOT NULL)
                'gender'               => $gender,
                'num_educ'             => $numEduc,
                'entity_id'            => self::ENTITY_SECONDAIRE,
                'academic_year_id'     => self::YEAR_ID,
                'class_id'             => $classId,
                'birth_certificate'    => null,
                'vaccination_card'     => null,
                'previous_report_card' => null,
                'diploma_certificate'  => null,
                'parent_full_name'     => null,
                'parent_email'         => null,
                'parent_phone'         => $phone,
                'school_fees'          => $fees['school_fees'],
                'is_validated'         => true,
                'amount_paid'          => 0,
                'school_fees_paid'     => 0,
                'fully_paid'           => false,
                'registration_type'    => 're_registration',
                'total_fees'           => $totalFees,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);

            $this->command->line("  ✔ Inséré : {$lastName} {$firstName} → {$className} [{$numEduc}]");
            $inserted++;
        }

        $this->command->info("");
        $this->command->info("  Résultat : {$inserted} apprenants insérés, {$skipped} ignorés.");
    }

    // ─── Détection de doublon ──────────────────────────────────────────────────

    /**
     * Retourne true si un étudiant avec le même nom de famille ET au moins
     * un token de prénom commun existe déjà en base (toutes années confondues).
     *
     * Logique :
     *   1. Récupérer tous les étudiants ayant le même last_name (icase).
     *   2. Pour chacun, découper first_name en tokens (espace, tiret, point).
     *   3. Si l'intersection des tokens CSV et des tokens DB est non vide → doublon.
     */
    private function studentAlreadyExists(string $lastName, string $firstName): bool
    {
        // Récupérer les candidats avec le même nom de famille
        $candidates = DB::table('students')
            ->whereRaw('LOWER(last_name) = ?', [mb_strtolower($lastName)])
            ->pluck('first_name');

        if ($candidates->isEmpty()) {
            return false;
        }

        // Tokeniser le prénom du CSV
        $csvTokens = $this->tokenizeFirstName($firstName);

        foreach ($candidates as $dbFirstName) {
            $dbTokens = $this->tokenizeFirstName($dbFirstName);
            // Intersection non vide = au moins un token commun
            if (! empty(array_intersect($csvTokens, $dbTokens))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Découpe un prénom composé en tokens normalisés (minuscules, sans accents).
     * Séparateurs : espace, tiret, point.
     * Les tokens de moins de 2 caractères sont ignorés (initiales seules).
     */
    private function tokenizeFirstName(string $name): array
    {
        // Normaliser : minuscules, retirer les accents
        $normalized = mb_strtolower($name);
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);

        // Découper sur espace, tiret, point
        $parts = preg_split('/[\s\-\.]+/', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        // Filtrer les tokens trop courts (initiales du type "M.")
        return array_values(array_filter($parts, fn($t) => mb_strlen($t) >= 2));
    }

    // ─── Utilitaire : générer un num_educ unique ───────────────────────────────

    private function generateUniqueNumEduc(int $counter): string
    {
        do {
            $numEduc = 'ARC2425-' . str_pad($counter, 4, '0', STR_PAD_LEFT);
            $counter++;
            $exists = DB::table('students')->where('num_educ', $numEduc)->exists();
        } while ($exists);

        return $numEduc;
    }
}