<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin Trimestre {{ $trimestre }}</title>
    <style>
        /* STYLES POUR FORMAT BULLETIN SCOLAIRE FRANÇAIS */
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000000;
            margin: 0;
            padding: 0;
            line-height: 1.1;
        }
        
        /* EN-TÊTE */
        .entete {
            width: 100%;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
        }
        
        .gauche {
            float: left;
            width: 45%;
            font-size: 9pt;
        }
        
        .droite {
            float: right;
            width: 45%;
            text-align: right;
            font-size: 9pt;
        }
        
        .centre {
            text-align: center;
            clear: both;
            padding-top: 5px;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            display: inline-block;
            vertical-align: middle;
        }
        
        .titre {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 5px 0;
            color: #000;
        }
        
        /* INFORMATIONS ÉLÈVE */
        .info-eleve {
            width: 100%;
            border: 1px solid #000;
            padding: 8px;
            margin: 10px 0;
            font-size: 9pt;
            background: #f8f9fa;
        }
        
        .info-colonne {
            width: 60%;
            float: left;
        }
        
        .trimestre-colonne {
            width: 20%;
            float: left;
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            padding-top: 15px;
        }
        
        .qrcode-colonne {
            width: 20%;
            float: right;
            text-align: center;
            border: 1px solid #000;
            padding: 3px;
            background: white;
        }
        
        /* TABLEAU DES NOTES */
        .tableau-notes {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 8pt;
            table-layout: fixed;
        }
        
        .tableau-notes th,
        .tableau-notes td {
            border: 1px solid #000;
            padding: 3px 1px;
            text-align: center;
            vertical-align: middle;
            height: 18px;
        }
        
        .tableau-notes th {
            background: #e9ecef;
            font-weight: bold;
        }
        
        /* Largeurs des colonnes selon format standard */
        .col-matiere { width: 22%; text-align: left; padding-left: 5px; }
        .col-coef { width: 5%; }
        .col-interro { width: 4%; }
        .col-moy-interro { width: 5%; }
        .col-devoir { width: 4.5%; }
        .col-moy { width: 5%; font-weight: bold; }
        .col-moy-coef { width: 6%; font-weight: bold; }
        .col-appreciation { width: 13%; text-align: left; padding-left: 5px; }
        
        /* TOTAUX ET MOYENNES */
        .totaux-section {
            width: 100%;
            margin: 8px 0;
            padding: 5px;
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
            border: 1px solid #000;
            background: #f8f9fa;
        }
        
        .totaux-ligne {
            display: flex;
            justify-content: space-around;
            margin: 3px 0;
        }
        
        .totaux-item {
            flex: 1;
            text-align: center;
            padding: 0 5px;
        }
        
        /* TROIS COLONNES INFÉRIEURES */
        .trois-colonnes {
            width: 100%;
            margin: 10px 0;
            display: table;
        }
        
        .colonne {
            display: table-cell;
            width: 33.33%;
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
        }
        
        .titre-colonne {
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
            text-decoration: underline;
            margin-bottom: 5px;
        }
        
        /* DÉCISIONS AVEC CASES */
        .decision-item {
            margin-bottom: 4px;
            padding-left: 20px;
            position: relative;
            font-size: 8pt;
        }
        
        .case-decision {
            position: absolute;
            left: 0;
            top: 0;
            width: 14px;
            height: 14px;
            border: 1px solid #000;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .case-decision.cochee {
            background: #28a745;
            color: white;
            font-weight: bold;
        }
        
        /* SIGNATURES */
        .signatures {
            width: 100%;
            margin: 15px 0;
            display: table;
        }
        
        .signature {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            font-size: 8pt;
        }
        
        .ligne-signature {
            border-bottom: 1px solid #000;
            width: 80%;
            height: 20px;
            margin: 5px auto;
        }
        
        /* PIED DE PAGE */
        .pied-page {
            width: 100%;
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px solid #000;
            font-size: 7pt;
            display: table;
        }
        
        .pied-gauche,
        .pied-centre,
        .pied-droite {
            display: table-cell;
            vertical-align: middle;
        }
        
        .pied-centre {
            text-align: center;
            font-style: italic;
        }
        
        .pied-droite {
            text-align: right;
        }
        
        .code-barres {
            border: 1px dashed #000;
            padding: 2px 6px;
            font-family: 'Courier New', monospace;
            font-size: 6pt;
            display: inline-block;
        }
        
        /* UTILITAIRES */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        
        .texte-gras {
            font-weight: bold;
        }
        
        .texte-centre {
            text-align: center;
        }
        
        .ligne-conduite {
            background: #fff3cd;
        }
        
        .ligne-totaux {
            background: #d4edda;
            font-weight: bold;
        }
        
        /* IMPRESSION */
        @media print {
            body {
                font-size: 10pt;
            }
            
            .tableau-notes {
                font-size: 7.5pt;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <!-- EN-TÊTE -->
    <div class="entete">
        <div class="gauche">
            <div class="texte-gras">Ministère des Enseignements Secondaires,</div>
            <div class="texte-gras">Techniques et de la Formation Professionnelle</div>
            <div style="margin-top: 5px; font-size: 8pt;">
                <div class="texte-gras">CS « MARIE-ALAIN »</div>
            </div>
        </div>
        
        <div class="droite">
            <div class="texte-gras">République du Bénin</div>
            <div>Fraternité - Justice - Travail</div>
            <hr style="border-top: 1px solid #000; margin: 3px 0; width: 80%; margin-left: auto;">
            <div>
                <span class="texte-gras">Année scolaire:</span> {{ $activeYear->name ?? '2025-2026' }}<br>
                <span class="texte-gras">Classe:</span> {{ $classe->name }}<br>
                <span class="texte-gras">Effectif:</span> {{ $classe->students->count() }}
            </div>
        </div>
        
        <div class="centre">
            <img src="{{ $logoPath ?? 'logo.png' }}" alt="Logo École" class="logo">
            <div class="titre">BULLETIN DE NOTES</div>
        </div>
    </div>

    <!-- INFORMATIONS ÉLÈVE -->
    <div class="info-eleve clearfix">
        <div class="info-colonne">
            <div><span class="texte-gras">Nom:</span> {{ strtoupper($student->last_name) }}</div>
            <div><span class="texte-gras">Prénom:</span> {{ ucfirst($student->first_name) }}</div>
            <div><span class="texte-gras">Matricule:</span> {{ $student->num_educ ?? '-' }}</div>
            <div><span class="texte-gras">Genre:</span> {{ $student->gender == 'M' ? 'Masculin' : 'Féminin' }}</div>
            <div><span class="texte-gras">Né(e) le:</span> {{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') : '-' }}</div>
            <div><span class="texte-gras">Classe:</span> {{ $classe->name }}</div>
        </div>
        
        <div class="trimestre-colonne">
            <div style="font-size: 12pt; margin-bottom: 5px;">{{ $trimestre }}</div>
            <div>Trimestre</div>
        </div>
        
        <div class="qrcode-colonne">
            <div style="height: 60px; display: flex; align-items: center; justify-content: center; color: #666; font-size: 7pt;">
                QR CODE<br>
                <span style="font-size: 6pt;">Bulletin ID: {{ $student->id }}-T{{ $trimestre }}</span>
            </div>
        </div>
    </div>

    <!-- TABLEAU DES NOTES -->
    <table class="tableau-notes">
        <thead>
            <tr>
                <th class="col-matiere" rowspan="2">Matière</th>
                <th class="col-coef" rowspan="2">Coef</th>
                <th colspan="5" style="background: #d1ecf1; border-bottom: 2px solid #000;">Interrogations</th>
                <th class="col-moy-interro" rowspan="2">Moy.I</th>
                <th colspan="2" style="background: #d1ecf1; border-bottom: 2px solid #000;">Devoirs</th>
                <th class="col-moy" rowspan="2">Moy</th>
                <th class="col-moy-coef" rowspan="2">Moy x Coef</th>
                <th class="col-appreciation" rowspan="2">Appréciation</th>
            </tr>
            <tr>
                <!-- Sous-colonnes pour les interrogations -->
                <th class="col-interro">I1</th>
                <th class="col-interro">I2</th>
                <th class="col-interro">I3</th>
                <th class="col-interro">I4</th>
                <th class="col-interro">I5</th>
                <!-- Sous-colonnes pour les devoirs -->
                <th class="col-devoir">D1</th>
                <th class="col-devoir">D2</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bulletin as $ligne)
            <tr>
                <td class="col-matiere">{{ $ligne['subject'] }}</td>
                <td class="col-coef">{{ $ligne['coef'] }}</td>
                @for ($i = 1; $i <= 5; $i++)
                <td class="col-interro">{{ $ligne['interros'][$i] ?? '-' }}</td>
                @endfor
                <td class="col-moy-interro">
                    @if($ligne['moyenneInterro'] !== null)
                        {{ number_format($ligne['moyenneInterro'], 1) }}
                    @else
                        -
                    @endif
                </td>
                <td class="col-devoir">{{ $ligne['devoirs'][1] ?? '-' }}</td>
                <td class="col-devoir">{{ $ligne['devoirs'][2] ?? '-' }}</td>
                <td class="col-moy">
                    @if($ligne['moyenne'] !== null)
                        {{ number_format($ligne['moyenne'], 2) }}
                    @else
                        -
                    @endif
                </td>
                <td class="col-moy-coef">
                    @if($ligne['moyCoeff'] > 0)
                        {{ number_format($ligne['moyCoeff'], 2) }}
                    @else
                        -
                    @endif
                </td>
                <td class="col-appreciation">{{ $ligne['appreciation'] ?? '-' }}</td>
            </tr>
            @endforeach
            
            <!-- LIGNE DES TOTAUX -->
            <tr class="ligne-totaux">
                <td colspan="10" style="text-align: right; padding-right: 10px;">
                    <center>Total Moy.Coef</center>
                </td>
                <td colspan="3" style="text-align: left; padding-left: 10px;">
                    <center>{{ number_format($totalMoyCoeff, 2) }}</center>
                </td>
            </tr>
            
            <!-- LIGNE DE LA CONDUITE -->
            <tr class="ligne-conduite">
                <td colspan="9" style="text-align: right; padding-right: 10px; font-weight: bold;">
                    <center>Conduite</center>
                </td>
                <td></td>
                <td class="col-moy">{{ $conduite ?? '-' }}/20</td>
                <td colspan="2">{{ $appreciationConduite ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- TROIS COLONNES INFÉRIEURES -->
    <div class="trois-colonnes">
        <!-- Colonne 1: Résultat de l'apprenant -->
        <div class="colonne">
            <div class="titre-colonne">Résultat de l'apprenant</div>
            <div style="margin-bottom: 4px;">
                <span style="font-weight: bold;">Moyenne générale : </span>
                {{ $moyenneGenerale ?? '-' }}/20
            </div>
            <div style="margin-bottom: 4px;">
                <span style="font-weight: bold;">Mention : </span>
                {{ $appreciationGenerale ?? '-' }}
            </div>
            <div style="margin-bottom: 4px;">
                <span style="font-weight: bold;">Rang : </span>
                {{ $rang }} sur {{ $classe->students->count() }}
            </div>
            <div>
                <span style="font-weight: bold;">Conduite : </span>
                {{ $conduite ?? '-' }}/20 ({{ $appreciationConduite ?? '-' }})
            </div>
        </div>
        
        <!-- Colonne 2: Résultat de la classe -->
        <div class="colonne">
            <div class="titre-colonne">Résultat de la classe</div>
            <div style="margin-bottom: 4px;">
                <span style="font-weight: bold;">Plus forte moyenne : </span>
                {{ $plusForte ?? '-' }}/20
            </div>
            <div style="margin-bottom: 4px;">
                <span style="font-weight: bold;">Plus faible moyenne : </span>
                {{ $plusFaible ?? '-' }}/20
            </div>
            <div style="margin-bottom: 4px;">
                <span style="font-weight: bold;">Moyenne de la classe : </span>
                {{ $moyClasse ?? '-' }}/20
            </div>
            <div>
                <span style="font-weight: bold;">Effectif : </span>
                {{ $classe->students->count() }} apprenants
            </div>
        </div>
        
        <!-- Colonne 3: Décision du Conseil -->
        <div class="colonne">
            <div class="titre-colonne">Décision du Conseil des Enseignants</div>
            
            <div class="decision-item">
                <div class="case-decision {{ $felicitation ? 'cochee' : '' }}">
                    {{ $felicitation ? : '' }}
                </div>
                Félicitation
            </div>
            
            <div class="decision-item">
                <div class="case-decision {{ $encouragement ? 'cochee' : '' }}">
                    {{ $encouragement ? : '' }}
                </div>
                Encouragement
            </div>
            
            <div class="decision-item">
                <div class="case-decision {{ $tableauHonneur ? 'cochee' : '' }}">
                    {{ $tableauHonneur ?  : '' }}
                </div>
                Tableau d'Honneur
            </div>
            
            <div class="decision-item">
                <div class="case-decision {{ $avertissement ? 'cochee' : '' }}">
                    {{ $avertissement ? : '' }}
                </div>
                Avertissement
            </div>
            
            @if(!$felicitation && !$encouragement && !$tableauHonneur && !$avertissement)
            <div style="margin-top: 10px; font-size: 7pt; text-align: center; font-style: italic; color: #666;">
                Aucune décision spécifique
            </div>
            @endif
        </div>
    </div>
    <br>
    <br>

    <!-- SIGNATURES -->
    <div class="signatures">
        <div class="signature">
            <div>Le Titulaire</div>
            <div class="ligne-signature"></div>
        </div>
      
        <div class="signature">
            <div>Le Censeur</div>
            <div class="ligne-signature"></div>
        </div>
        
        <div class="signature">
            <div>Le Chef d'Établissement</div>
            <div class="ligne-signature"></div>
        </div>
        <br><br><br>
    </div>

    <br>
    <br>
    <br><br>

    <!-- PIED DE PAGE -->
    <div class="pied-page">
        <div class="pied-gauche">
            <span class="code-barres">BULLETIN-{{ $student->num_educ ?? $student->id }}-T{{ $trimestre }}</span>
        </div>
        
        <div class="pied-centre">
            Discipline - Créativité - Excellence
        </div>
        
        <div class="pied-droite">
            Imprimé le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>
</body>
</html>