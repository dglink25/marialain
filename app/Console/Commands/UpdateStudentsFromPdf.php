<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Commande artisan : php artisan students:update-from-pdf
 *
 * But : mettre à jour les colonnes `num_educ` (matricule) et `first_name` (prénom(s))
 *       pour les élèves déjà présents en base, en se basant sur les listes PDF
 *       du CPEG Marie-Alain 2025-2026.
 *
 * Stratégie de correspondance (du plus sûr au moins sûr) :
 *   1. Nom de famille  +  date de naissance  → 100 % fiable
 *   2. Nom de famille  +  au moins un prénom commun → très fiable
 *   3. Nom de famille  +  lieu de naissance  → fiable si pas d'homonyme
 *
 * Un enregistrement n'est mis à jour QUE si UNE SEULE correspondance est trouvée
 * et qu'au moins un prénom du PDF se retrouve dans le prénom stocké en base
 * (ou vice-versa), afin d'éviter les collisions sur les fratries (ex: ADADJI).
 */
class UpdateStudentsFromPdf extends Command
{
    protected $signature = 'students:update-from-pdf
                            {--dry-run : Simuler sans écrire en base}
                            {--verbose-log : Afficher le détail de chaque match}';

    protected $description = 'Met à jour num_educ et first_name des élèves depuis les données PDF 2025-2026';

