@php $d = $data; @endphp

<div class="bulletin-page">

    {{-- En-tête --}}
    <table class="header-table">
        <tr>
            <td class="header-left">
                MINISTERE DES ENSEIGNEMENTS SECONDAIRE, TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE<br>
                <strong class="school-name">CS &laquo; MARIE-ALAIN &raquo;</strong><br>
                <small>AGORI AITCHEDJI - 08 BP : 559 Cotonou / T&eacute;l: 01 62 61 67 67</small>
            </td>
            <td class="header-center">
                <img src="{{ asset('logo.png') }}" class="logo" alt="Logo">
            </td>
            <td class="header-right">
                REPUBLIQUE DU BENIN<br>
                Ann&eacute;e scolaire : {{ $d['activeYear']->name }}<br>
                Classe : {{ $d['classe']->name }}<br>
                Effectif : {{ $d['classe']->students->count() }}
            </td>
        </tr>
    </table>

    <div class="title">BULLETIN DE FIN D'ANN&Eacute;E</div>
    <div class="subtitle">Les notes du tableau correspondent au Trimestre 3</div>

    {{-- Infos élève --}}
    <div class="info-section">
        <div class="student-box">
            Nom : <strong>{{ strtoupper($d['student']->last_name) }}</strong> &nbsp;&nbsp;
            Pr&eacute;noms : <strong>{{ $d['student']->first_name }}</strong><br>
            N&deg; Matricule : {{ $d['student']->num_educ ?? '&mdash;' }} &nbsp;&nbsp;
            Sexe : {{ $d['student']->gender == 'M' ? 'Masculin' : 'F&eacute;minin' }}
        </div>
        <div class="qr-box">
            <img src="{{ asset('qrcode.png') }}" class="qr-code" alt="QR">
        </div>
        <div class="clear"></div>
    </div>

    {{-- Récapitulatif trimestriel --}}
    <table class="trimestre-band">
        <thead>
            <tr>
                <th colspan="2" class="t-label">Trimestre 1</th>
                <th colspan="2" class="t-label">Trimestre 2</th>
                <th colspan="2" class="t-label">Trimestre 3</th>
                <th colspan="2" class="t-ann">Moyenne Annuelle</th>
            </tr>
            <tr>
                <th>Moyenne</th><th>Rang</th>
                <th>Moyenne</th><th>Rang</th>
                <th>Moyenne</th><th>Rang</th>
                <th>Moy. Ann.</th><th>Rang Ann.</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>{{ $d['moyT1'] }}</strong></td>
                <td>{{ $d['rangT1'] }}</td>
                <td><strong>{{ $d['moyT2'] }}</strong></td>
                <td>{{ $d['rangT2'] }}</td>
                <td><strong>{{ $d['moyT3'] }}</strong></td>
                <td>{{ $d['rangT3'] }}</td>
                <td style="background:#e8f5e9;"><strong style="font-size:11px;">{{ $d['moyAnnuelle'] }}</strong></td>
                <td style="background:#e8f5e9;"><strong>{{ $d['rangAnnuel'] }}</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Tableau des notes T3 --}}
    <table class="notes">
        <thead>
            <tr>
                <th rowspan="2">Mati&egrave;res</th>
                <th rowspan="2">Coef</th>
                <th colspan="3">Notes de Classe &mdash; Trimestre 3</th>
                <th rowspan="2">Moy. /20</th>
                <th rowspan="2">Note Coef.</th>
                <th rowspan="2">Rang T3</th>
                <th rowspan="2">Appr&eacute;ciations</th>
            </tr>
            <tr>
                <th>Moy. Interro</th>
                <th>Devoir N&deg;1</th>
                <th>Devoir N&deg;2</th>
            </tr>
        </thead>
        <tbody>
            @foreach($d['bulletin'] as $row)
            <tr>
                <td class="subject-name">{{ $row['subject'] }}</td>
                <td>{{ $row['coef'] }}</td>
                <td>{{ $row['moyenneInterro'] }}</td>
                <td>{{ $row['devoirs'][1] }}</td>
                <td>{{ $row['devoirs'][2] }}</td>
                <td>{{ $row['moyenne'] }}</td>
                <td>{{ $row['moyCoeff'] }}</td>
                <td>{{ $row['rang'] ?? '-' }}</td>
                <td>{{ $row['appreciation'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight:bold; background:#eee;">
                <td>Totaux : {{ $d['totalCoeff'] }}</td>
                <td>{{ $d['totalCoeff'] }}</td>
                <td colspan="4"></td>
                <td>{{ $d['totalMoyCoeff'] }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    {{-- Domaines --}}
    <div class="domain-averages">
        <div class="domain-item">Moy. Litt&eacute;raires : <strong>{{ $d['moyenneLitteraire'] }}</strong></div>
        <div class="domain-item">Moy. Scientifiques : <strong>{{ $d['moyenneScientifique'] }}</strong></div>
        <div class="domain-item">Moy. Autres : <strong>{{ $d['moyenneAutres'] }}</strong></div>
    </div>

    {{-- Résumé 3 blocs --}}
    <div class="summary-wrapper">
        <div class="summary-box" style="margin-right:2%">
            <div class="summary-box-header">R&eacute;sultat de l&apos;apprenant</div>
            <div class="summary-content">
                Moy. Annuelle : <strong>{{ $d['moyAnnuelle'] }}</strong> / 20<br>
                Rang Annuel : <strong>{{ $d['rangAnnuel'] }}</strong><br>
                Moy. T3 : <strong>{{ $d['moyT3'] }}</strong> &mdash; Rang T3 : <strong>{{ $d['rangT3'] }}</strong><br>
                Mention : <strong>{{ $d['appreciationGenerale'] }}</strong>
            </div>
        </div>
        <div class="summary-box" style="margin-right:2%">
            <div class="summary-box-header">R&eacute;sultat de la classe (T3)</div>
            <div class="summary-content">
                Plus forte moyenne : {{ $d['plusForte'] }}<br>
                Plus faible moyenne : {{ $d['plusFaible'] }}<br>
                Moyenne de la classe : {{ $d['moyClasse'] }}
            </div>
        </div>
        <div class="summary-box">
            <div class="summary-box-header">D&eacute;cision du Conseil</div>
            <div class="summary-content">
                {{ $d['felicitation']   ? '[X]' : '[ ]' }} F&eacute;licitations<br>
                {{ $d['encouragement']  ? '[X]' : '[ ]' }} Encouragement<br>
                {{ $d['tableauHonneur'] ? '[X]' : '[ ]' }} Tableau d&apos;Honneur<br>
                {{ $d['avertissement']  ? '[X]' : '[ ]' }} Avertissement
            </div>
        </div>
    </div>

    {{-- Signatures --}}
    <table class="footer-table">
        <tr>
            <td style="width:50%; text-align:center; border:none;">
                <u><strong>Le Titulaire</strong></u><br><br>
                <div class="mention-frame">{{ $d['appreciationGenerale'] }}</div>
            </td>
            <td style="width:50%; text-align:center; border:none;">
                <u><strong>Le Directeur</strong></u><br>
                <br><br><br><br>
                <strong>Firmin DIDAGBE</strong>
            </td>
        </tr>
    </table>

    <div class="motto">Discipline &mdash; Cr&eacute;ativit&eacute; &mdash; Excellence</div>

</div>
