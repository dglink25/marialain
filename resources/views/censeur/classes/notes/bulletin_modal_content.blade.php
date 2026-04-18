

<div class="bulletin-modal-wrapper font-sans text-xs text-black" style="font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4;">

    {{-- ===== EN-TÊTE ===== --}}
    <table style="width:100%; border-collapse:collapse; margin-bottom:10px;">
        <tr>
            <td style="width:40%; vertical-align:top; font-size:10px; border:none; padding:4px 0;">
                MINISTÈRE DES ENSEIGNEMENTS SECONDAIRE, TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE<br>
                <strong style="font-size:14px;">CS « MARIE-ALAIN »</strong><br>
                <small>AGORI AITCHEDJI - 08 BP : 559 Cotonou / Tél: 01 62 61 67 67</small>
            </td>
            <td style="width:20%; text-align:center; vertical-align:middle; border:none; padding:4px 0;">
                @if(file_exists(public_path('logo.png')))
                    <img src="{{ asset('logo.png') }}" style="width:70px; height:auto;">
                @else
                    <div style="width:70px; height:70px; border:1px solid #ccc; display:inline-flex; align-items:center; justify-content:center; font-size:9px; color:#666;">LOGO</div>
                @endif
            </td>
            <td style="width:40%; text-align:right; vertical-align:top; font-size:10px; border:none; padding:4px 0;">
                REPUBLIQUE DU BÉNIN<br>
                Année scolaire : <strong>{{ $activeYear->name }}</strong><br>
                Trimestre : <strong>{{ $trimestre }}</strong><br>
                Classe : <strong>{{ $classe->name }}</strong><br>
                Effectif : <strong>{{ $classe->students->count() }}</strong>
            </td>
        </tr>
    </table>

    {{-- Titre centré --}}
    <div style="text-align:center; font-size:16px; font-weight:bold; text-decoration:underline; margin:10px 0 15px;">
        BULLETIN DE NOTES
    </div>

    {{-- ===== INFORMATIONS ÉLÈVE ===== --}}
    <div style="margin-bottom:12px; overflow:hidden;">
        <div style="float:left; width:75%;">
            Nom : <strong>{{ strtoupper($student->last_name) }}</strong> &nbsp;&nbsp;
            Prénoms : <strong>{{ $student->first_name }}</strong><br>
            N° Matricule : {{ $student->registration_number ?? '—' }} &nbsp;&nbsp;
            Sexe : {{ $student->gender == 'M' ? 'Masculin' : 'Féminin' }}
        </div>
        <div style="float:right; width:80px; text-align:center;">
            @if(file_exists(public_path('qrcode.png')))
                <img src="{{ asset('qrcode.png') }}" style="width:60px; height:60px; border:1px solid #ccc;">
            @endif
        </div>
        <div style="clear:both;"></div>
    </div>

    {{-- ===== TABLEAU DES NOTES ===== --}}
    <table style="width:100%; border-collapse:collapse; margin-top:8px; font-size:10px;">
        <thead>
            <tr style="background-color:#f2f2f2;">
                <th rowspan="2" style="border:1px solid #000; padding:4px; text-align:left; min-width:160px;">Matières</th>
                <th rowspan="2" style="border:1px solid #000; padding:4px; text-align:center; width:35px;">Coef</th>
                <th colspan="3" style="border:1px solid #000; padding:4px; text-align:center;">Notes de Classe</th>
                <th rowspan="2" style="border:1px solid #000; padding:4px; text-align:center; width:55px;">Moy. /20</th>
                <th rowspan="2" style="border:1px solid #000; padding:4px; text-align:center; width:50px;">Note Coef.</th>
                <th rowspan="2" style="border:1px solid #000; padding:4px; text-align:center; width:40px;">Rang</th>
                <th rowspan="2" style="border:1px solid #000; padding:4px; text-align:center;">Appréciations</th>
            </tr>
            <tr style="background-color:#f2f2f2;">
                <th style="border:1px solid #000; padding:4px; text-align:center; width:60px; font-size:9px;">Moy. Interro</th>
                <th style="border:1px solid #000; padding:4px; text-align:center; width:55px; font-size:9px;">Devoir N°1</th>
                <th style="border:1px solid #000; padding:4px; text-align:center; width:55px; font-size:9px;">Devoir N°2</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bulletin as $row)
            <tr style="{{ $loop->even ? 'background-color:#fafafa;' : '' }}">
                <td style="border:1px solid #000; padding:3px 5px; font-weight:bold; text-align:left;">{{ $row['subject'] }}</td>
                <td style="border:1px solid #000; padding:3px; text-align:center;">{{ $row['coef'] }}</td>
                <td style="border:1px solid #000; padding:3px; text-align:center;">{{ $row['moyenneInterro'] }}</td>
                <td style="border:1px solid #000; padding:3px; text-align:center;">{{ $row['devoirs'][1] }}</td>
                <td style="border:1px solid #000; padding:3px; text-align:center;">{{ $row['devoirs'][2] }}</td>
                <td style="border:1px solid #000; padding:3px; text-align:center; font-weight:bold;
                    {{ is_numeric(str_replace(',', '.', $row['moyenne'])) && str_replace(',', '.', $row['moyenne']) < 10 ? 'color:#c0392b;' : 'color:#27ae60;' }}">
                    {{ $row['moyenne'] }}
                </td>
                <td style="border:1px solid #000; padding:3px; text-align:center;">{{ $row['moyCoeff'] }}</td>
                <td style="border:1px solid #000; padding:3px; text-align:center; font-size:9px;">{{ $row['rang'] ?? '—' }}</td>
                <td style="border:1px solid #000; padding:3px; text-align:center; font-size:9px;">{{ $row['appreciation'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight:bold; background:#eee;">
                <td style="border:1px solid #000; padding:3px 5px;" colspan="1">Totaux : {{ $totalCoeff }}</td>
                <td style="border:1px solid #000; padding:3px; text-align:center;">{{ $totalCoeff }}</td>
                <td colspan="4" style="border:1px solid #000; padding:3px;"></td>
                <td style="border:1px solid #000; padding:3px; text-align:center; font-weight:bold;">{{ $totalMoyCoeff }}</td>
                <td colspan="2" style="border:1px solid #000; padding:3px;"></td>
            </tr>
        </tfoot>
    </table>

    {{-- ===== MOYENNES PAR DOMAINE ===== --}}
    <div style="display:table; width:100%; margin:6px 0; font-size:9px; border-bottom:1px dashed #000; padding-bottom:5px;">
        <div style="display:table-cell; padding:3px 5px;">
            Moy. Matières Littéraires : <strong>{{ $moyenneLitteraire }}</strong>
        </div>
        <div style="display:table-cell; padding:3px 5px; text-align:center;">
            Moy. Matières Scientifiques : <strong>{{ $moyenneScientifique }}</strong>
        </div>
        <div style="display:table-cell; padding:3px 5px; text-align:right;">
            Moy. Autres Matières : <strong>{{ $moyenneAutres }}</strong>
        </div>
    </div>

    {{-- ===== BLOCS RÉSUMÉ ===== --}}
    <div style="display:table; width:100%; margin-top:12px; border-collapse:separate; border-spacing:4px;">
        {{-- Résultat apprenant --}}
        <div style="display:table-cell; width:32%; vertical-align:top; border:1px solid #000;">
            <div style="background:#e0e0e0; font-weight:bold; text-align:center; padding:4px; border-bottom:1px solid #000; font-size:10px;">
                Résultat de l'apprenant
            </div>
            <div style="padding:6px 8px; line-height:1.7; font-size:10px;">
                Moyenne : <strong style="font-size:13px; {{ str_replace(',', '.', $moyenneGenerale) >= 10 ? 'color:#27ae60;' : 'color:#c0392b;' }}">{{ $moyenneGenerale }}</strong> / 20<br>
                Rang : <strong>{{ $rang }}</strong> sur {{ $classe->students->count() }}<br>
                Mention : <strong>{{ $appreciationGenerale }}</strong>
            </div>
        </div>

        {{-- Spacer --}}
        <div style="display:table-cell; width:2%;"></div>

        {{-- Résultat de la classe --}}
        <div style="display:table-cell; width:32%; vertical-align:top; border:1px solid #000;">
            <div style="background:#e0e0e0; font-weight:bold; text-align:center; padding:4px; border-bottom:1px solid #000; font-size:10px;">
                Résultat de la classe
            </div>
            <div style="padding:6px 8px; line-height:1.7; font-size:10px;">
                Plus forte moyenne : <strong>{{ $plusForte }}</strong><br>
                Plus faible moyenne : <strong>{{ $plusFaible }}</strong><br>
                Moyenne de la classe : <strong>{{ $moyClasse }}</strong>
            </div>
        </div>

        {{-- Spacer --}}
        <div style="display:table-cell; width:2%;"></div>

        {{-- Décision du conseil --}}
        <div style="display:table-cell; width:32%; vertical-align:top; border:1px solid #000;">
            <div style="background:#e0e0e0; font-weight:bold; text-align:center; padding:4px; border-bottom:1px solid #000; font-size:10px;">
                Décision du Conseil
            </div>
            <div style="padding:6px 8px; line-height:1.8; font-size:10px;">
                {{ $felicitation ? '[✓]' : '[&nbsp;&nbsp;]' }} Félicitations<br>
                {{ $encouragement ? '[✓]' : '[&nbsp;&nbsp;]' }} Encouragement<br>
                {{ $tableauHonneur ? '[✓]' : '[&nbsp;&nbsp;]' }} Tableau d'Honneur<br>
                {{ $avertissement ? '[✓]' : '[&nbsp;&nbsp;]' }} Avertissement
            </div>
        </div>
    </div>

    {{-- ===== SIGNATURES ===== --}}
    <table style="width:100%; border-collapse:collapse; margin-top:25px; border:none;">
        <tr>
            <td style="width:50%; text-align:center; border:none; padding:8px;">
                <u><strong>Le Titulaire</strong></u><br>
                <div style="display:inline-block; border:2px solid #000; padding:6px 14px; font-weight:bold; margin-top:10px; font-size:11px;">
                    {{ $appreciationGenerale }}
                </div>
            </td>
            <td style="width:50%; text-align:center; border:none; padding:8px;">
                <u><strong>Le Directeur</strong></u><br>
                <br><br><br><br>
                <strong>Firmin DIDAGBE</strong>
            </td>
        </tr>
    </table>

    {{-- Devise --}}
    <div style="text-align:center; margin-top:20px; font-style:italic; border-top:1px solid #000; padding-top:6px; font-size:10px;">
        Discipline &mdash; Créativité &mdash; Excellence
    </div>
</div>