    // ─────────────────────────────────────────────
    //  DONNÉES EXTRAITES DES PDFs
    //  Format : ['NOM', 'Prénoms complets', 'Matricule', 'Sexe', 'Date naissance (d/m/Y)', 'Lieu naissance', 'Classe']
    // ─────────────────────────────────────────────
    private array $pdfData = [
        // ── 6e ──────────────────────────────────────────────────────────────
        ['ABDOULAYE',       'Sammack',                                          '1150323458180', 'M', '22/02/2015', 'PARAKOU',              '6e'],
        ['ACLASSATO',       'Firdaouss',                                        '2140323130925', 'F', '31/12/2014', 'Cotonou',              '6e'],
        ['ADJAHOUIN',       'Laurelle Orylle Tobi',                             '2140323008303', 'F', '23/02/2014', 'Abomey-Calavi',        '6e'],
        ['ADJAÎ',           'Ruth Parfaite',                                    '2120323067355', 'F', '12/01/2012', 'Abomey-Calavi',        '6e'],
        ['AHIHA',           'Osée Ruben',                                       '1130324018168', 'M', '26/11/2013', 'Cotonou',              '6e'],
        ['AHOMAGNON',       'Orphée Yabo',                                      '2110323014399', 'F', '08/10/2011', 'Cotonou',              '6e'],
        ['ALLADATIN',       'Sephora Carmelle Iretiwa',                         '2140324054369', 'F', '03/06/2014', 'Cotonou',              '6e'],
        ['ALLOGAN',         'Sènoumantin Audace Prémicelove Marie-Luciano',     '1150423011084', 'M', '15/05/2015', 'PARAKOU',              '6e'],
        ['AMOUSSOU',        'Limatou',                                          '2140524002044', 'F', '20/12/2014', 'BANTE',                '6e'],
        ['ASSOGBA',         'Bruneil André Bignon',                             '1140324058628', 'M', '24/12/2014', 'Abomey-Calavi',        '6e'],
        ['ATCHIDI',         'Steev Rico',                                       '1110324018167', 'M', '29/05/2011', 'Cotonou',              '6e'],
        ['ATOLA',           'Miracle Arielle Imanlè',                           '2140323027500', 'F', '10/04/2014', 'Cotonou',              '6e'],
        ['CLOGBEDA',        'Christodule Destin Précieux',                      '1140324016045', 'M', '27/03/2014', 'Sèmè Kpodji',          '6e'],
        ['FAKEYE',          'Salomon Djamiou Lawale',                           '1140323130934', 'M', '23/03/2014', 'Abomey-Calavi',        '6e'],
        ['GNAHA',           'Dègnon Trésor Marélys',                            '2150323170720', 'F', '11/03/2015', 'Abomey-Calavi',        '6e'],
        ['GUEZO GNAGBOLOU', 'Aryel Ellison',                                    '1140323130936', 'M', '31/12/2014', 'Abomey-Calavi Aïtchedji','6e'],
        ['HOUNGBEDJI',      'Jeffrey Marie Tadagbé',                            '1140323130937', 'M', '08/11/2014', 'Sème-Podji',           '6e'],
        ['HOUNGUEVOU',      'Falouz Pascal Junior',                             '1140323130938', 'M', '20/04/2014', 'Cotonou',              '6e'],
        ['KANGAN',          'Césaire Jean-Jacques Tobi',                        '1120323252473', 'M', '26/08/2012', 'Cotonou',              '6e'],
        ['MONKOUN',         'Ibukun Maëlys Ilyes',                              '2150323129399', 'F', '08/04/2015', 'Cotonou',              '6e'],
        ['SAH',             'Kelvine Imanolle Oluwafèmi',                       '2140324067276', 'F', '03/06/2014', 'Cotonou',              '6e'],
        ['SODE',            'Prince Wanilo Gere',                               '1120323130942', 'M', '22/04/2012', 'Porto-Novo',           '6e'],
        ['SOUNNOUHO',       'Sèdjlo Sirius',                                    '1130324067131', 'M', '22/03/2013', 'Abomey-Calavi',        '6e'],
        ['SOUNNOUVOU',      'Kpèdétin Sarah',                                   '2140323130943', 'F', '19/12/2014', 'Abomey-Calavi',        '6e'],

        // ── 5e ──────────────────────────────────────────────────────────────
        ['ABDOULAYE',       'Hafizath',                                         '2120323033383', 'F', '30/09/2012', 'PARAKOU',              '5e'],
        ['ADAMOU',          'Wakiratou',                                        '2110323027087', 'F', '26/04/2011', 'Djougou',              '5e'],
        ['AHOUANDOGBO',     'Jean Eudes',                                       '1140323130927', 'M', '14/11/2014', 'Arr VIDOLE',           '5e'],
        ['ANAGOSSI',        'Oumè Carole Ruth',                                 '2130323635710', 'F', '23/01/2013', 'Abomey-Calavi',        '5e'],
        ['AVOCE-KOUNOUDJI', 'Christ Love',                                      '2100322183633', 'F', '10/07/2010', 'Menontin',             '5e'],
        ['BATIMON ALI',     'Ismaël',                                           '1130323635711', 'M', '22/10/2013', 'Cotonou',              '5e'],
        ['COCOU',           'Messeton Charisma Olayessan',                      '2130324002766', 'F', '25/10/2013', 'Menontin',             '5e'],
        ['DACLOUNON',       'Bignon Michelle Charnelle',                        '2130323635713', 'F', '12/08/2013', 'Cotonou',              '5e'],
        ['DAH-MOROU',       'Noha Blessing Guenieve Djidjoho',                  '2141023527748', 'F', '21/10/2014', 'Cotonou',              '5e'],
        ['DEGAN',           'Omaël Gilbert Egnonnoumi',                         '1120323635715', 'M', '20/08/2012', 'Cotonou',              '5e'],
        ['DENOU',           'Ethan Jérémy Naël',                                '1130323028352', 'M', '30/12/2013', 'Cotonou',              '5e'],
        ['DZINAKU',         'Yasmine',                                          '2120322133837', 'F', '18/03/2012', 'Akassato',             '5e'],
        ['FASSINOU',        'Edson Roland Tèkpo',                               '1130323635717', 'M', '15/09/2013', 'Abomey-Calavi',        '5e'],
        ['GANKPAN',         'Exaucé Arthur Joseph',                             '1140423035721', 'M', '21/03/2014', 'ABOMEY - CALAVI',      '5e'],
        ['GOUSSANOU',       'Mahutondin Fulvian Manoël Sovi',                   '1130323931714', 'M', '13/11/2013', 'Cotonou',              '5e'],
        ['HOUENOU',         'Mahuclo Olivier',                                  '1140323635719', 'M', '05/03/2014', 'Abomey-Calavi',        '5e'],
        ['KAKANAKOU',       'Alex Peter Prince Bidemi',                         '1140323252466', 'M', '17/02/2014', 'Cotonou',              '5e'],
        ['KLICO',           'Exaucé',                                           '1140323635722', 'M', '09/05/2013', 'Abomey-Calavi',        '5e'],
        ['LOKOSSOU',        'Junior Hervé',                                     '1140323635725', 'M', '30/05/2014', 'Cotonou',              '5e'],
        ['METONNOU',        'Kpèmanvo Exaucé',                                  '1130323059487', 'M', '06/04/2013', 'ABOMEY-CALAVI',        '5e'],
        ['ODJO',            'Temidayo Emmanuel Precieux',                       '1130323635727', 'M', '20/06/2013', 'Cotonou',              '5e'],
        ['TOSSOU',          'Gbènakpon Gaël Tanguy',                            '1110323544846', 'M', '16/12/2011', 'Bantè',                '5e'],
        ['VODONOU',         'Anne-Marie Sica Francisca',                        '2130323635729', 'F', '08/09/2013', 'Godomey',              '5e'],
        ['YEHOUME',         'Grâce-Aimée Falonne Mahoussi',                     '2130323635730', 'F', '13/09/2013', 'Parakou',              '5e'],
        ['ZINSOUGA',        'Keyxnel Gerakhmeel Anihouvi',                      '1130323635731', 'M', '15/09/2013', 'Abomey-Calavi',        '5e'],
        ['ZONON',           'Sourou Patrick',                                   '1120323635732', 'M', '14/03/2012', 'Abomey',               '5e'],

        // ── 4e ──────────────────────────────────────────────────────────────
        ['ABAGLI',          'Daniella Chancelle',                               '2130322183623', 'F', '20/05/2013', 'Ab-Calavi',            '4e'],
        ['ADADJI',          'Sènami Geraldine',                                 '2110322183626', 'F', '03/10/2011', 'Cotonou',              '4e'],
        ['ADADJI',          'Vignissi Carine',                                  '2120322183624', 'F', '17/06/2012', 'Aïtchedji',            '4e'],
        ['ADADJI',          'Vignon Carlos',                                    '1120322183625', 'M', '17/06/2012', 'Aïtchedji',            '4e'],
        ['AMOUSSOU',        'Mohamed Owolabi',                                  '1100522090743', 'M', '28/02/2010', 'ISSATI',               '4e'],
        ['AYINON',          'Fifamè Bérékia',                                   '2120322183634', 'F', '22/07/2012', 'Aïtchedji',            '4e'],
        ['AZANDOSSESSI',    'Maxien Robert Bidossessi',                         '1120322183635', 'M', '30/04/2012', 'Ab-Calavi',            '4e'],
        ['BACHABI ALIDOU',  'Mouzâhir Kayodé Akindé',                           '1130322183636', 'M', '15/01/2013', 'Cotonou',              '4e'],
        ['BADE',            'Nadine Sena Elisabeth',                            '2120322183637', 'F', '08/11/2012', 'Ab-Calavi',            '4e'],
        ['BAH-AGBA',        'Imane Sylva',                                      '2120323033354', 'F', '05/11/2012', 'Ab-Calavi',            '4e'],
        ['BOCO',            'Isaac Gérard',                                     '111120340432',  'M', '03/10/2011', 'COTONOU',              '4e'],
        ['CLOGBEDA',        'Rodin-Mari Luigi Elie',                            '108120298878',  'M', '03/08/2008', 'SÈMÈ-PODJI',           '4e'],
        ['DADEHOU',         'Mahounan Adjibola Adalric Crédo',                  '112120340433',  'M', '06/06/2012', 'ABOMEY-CALAVI',        '4e'],
        ['EHO',             'Léa Gracia',                                       '207120252230',  'F', '26/10/2007', 'EKPÈ',                 '4e'],
        ['GOMENOU',         'Sèssimè Marie-Beraka Majorelle',                   '2120322183643', 'F', '22/12/2012', 'Ab-Calavi',            '4e'],
        ['HOUANGNI',        'Rayan Livan Ifede',                                '1130322183645', 'M', '28/04/2013', 'Cotonou',              '4e'],
        ['HOUEMABE',        'Yémè Mahoulomé Clotilde',                          '212120340440',  'F', '22/03/2012', 'COTONOU',              '4e'],
        ['KANGAN',          'Bricette Annie-Claude Foumilayo',                  '211120355016',  'F', '27/02/2011', 'ABOMEY-CALAVI',        '4e'],
        ['KINDE',           'Mahutondji Hermann',                               '1110322183646', 'M', '26/09/2011', 'Ab-Calavi',            '4e'],
        ['KOHLA',           'Leslie Anamaria Rolande',                          '2101023011556', 'F', '14/12/2010', 'Ekpè',                 '4e'],
        ['KPONON',          'Dotou Grâce',                                      '2120323050607', 'F', '02/07/2012', 'Ab-calavi',            '4e'],
        ['SABI YERIMA',     'Kadjogbé Wérabassi',                               '1120323036846', 'M', '04/09/2012', 'Natitingou',           '4e'],
        ['TOSSOUKPE',       'Hermione Ginette Calfridath',                      '2110322183653', 'F', '04/10/2011', 'Ab-Calavi',            '4e'],
        ['VODOUNSI',        'Sosthene',                                         '1100322183654', 'M', '27/11/2010', 'Ab-Calavi',            '4e'],

        // ── 3e ──────────────────────────────────────────────────────────────
        ['ABAGLI',          'Jesus Kpégo Ezékiel Salem',                        '111120340426',  'M', '10/02/2011', 'COTONOU',              '3e'],
        ['ADANDE',          'Carelle Divine Yabo',                              '212120340427',  'F', '07/04/2012', 'COTONOU',              '3e'],
        ['AHAMIDE',         'Omraam Vital',                                     '112120350633',  'M', '12/03/2012', 'ABOMEY-CALAVI',        '3e'],
        ['AHOUANMAGNAGAHOU','Hermione',                                         '210120339026',  'F', '15/05/2010', 'ABOMEY',               '3e'],
        ['ASSOGBA',         'Gracia Mariella',                                  '210120271837',  'F', '13/04/2010', 'COTONOU',              '3e'],
        ['AZONDEKON',       'Ishémie Christiana Samira',                        '212120363290',  'F', '02/07/2012', 'ABOMEY-CALAVI',        '3e'],
        ['BADE',            'Sessi Emmanuella Espérancia',                      '211120340430',  'F', '28/12/2011', 'COTONOU',              '3e'],
        ['BAH L\'IMAM MOUSSA','Falidathou',                                     '210030062801',  'F', '02/03/2010', 'DJOUGOU',              '3e'],
        ['BAH-L\'IMAM',     'Faouziath',                                        '210120340431',  'F', '29/11/2010', 'COTONOU',              '3e'],
        ['DETONDJI',        'Fierté Mahoukpégo Anaïs Osciliatrice',             '212120340434',  'F', '21/05/2012', 'ABOMEY',               '3e'],
        ['DIDAGBE',         'Minikpamahou Alex',                                '111120340435',  'M', '22/04/2011', 'COTONOU',              '3e'],
        ['DOUKPO',          'Frey Abel Michel Noudjiou',                        '109120304693',  'M', '13/11/2009', 'ALLADA',               '3e'],
        ['EDAYE',           'Yanëlle Fifamè Divine',                            '212120340436',  'F', '23/06/2012', 'GODOMEY',              '3e'],
        ['GNADEKPA',        'Codjo Clément Emmanuel',                           '110120341504',  'M', '22/03/2010', 'COME',                 '3e'],
        ['GNAHA',           'Doudédji Marjonelle Joanècia',                     '212120359193',  'F', '17/06/2012', 'ABOMEY-CALAVI',        '3e'],
        ['GNANVI',          'Sylvain Thonangnon',                               '106120340437',  'M', '05/11/2006', 'OUESSE',               '3e'],
        ['GOUSSANOU',       'Deyon Gidéon Mirock',                              '111120348001',  'M', '01/07/2011', 'COTONOU',              '3e'],
        ['HOUNGUEVOU',      'Loufaz Ange Mahougnon',                            '111120315400',  'M', '03/02/2011', 'LOBOZOUNKPA',          '3e'],
        ['KOUNDE',          'Nongnikpo Merveille Immaculée',                    '210120306708',  'F', '27/08/2010', 'ABOMEY-CALAVI',        '3e'],
        ['LOKONON',         'Marie-Anne Fifamè',                                '211120340442',  'F', '28/05/2011', 'COTONOU',              '3e'],
        ['MOUTAÏROU',       'Samir Alabi Boladji',                              '112120340443',  'M', '04/08/2012', 'PORTO-NOVO',           '3e'],
        ['SENAKPON',        'Charlemagne Juste Sèna',                           '110120315363',  'M', '09/05/2010', 'COTONOU',              '3e'],
        ['VODOUNSSI',       'Fênou Stéphanas',                                  '108120291360',  'M', '14/04/2008', 'ABOMEY-CALAVI',        '3e'],

        // ── 2nde D ───────────────────────────────────────────────────────────
        ['ADAMA',           'Mariam Asakè',                                     '209120305009',  'F', '29/06/2009', 'SAKI NIGERIA',         '2nde D'],
        ['ANAGOSSI',        'Inès',                                             '209120305011',  'F', '10/09/2009', 'ABOMEY-CALAVI',        '2nde D'],
        ['DENOU',           'Barnay Nathan Vangelis',                           '109120328425',  'M', '03/11/2009', 'Cotonou',              '2nde D'],
        ['DJOSSOU',         'Gbètovivi Aubin',                                  '110120305017',  'M', '01/03/2010', 'COTONOU',              '2nde D'],
        ['DONOU',           'Fréjus Kévin Tolidji',                             '109120305018',  'M', '29/12/2009', 'ABOMEY-CALAVI',        '2nde D'],
        ['DOSSEH',          'Odilon Kéneth',                                    '111120306707',  'M', '04/01/2011', 'ABOMEY-CALAVI',        '2nde D'],
        ['GANKPAN',         'Sylvia Ruth',                                      '210010144692',  'F', '31/10/2010', 'ABOMEY-CALAVI',        '2nde D'],
        ['GANKPAN',         'Sylvie Marthe',                                    '210010144693',  'F', '31/10/2010', 'ABOMEY-CALAVI',        '2nde D'],
        ['GOMENOU',         'Sègnon Sévérine Grâce',                            '210120305021',  'F', '27/11/2010', 'ABOMEY-CALAVI',        '2nde D'],
        ['GOUDOU',          'Romano Floris Sèdjro',                             '111120305022',  'M', '13/04/2011', 'COTONOU',              '2nde D'],
        ['HIDJO',           'Mahuna Yanis Marc-Antoine',                        '110120274661',  'M', '13/04/2010', 'COTONOU',              '2nde D'],
        ['KLICO',           'Destinée Prémine',                                 '211120305025',  'F', '08/05/2011', 'ABOMEY-CALAVI',        '2nde D'],
        ['KOHONOU',         'Mahouna Désiré David',                             '108120212620',  'M', '15/03/2008', 'Cotonou',              '2nde D'],
        ['KPAKPO',          'Fleurette Amatsia Adoukouwê',                      '210120305026',  'F', '25/07/2010', 'AÏTCHEDJI',            '2nde D'],
        ['LIONFIN',         'Amado Cléophace Dieudonné Sènan',                  '108120305028',  'M', '26/11/2008', 'COTONOU',              '2nde D'],
        ['ODJO',            'Rhonel Samuel Oluwatobi',                          '110120305029',  'M', '14/08/2010', 'COTONOU',              '2nde D'],

        // ── 1ère D ───────────────────────────────────────────────────────────
        ['ADOUNGBE',        'Ashley Maéva',                                     '210120277365',  'F', '22/02/2010', 'ETATS-UNIS',           '1ere D'],
        ['AWASSI',          'Béni Houéfa',                                      '209120277368',  'F', '20/06/2009', 'COTONOU',              '1ere D'],
        ['AYEDOUN',         'Olagninka Grâcia Merveille',                       '208120277369',  'F', '02/02/2008', 'ABOMEY-CALAVI',        '1ere D'],
        ['AYINON',          'Sèwanou Précieux',                                 '108120138322',  'M', '08/12/2008', 'Abomey-Calavi',        '1ere D'],
        ['BADE',            'Micrète Fifamè Célia',                             '210120277372',  'F', '19/05/2010', 'COTONOU',              '1ere D'],
        ['CHINCOUN',        'Ifè Mauraine Ahouéfa',                             '210120271790',  'F', '18/11/2010', 'COTONOU',              '1ere D'],
        ['FAGNIBO',         'Phil-Terry Dègnon Adéola',                         '109120283258',  'M', '19/07/2009', 'COTONOU',              '1ere D'],
        ['FASSINOU',        'Elcy Nonvimigni Pladia',                           '208120288232',  'F', '10/11/2008', 'COTONOU',              '1ere D'],
        ['KOHONOU',         'Houéfa Blanche Eunice',                            '209120212621',  'F', '31/12/2009', 'COTONOU',              '1ere D'],
        ['SEHA',            'Yabo Ayiyéton Lumière',                            '207120277380',  'F', '04/10/2007', 'ABOMEY-CALAVI',        '1ere D'],
        ['TCHINKOUN',       'Andy Naasson Vidékon',                             '110120285198',  'M', '25/01/2010', 'COTONOU',              '1ere D'],
        ['VODOUNON',        'Cheryl Chatialla Fèmi Elvira',                     '209120277381',  'F', '27/11/2009', 'ABOMEY-CALAVI',        '1ere D'],

        // ── 1ère B ───────────────────────────────────────────────────────────
        ['GOUNDE',          'Edgar',                                            '107060088996',  'M', '08/07/2007', 'ITCHOCOBO',            '1ere B'],

        // ── Tle D ────────────────────────────────────────────────────────────
        ['ADANHOUME',       'Moriola Yao Chabel Précieux',                      '107110089359',  'M', '17/05/2007', 'Cotonou',              'Tle D'],
        ['DEGAN',           'Grâce Marie-Flora',                                '209120229685',  'F', '24/11/2009', 'COTONOU',              'Tle D'],
        ['GNANVI',          'Sèdami Lucrèce',                                   '208120191961',  'F', '01/04/2008', 'Cotonou',              'Tle D'],
        ['GOULOLE',         'Ornélia Lauren Tania Olagnikè',                    '209070049368',  'F', '13/09/2009', 'Abomey-Calavi',        'Tle D'],
        ['KINDE',           'Marie-Alphonsine Mahuclo',                         '208120007507',  'F', '30/07/2008', 'Abomey-Calavi',        'Tle D'],
        ['MEKOUN',          'Prince Junior',                                    '109120172140',  'M', '08/01/2009', 'Cotonou',              'Tle D'],
        ['SABI YERIMA',     'Ifèdé Bossima',                                    '109120132634',  'M', '15/09/2009', 'Natitingou',           'Tle D'],

        // ── Tle B ────────────────────────────────────────────────────────────
        ['AYENON',          'Ezinwé Reine',                                     '299060008735',  'F', '08/09/1999', 'GOUKA',                'Tle B'],
        ['DENOU',           'Jovana Maendelle Phédora',                         '208090160575',  'F', '27/03/2008', 'Cotonou',              'Tle B'],
        ['KOBA',            'Kodoumou Jeanne',                                  '206060076438',  'F', '10/12/2002', 'DASSA',                'Tle B'],
        ['KOHONOU',         'Josué',                                            '105120209502',  'M', '31/12/2005', 'Cotonou',              'Tle B'],
    ];

