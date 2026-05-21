<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
@page { margin: 10mm 8mm; size: A4 landscape; }
body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 9px; color: #000; margin: 0; padding: 0; }

/* ── En-tête ── */
.header-table { width: 100%; border: none; margin-bottom: 8px; }
.header-left  { width: 40%; text-align: left; font-size: 8px; vertical-align: top; border: none; }
.header-center{ width: 20%; text-align: center; vertical-align: middle; border: none; }
.header-right { width: 40%; text-align: right; vertical-align: top; border: none; font-size: 8px; }
.logo { width: 60px; }
.school-name  { font-size: 14px; font-weight: bold; }
.page-title   { text-align: center; font-size: 15px; font-weight: bold; text-decoration: underline; margin: 6px 0 4px; }
.subject-line { text-align: center; font-size: 11px; font-weight: bold; margin-bottom: 8px; }

/* ── Stats bar ── */
.stats-bar { display: table; width: 100%; border-collapse: collapse; margin-bottom: 8px; }
.stat-cell {
    display: table-cell; padding: 5px 10px; border: 1px solid #c5cde0;
    background: #f0f4ff; text-align: center; font-size: 8px;
}
.stat-cell strong { font-size: 11px; display: block; }

/* ── Tableau principal ── */
table.notes-table { width: 100%; border-collapse: collapse; }
table.notes-table th {
    background: #1e40af; color: #fff; padding: 5px 4px;
    text-align: center; font-size: 8px; border: 1px solid #1e3a8a;
}
table.notes-table th.left { text-align: left; padding-left: 6px; }
table.notes-table td { padding: 4px; border: 1px solid #d1d5db; text-align: center; font-size: 8.5px; vertical-align: middle; }
table.notes-table td.left { text-align: left; padding-left: 6px; font-weight: 600; }
table.notes-table tr:nth-child(even) td { background: #f8faff; }
table.notes-table tfoot td {
    background: #1e3a8a; color: #fff; font-weight: bold; padding: 5px 4px;
}

/* ── Badge moyenne ── */
.moy { display: inline-block; padding: 1px 6px; border-radius: 10px; font-weight: 700; font-size: 8.5px; }
.moy-good { background: #d1fae5; color: #065f46; }
.moy-bad  { background: #fee2e2; color: #991b1b; }
.moy-null { background: #f1f5f9; color: #94a3b8; }

/* ── Footer ── */
.footer { text-align: center; margin-top: 20px; font-style: italic; font-size: 8px; border-top: 1px solid #000; padding-top: 4px; }
.signatures { display: table; width: 100%; margin-top: 16px; }
.sig-cell { display: table-cell; text-align: center; font-size: 8.5px; }
</style>
</head>
<body>

<!-- En-tête -->
<table class="header-table">
    <tr>
        <td class="header-left">
            MINISTERE DES ENSEIGNEMENTS SECONDAIRE, TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE<br>
            <span class="school-name">CS « MARIE-ALAIN »</span><br>
            <small>AGORI AITCHEDJI - 08 BP : 559 Cotonou / Tél: 01 62 61 67 67</small>
        </td>
        <td class="header-center">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('logo.png'))) }}" class="logo">
        </td>
        <td class="header-right">
            REPUBLIQUE DU BENIN<br>
            Année scolaire : <strong>{{ $year->name }}</strong><br>
            Classe : <strong>{{ $class->name }}</strong><br>
            Effectif : {{ $total }} élève(s)<br>
            {{ $trimestre == 0 ? 'Bilan Annuel' : 'Trimestre : ' . $trimestre }}
        </td>
    </tr>
</table>

<div class="page-title">RELEVÉ DE NOTES PAR MATIÈRE</div>
<div class="subject-line">
    Matière : {{ strtoupper($subject->name) }} &nbsp;|&nbsp; Coefficient : {{ $coef }}
    &nbsp;|&nbsp; {{ $trimestre == 0 ? 'Bilan Annuel' : 'Trimestre ' . $trimestre }}
</div>

<!-- Stats -->
<div class="stats-bar">
    <div class="stat-cell"><strong>{{ $total }}</strong>Effectif</div>
    <div class="stat-cell"><strong>{{ $admis }}</strong>Admis (≥10)</div>
    <div class="stat-cell"><strong>{{ $total > 0 ? round($admis/$total*100) : 0 }}%</strong>Taux réussite</div>
    <div class="stat-cell"><strong>{{ $moyClass }}</strong>Moy. classe</div>
    <div class="stat-cell"><strong>{{ $maxMoy }}</strong>Plus forte</div>
    <div class="stat-cell"><strong>{{ $minMoy }}</strong>Plus faible</div>
</div>

<!-- Tableau notes -->
@if($trimestre == 0)
{{-- Vue annuelle --}}
<table class="notes-table">
    <thead>
        <tr>
            <th style="width:25px">#</th>
            <th class="left" style="min-width:160px">Nom & Prénoms</th>
            <th>Moy. T1</th>
            <th>Moy. T2</th>
            <th>Moy. T3</th>
            <th>Moy. Annuelle</th>
            <th>Rang</th>
            <th>Appréciation</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $i => $row)
        @php
            $ma = $row['moy_ann'];
            $cls = $ma === null ? 'moy-null' : ($ma >= 10 ? 'moy-good' : 'moy-bad');
            $appr = $ma === null ? '-' : match(true) {
                $ma > 16  => 'Très Bien',
                $ma >= 14 => 'Bien',
                $ma >= 12 => 'Assez Bien',
                $ma >= 10 => 'Passable',
                $ma >= 8  => 'Insuffisant',
                $ma >= 6  => 'Faible',
                $ma >= 4  => 'Médiocre',
                default   => 'Très Faible',
            };
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="left">{{ $row['student']->last_name }} {{ $row['student']->first_name }}</td>
            <td><span class="moy {{ $row['moy_t1'] !== null ? ($row['moy_t1'] >= 10 ? 'moy-good' : 'moy-bad') : 'moy-null' }}">{{ $fmt($row['moy_t1']) }}</span></td>
            <td><span class="moy {{ $row['moy_t2'] !== null ? ($row['moy_t2'] >= 10 ? 'moy-good' : 'moy-bad') : 'moy-null' }}">{{ $fmt($row['moy_t2']) }}</span></td>
            <td><span class="moy {{ $row['moy_t3'] !== null ? ($row['moy_t3'] >= 10 ? 'moy-good' : 'moy-bad') : 'moy-null' }}">{{ $fmt($row['moy_t3']) }}</span></td>
            <td><span class="moy {{ $cls }}" style="font-size:9.5px">{{ $fmt($ma) }}</span></td>
            <td>{{ $row['rang'] !== '-' ? $row['rang'].'ᵉ' : '-' }}</td>
            <td>{{ $appr }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" style="text-align:right">Moyenne de la classe :</td>
            <td>{{ $moyClass }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

@else
{{-- Vue trimestrielle --}}
<table class="notes-table">
    <thead>
        <tr>
            <th style="width:25px">#</th>
            <th class="left" style="min-width:160px">Nom & Prénoms</th>
            <th>Interro 1</th>
            <th>Interro 2</th>
            <th>Interro 3</th>
            <th>Moy. Interros</th>
            <th>Devoir 1</th>
            <th>Devoir 2</th>
            <th>Moy./20</th>
            <th>Moy.Coef (×{{ $coef }})</th>
            <th>Rang</th>
            <th>Appréciation</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $i => $row)
        @php
            $m = $row['moyenne'];
            $cls = $m === null ? 'moy-null' : ($m >= 10 ? 'moy-good' : 'moy-bad');
            $appr = $m === null ? '-' : match(true) {
                $m > 16  => 'Très Bien',
                $m >= 14 => 'Bien',
                $m >= 12 => 'Assez Bien',
                $m >= 10 => 'Passable',
                $m >= 8  => 'Insuffisant',
                $m >= 6  => 'Faible',
                $m >= 4  => 'Médiocre',
                default  => 'Très Faible',
            };
            $moyCoef = $m !== null ? round($m * $coef, 2) : null;
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="left">{{ $row['student']->last_name }} {{ $row['student']->first_name }}</td>
            <td>{{ isset($row['interros'][0]) ? $fmt($row['interros'][0]) : '-' }}</td>
            <td>{{ isset($row['interros'][1]) ? $fmt($row['interros'][1]) : '-' }}</td>
            <td>{{ isset($row['interros'][2]) ? $fmt($row['interros'][2]) : '-' }}</td>
            <td>{{ $fmt($row['moyInterro']) }}</td>
            <td>{{ $fmt($row['devoir1']) }}</td>
            <td>{{ $fmt($row['devoir2']) }}</td>
            <td><span class="moy {{ $cls }}">{{ $fmt($m) }}</span></td>
            <td><span class="moy {{ $cls }}">{{ $fmt($moyCoef) }}</span></td>
            <td>{{ $row['rang'] !== '-' ? $row['rang'].'ᵉ' : '-' }}</td>
            <td>{{ $appr }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="8" style="text-align:right">Moyenne de la classe :</td>
            <td>{{ $moyClass }}</td>
            <td colspan="3"></td>
        </tr>
    </tfoot>
</table>
@endif

<!-- Signatures -->
<div class="signatures">
    <div class="sig-cell">
        <u><strong>Le Titulaire</strong></u><br><br><br><br>
    </div>
    <div class="sig-cell">
        <u><strong>Le Directeur</strong></u><br><br><br><br>
        <strong>Firmin DIDAGBE</strong>
    </div>
</div>

<div class="footer">Discipline - Créativité - Excellence &nbsp;·&nbsp; CS « MARIE-ALAIN » &nbsp;·&nbsp; Généré le {{ now()->format('d/m/Y') }}</div>
</body>
</html>