@extends('layouts.app')
@section('content')
<h2>Ajouter une Classe</h2>
<form method="POST" action="{{ route('admin.classes.store') }}">
    @csrf
    <label>Année scolaire</label>
    <select name="year_id" required>
        <option value="">--Choisir--</option>
        @foreach($years as $year)
            <option value="{{ $year->id }}">{{ $year->name }}</option>
        @endforeach
    </select>

    <label>Secteur</label>
    <select name="sector" id="sector" required>
        <option value="">--Choisir--</option>
        <option value="Maternelle et Primaire">Maternelle et Primaire</option>
        <option value="Secondaire">Secondaire</option>
    </select>

    <label>Classe</label>
    <select name="name" id="class_name" required>
        <option value="">--Choisir secteur d'abord--</option>
    </select>

    <label>Série (pour 2nde/1ère/Tle)</label>
    <select name="series" id="series" style="display:none;">
        <option value="">--Choisir--</option>
        <option value="A">A</option>
        <option value="B">B</option>
        <option value="C">C</option>
        <option value="D">D</option>
    </select>

    <input type="hidden" name="level" id="level">

    <button type="submit" id="submit_btn" disabled>Enregistrer</button>
</form>

<script>
const secteur = document.getElementById('sector');
const classe = document.getElementById('class_name');
const level = document.getElementById('level');
const series = document.getElementById('series');
const btn = document.getElementById('submit_btn');

const classes = {
    'Maternelle et Primaire': ['Maternelle 1','Maternelle 2','CI','CP','CE1','CE2','CM1','CM2'],
    'Secondaire': ['6ème','5ème','4ème','3ème','2nde','1ère','Tle']
};

// Met à jour la liste des classes selon le secteur
secteur.addEventListener('change', () => {
    const sec = secteur.value;
    classe.innerHTML = '<option value="">--Choisir--</option>';
    if(classes[sec]){
        classes[sec].forEach(c => {
            const opt = document.createElement('option');
            opt.value = c;
            opt.text = c;
            classe.appendChild(opt);
        });
    }
    series.style.display = 'none';
    series.value = '';
    level.value = '';
    btn.disabled = true;
});

// Met à jour le niveau et l'affichage de la série
classe.addEventListener('change', () => {
    const c = classe.value;
    level.value = (secteur.value=='Maternelle et Primaire') ? 'primaire' : 'secondaire';
    if(['2nde','1ère','Tle'].includes(c)) {
        series.style.display = 'block';
    } else {
        series.style.display = 'none';
        series.value = '';
    }
    checkForm();
});

// Vérifie si le formulaire est complet pour activer le bouton
series.addEventListener('change', checkForm);

function checkForm() {
    const yearSelected = !!document.querySelector('select[name="year_id"]').value;
    const sectorSelected = !!secteur.value;
    const classSelected = !!classe.value;
    const seriesSelected = (series.style.display === 'block') ? !!series.value : true;

    btn.disabled = !(yearSelected && sectorSelected && classSelected && seriesSelected);
}
</script>
@endsection