    // ─────────────────────────────────────────────
    //  ÉTAT
    // ─────────────────────────────────────────────
    private int   $updated   = 0;
    private int   $skipped   = 0;
    private array $failures  = [];
    private array $log       = [];
    private bool  $dryRun    = false;
    private bool  $verboseLog = false;

    // ─────────────────────────────────────────────
    //  HANDLE
    // ─────────────────────────────────────────────
    public function handle(): int
    {
        $this->dryRun     = (bool) $this->option('dry-run');
        $this->verboseLog = (bool) $this->option('verbose-log');

        $this->printBanner();

        if ($this->dryRun) {
            $this->warn('⚠️  MODE DRY-RUN activé — aucune modification en base de données');
        }

        // Charger tous les étudiants une seule fois (optimisation)
        $allStudents = Student::select('id', 'last_name', 'first_name', 'birth_date', 'birth_place', 'num_educ', 'gender')
            ->get();

        $this->info(sprintf("\n📋 %d élèves chargés depuis la base de données", $allStudents->count()));
        $this->info(sprintf("📋 %d lignes dans les PDFs à traiter\n", count($this->pdfData)));

        $bar = $this->output->createProgressBar(count($this->pdfData));
        $bar->start();

        foreach ($this->pdfData as $row) {
            [$pdfLastName, $pdfFirstNames, $pdfMatricule, $pdfGender, $pdfBirthDate, $pdfBirthPlace, $pdfClasse] = $row;

            $result = $this->processRow(
                $allStudents,
                $pdfLastName,
                $pdfFirstNames,
                $pdfMatricule,
                $pdfGender,
                $pdfBirthDate,
                $pdfBirthPlace,
                $pdfClasse
            );

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->printSummary();

        return self::SUCCESS;
    }

    // ─────────────────────────────────────────────
    //  TRAITEMENT D'UNE LIGNE PDF
    // ─────────────────────────────────────────────
    private function processRow(
        $allStudents,
        string $pdfLastName,
        string $pdfFirstNames,
        string $pdfMatricule,
        string $pdfGender,
        string $pdfBirthDateStr,
        string $pdfBirthPlace,
        string $pdfClasse
    ): void {
        $context = "[{$pdfClasse}] {$pdfLastName} {$pdfFirstNames}";

        // ── 1. Parser la date de naissance du PDF ──
        try {
            $pdfBirthDate = Carbon::createFromFormat('d/m/Y', $pdfBirthDateStr);
        } catch (\Exception $e) {
            $this->recordFailure($context, "Date de naissance invalide dans PDF: {$pdfBirthDateStr}");
            return;
        }

        // ── 2. Normaliser le nom de famille PDF ──
        $normalizedPdfLastName = $this->normalizeName($pdfLastName);
        $pdfFirstNameList      = $this->splitFirstNames($pdfFirstNames);

        // ── 3. Chercher les candidats par nom de famille ──
        $candidatesByName = $allStudents->filter(function ($s) use ($normalizedPdfLastName) {
            return $this->normalizeName($s->last_name) === $normalizedPdfLastName;
        });

        if ($candidatesByName->isEmpty()) {
            $this->recordFailure($context, "Aucun élève trouvé avec le nom de famille « {$pdfLastName} » en base");
            return;
        }

        // ── 4. Raffiner par date de naissance (critère le plus fiable) ──
        $matchByDate = $candidatesByName->filter(function ($s) use ($pdfBirthDate) {
            if (!$s->birth_date) return false;
            try {
                $dbDate = Carbon::parse($s->birth_date);
                return $dbDate->format('Y-m-d') === $pdfBirthDate->format('Y-m-d');
            } catch (\Exception $e) {
                return false;
            }
        });

        $candidate = null;
        $matchMethod = '';

        if ($matchByDate->count() === 1) {
            // Correspondance parfaite nom + date
            $candidate   = $matchByDate->first();
            $matchMethod = 'nom + date_naissance';
        } elseif ($matchByDate->count() > 1) {
            // Plusieurs personnes avec même nom ET même date (fratrie identique ?)
            // On tente de résoudre avec les prénoms
            $candidate   = $this->resolveByFirstName($matchByDate, $pdfFirstNameList);
            $matchMethod = 'nom + date_naissance + prénom';

            if (!$candidate) {
                $this->recordFailure(
                    $context,
                    "Ambiguïté : {$matchByDate->count()} élèves avec le nom « {$pdfLastName} » et la même date {$pdfBirthDateStr}"
                );
                return;
            }
        } else {
            // Pas de match sur la date → on essaie nom + prénom
            $candidate   = $this->resolveByFirstName($candidatesByName, $pdfFirstNameList);
            $matchMethod = 'nom + prénom';

            if (!$candidate) {
                // Dernier recours : nom + lieu de naissance (si candidat unique)
                $matchByPlace = $candidatesByName->filter(function ($s) use ($pdfBirthPlace) {
                    return $s->birth_place
                        && $this->normalizeName($s->birth_place) === $this->normalizeName($pdfBirthPlace);
                });

                if ($matchByPlace->count() === 1) {
                    $candidate   = $matchByPlace->first();
                    $matchMethod = 'nom + lieu_naissance';
                } else {
                    $this->recordFailure(
                        $context,
                        "Impossible de trouver un candidat unique pour « {$pdfLastName} {$pdfFirstNames} » (date={$pdfBirthDateStr}, lieu={$pdfBirthPlace})"
                    );
                    return;
                }
            }
        }

        // ── 5. Vérification obligatoire : au moins un prénom en commun ──
        if (!$this->hasAtLeastOneCommonFirstName($candidate->first_name, $pdfFirstNameList)) {
            $this->recordFailure(
                $context,
                "SÉCURITÉ : aucun prénom commun entre « {$candidate->first_name} » (base) "
                . "et « {$pdfFirstNames} » (PDF) pour l'élève ID {$candidate->id} — mise à jour refusée"
            );
            return;
        }

        // ── 6. Vérifier si une mise à jour est nécessaire ──
        $needsNumEduc    = trim($candidate->num_educ ?? '') !== trim($pdfMatricule);
        $needsFirstName  = $this->normalizeName($candidate->first_name) !== $this->normalizeName($pdfFirstNames);

        if (!$needsNumEduc && !$needsFirstName) {
            $this->skipped++;
            if ($this->verboseLog) {
                $this->log[] = "✔  SKIP  [{$matchMethod}] {$context} (déjà à jour)";
            }
            return;
        }

        // ── 7. Construire le diff pour l'affichage ──
        $changes = [];
        if ($needsNumEduc) {
            $changes[] = "num_educ: «{$candidate->num_educ}» → «{$pdfMatricule}»";
        }
        if ($needsFirstName) {
            $changes[] = "first_name: «{$candidate->first_name}» → «{$pdfFirstNames}»";
        }

        // ── 8. Appliquer la mise à jour ──
        if (!$this->dryRun) {
            try {
                DB::transaction(function () use ($candidate, $pdfMatricule, $pdfFirstNames, $needsNumEduc, $needsFirstName) {
                    $updateData = [];
                    if ($needsNumEduc)   $updateData['num_educ']    = $pdfMatricule;
                    if ($needsFirstName) $updateData['first_name']  = $pdfFirstNames;

                    Student::where('id', $candidate->id)->update($updateData);
                });
            } catch (\Exception $e) {
                $this->recordFailure($context, "Erreur DB lors de la mise à jour ID {$candidate->id}: " . $e->getMessage());
                Log::error("UpdateStudentsFromPdf: {$context} — " . $e->getMessage());
                return;
            }
        }

        $this->updated++;
        $prefix = $this->dryRun ? '🔵 DRY  ' : '✅ MÀJ  ';
        $this->log[] = "{$prefix}[{$matchMethod}] {$context} — " . implode(' | ', $changes);

        if ($this->verboseLog) {
            $this->line("  {$prefix}{$context} — " . implode(' | ', $changes));
        }
    }

    // ─────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────

    /**
     * Résoudre l'ambiguïté en cherchant un candidat qui partage
     * AU MOINS UN prénom avec les prénoms du PDF.
     * Retourne null si 0 ou plusieurs candidats correspondent.
     */
    private function resolveByFirstName($candidates, array $pdfFirstNameList): ?Student
    {
        $matched = $candidates->filter(function ($s) use ($pdfFirstNameList) {
            return $this->hasAtLeastOneCommonFirstName($s->first_name, $pdfFirstNameList);
        });

        return $matched->count() === 1 ? $matched->first() : null;
    }

    /**
     * Vérifie qu'au moins un prénom du PDF se retrouve dans le prénom en base (ou vice-versa).
     * La comparaison est insensible à la casse et aux accents.
     */
    private function hasAtLeastOneCommonFirstName(string $dbFirstName, array $pdfFirstNameList): bool
    {
        $dbNames = $this->splitFirstNames($dbFirstName);

        foreach ($pdfFirstNameList as $pdfName) {
            $normalizedPdf = $this->normalizeName($pdfName);
            foreach ($dbNames as $dbName) {
                if ($this->normalizeName($dbName) === $normalizedPdf) {
                    return true;
                }
                // Correspondance partielle (au moins 4 caractères, pour "Sènami" ≈ "Senami")
                if (strlen($normalizedPdf) >= 4 && str_contains($this->normalizeName($dbName), $normalizedPdf)) {
                    return true;
                }
                if (strlen($this->normalizeName($dbName)) >= 4 && str_contains($normalizedPdf, $this->normalizeName($dbName))) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Découpe une chaîne de prénoms en tableau de prénoms individuels.
     */
    private function splitFirstNames(string $names): array
    {
        return array_filter(
            array_map('trim', preg_split('/[\s\-]+/', $names)),
            fn($n) => strlen($n) >= 2
        );
    }

    /**
     * Normalise une chaîne : minuscules, sans accents, sans ponctuation superflue.
     */
    private function normalizeName(string $name): string
    {
        $name = mb_strtolower(trim($name), 'UTF-8');

        // Translitération des caractères accentués courants (français + béninois)
        $search  = ['à','â','ä','á','ã','å','æ','ç','è','é','ê','ë','ì','í','î','ï',
                    'ñ','ò','ó','ô','ö','õ','ø','ù','ú','û','ü','ý','ÿ',
                    'ğ','ş','ı','ε','ε',
                    'à','â','ä','á','ã','å','æ','ç','è','é','ê','ë','ì','í','î','ï',
                    'ñ','ò','ó','ô','ö','õ','ø','ù','ú','û','ü','ý','ÿ'];
        $replace = ['a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i',
                    'n','o','o','o','o','o','o','u','u','u','u','y','y',
                    'g','s','i','e','e',
                    'a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i',
                    'n','o','o','o','o','o','o','u','u','u','u','y','y'];

        $name = str_replace($search, $replace, $name);

        // Supprimer les apostrophes et tirets (pour "BAH-L'IMAM" → "bahlimam")
        $name = preg_replace("/['\-]/", '', $name);

        // Supprimer les espaces multiples
        $name = preg_replace('/\s+/', ' ', $name);

        return trim($name);
    }

    private function recordFailure(string $context, string $reason): void
    {
        $this->failures[] = ['context' => $context, 'reason' => $reason];
        Log::warning("UpdateStudentsFromPdf FAILURE: {$context} — {$reason}");
    }

    // ─────────────────────────────────────────────
    //  AFFICHAGE
    // ─────────────────────────────────────────────
    private function printBanner(): void
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║   CPEG Marie-Alain — Mise à jour matricules & prénoms       ║');
        $this->info('║   Année scolaire 2025-2026                                  ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
    }

    private function printSummary(): void
    {
        // ── Log détaillé ──
        if (!empty($this->log)) {
            $this->info('─── Détail des mises à jour ───────────────────────────────────');
            foreach ($this->log as $line) {
                $this->line("  {$line}");
            }
            $this->newLine();
        }

        // ── Tableau récapitulatif ──
        $this->info('─── RÉCAPITULATIF ────────────────────────────────────────────');
        $this->table(
            ['Statut', 'Nombre'],
            [
                ['✅ Mis à jour'  , $this->updated],
                ['⏭  Déjà à jour' , $this->skipped],
                ['❌ Échecs'       , count($this->failures)],
                ['📋 Total PDF'   , count($this->pdfData)],
            ]
        );

        // ── Liste des échecs ──
        if (!empty($this->failures)) {
            $this->newLine();
            $this->error('─── LISTE DES ÉCHECS (' . count($this->failures) . ') ─────────────────────────────');
            $this->table(
                ['#', 'Élève (PDF)', 'Raison'],
                array_map(
                    fn($i, $f) => [$i + 1, $f['context'], $f['reason']],
                    array_keys($this->failures),
                    $this->failures
                )
            );

            // Sauvegarder les échecs dans un fichier
            $failurePath = storage_path('logs/update_students_failures_' . now()->format('Ymd_His') . '.json');
            file_put_contents($failurePath, json_encode($this->failures, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->warn("📄 Fichier d'échecs sauvegardé : {$failurePath}");
        } else {
            $this->info('🎉 Aucun échec !');
        }

        if ($this->dryRun) {
            $this->newLine();
            $this->warn('⚠️  DRY-RUN : aucune donnée n\'a été modifiée en base.');
            $this->warn('   Relancez sans --dry-run pour appliquer les changements.');
        }
    }
}