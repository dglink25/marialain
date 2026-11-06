@extends('layouts.app')
@php $pageTitle = "Trimestres — " . $classe->name; @endphp

@section('content')
<div class="min-h-screen bg-slate-50 py-6 px-4 sm:px-6 lg:px-8">
<div class="max-w-7xl mx-auto">

    {{-- En-tête --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ url()->previous() }}"
               class="w-10 h-10 flex items-center justify-center bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm">
                <i class="fas fa-arrow-left text-slate-600"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">{{ $classe->name }}</h1>
                <p class="text-sm text-slate-500 mt-0.5">
                    <i class="fas fa-calendar-alt mr-1 text-indigo-400"></i>
                    {{ $activeYear->name ?? '' }} &nbsp;·&nbsp;
                    <i class="fas fa-users mr-1 text-indigo-400"></i>
                    {{ $students->count() }} élève(s)
                </p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('censeur.classes.point-annee.pdf', $classe->id) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl shadow transition">
                <i class="fas fa-file-pdf text-xs"></i> Point Année (PDF)
            </a>
            <a href="{{ route('censeur.classes.point-annee', $classe->id) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow transition">
                <i class="fas fa-chart-line text-xs"></i> Point de l'Année
            </a>
            {{-- Bouton Fiche élève global --}}
            <button onclick="ouvrirFicheModal(null)"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow transition">
                <i class="fas fa-user-graduate text-xs"></i> Fiche élève
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-2xl px-5 py-4 flex items-center gap-3 shadow-sm">
        <i class="fas fa-check-circle text-green-500"></i>
        <span class="text-sm font-semibold">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Grille des 3 trimestres --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
        @foreach($trimestres as $t)
        @php
            $colors = [
                1 => ['bg' => 'from-blue-500 to-indigo-500',   'light' => 'bg-blue-50',   'border' => 'border-blue-200',   'text' => 'text-blue-700',   'icon' => 'bg-blue-100'],
                2 => ['bg' => 'from-emerald-500 to-teal-500',  'light' => 'bg-emerald-50','border' => 'border-emerald-200','text' => 'text-emerald-700','icon' => 'bg-emerald-100'],
                3 => ['bg' => 'from-violet-500 to-purple-500', 'light' => 'bg-violet-50', 'border' => 'border-violet-200', 'text' => 'text-violet-700', 'icon' => 'bg-violet-100'],
            ];
            $c = $colors[$t];
        @endphp
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
            {{-- Bandeau couleur --}}
            <div class="h-1.5 bg-gradient-to-r {{ $c['bg'] }}"></div>

            {{-- Header carte --}}
            <div class="px-5 pt-5 pb-4 border-b border-slate-50 flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br {{ $c['bg'] }} rounded-xl flex items-center justify-center shadow-sm shrink-0">
                    <span class="text-white font-black text-xl">{{ $t }}</span>
                </div>
                <div>
                    <p class="font-bold text-slate-800 text-base">Trimestre {{ $t }}</p>
                    <p class="text-xs text-slate-400">Période d'évaluation</p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="p-4 space-y-2">
                <a href="{{ route('teacher.classes.trimestres.eleves', [$classe->id, $t]) }}"
                   class="flex items-center gap-3 px-4 py-2.5 {{ $c['light'] }} {{ $c['border'] }} border rounded-xl hover:opacity-80 transition group">
                    <div class="w-7 h-7 {{ $c['icon'] }} rounded-lg flex items-center justify-center shrink-0">
                        <i class="fas fa-list-alt {{ $c['text'] }} text-xs"></i>
                    </div>
                    <span class="text-sm font-semibold {{ $c['text'] }}">Récapitulatif</span>
                    <i class="fas fa-chevron-right {{ $c['text'] }} text-xs ml-auto opacity-0 group-hover:opacity-100 transition"></i>
                </a>

                <a href="{{ route('censeur.classes.trimestre.matiere', [$classe->id, $t]) }}"
                   class="flex items-center gap-3 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl hover:bg-slate-100 transition group">
                    <div class="w-7 h-7 bg-slate-100 rounded-lg flex items-center justify-center shrink-0">
                        <i class="fas fa-book text-slate-500 text-xs"></i>
                    </div>
                    <span class="text-sm font-semibold text-slate-700">Matières</span>
                    <i class="fas fa-chevron-right text-slate-400 text-xs ml-auto opacity-0 group-hover:opacity-100 transition"></i>
                </a>

                <a href="{{ route('censeur.classes.trimestre.points', [$classe->id, $t]) }}"
                   class="flex items-center gap-3 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl hover:bg-slate-100 transition group">
                    <div class="w-7 h-7 bg-slate-100 rounded-lg flex items-center justify-center shrink-0">
                        <i class="fas fa-chart-bar text-slate-500 text-xs"></i>
                    </div>
                    <span class="text-sm font-semibold text-slate-700">Points disponibles</span>
                    <i class="fas fa-chevron-right text-slate-400 text-xs ml-auto opacity-0 group-hover:opacity-100 transition"></i>
                </a>

                <button type="button" onclick="openListe({{ $t }})"
                        class="w-full flex items-center gap-3 px-4 py-2.5 bg-sky-50 border border-sky-200 rounded-xl hover:bg-sky-100 transition group">
                    <div class="w-7 h-7 bg-sky-100 rounded-lg flex items-center justify-center shrink-0">
                        <i class="fas fa-file-pdf text-sky-600 text-xs"></i>
                    </div>
                    <span class="text-sm font-semibold text-sky-700">Fiche de notes PDF</span>
                    <i class="fas fa-chevron-right text-sky-500 text-xs ml-auto opacity-0 group-hover:opacity-100 transition"></i>
                </button>

                {{-- Bouton Fiche élève par trimestre --}}
                <button type="button" onclick="ouvrirFicheModal({{ $t }})"
                        class="w-full flex items-center gap-3 px-4 py-2.5 bg-indigo-50 border border-indigo-200 rounded-xl hover:bg-indigo-100 transition group">
                    <div class="w-7 h-7 bg-indigo-100 rounded-lg flex items-center justify-center shrink-0">
                        <i class="fas fa-user-graduate text-indigo-600 text-xs"></i>
                    </div>
                    <span class="text-sm font-semibold text-indigo-700">Fiche élève</span>
                    <i class="fas fa-chevron-right text-indigo-500 text-xs ml-auto opacity-0 group-hover:opacity-100 transition"></i>
                </button>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Info bas de page --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-6 py-4 flex items-start gap-4">
        <div class="w-9 h-9 bg-indigo-50 rounded-xl flex items-center justify-center shrink-0 mt-0.5">
            <i class="fas fa-info-circle text-indigo-500"></i>
        </div>
        <div class="text-sm text-slate-600">
            <strong class="text-slate-800">Gestion des trimestres</strong> —
            Consultez le <strong>récapitulatif</strong> pour une vue d'ensemble,
            gérez les <strong>matières</strong> par trimestre,
            ou utilisez <strong>Fiche élève</strong> pour consulter toutes les notes d'un apprenant en temps réel.
        </div>
    </div>

</div>
</div>

{{-- ═══════════════════════ MODAL FICHE DE NOTES PDF ═══════════════════════ --}}
<div id="listeElevesModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" id="listeElevesOverlay" onclick="closeListe()"></div>
    <div class="fixed inset-0 overflow-y-auto flex items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md animate-modalIn">
            <div class="h-1.5 bg-gradient-to-r from-sky-500 to-blue-600 rounded-t-2xl"></div>
            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-sky-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-file-pdf text-sky-600"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Fiche de notes PDF</h3>
                            <p class="text-xs text-slate-400" id="listeElevesTrimestreLabel"></p>
                        </div>
                    </div>
                    <button onclick="closeListe()" class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Sélectionner une matière</label>
                    <select id="listeElevesSubjectSelect" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        <option value="">-- Choisissez une matière --</option>
                        @foreach($matieres as $matiere)
                        <option value="{{ $matiere->subject_id ?? $matiere->id }}">
                            {{ $matiere->name ?? $matiere->subject->name ?? 'Matière' }}
                            @if(isset($matiere->coefficient)) (Coef. {{ $matiere->coefficient }}) @endif
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3">
                    <button onclick="closeListe()" class="flex-1 py-2.5 bg-slate-100 text-slate-700 font-semibold rounded-xl hover:bg-slate-200 text-sm transition">Annuler</button>
                    <button onclick="telechargerListe()" class="flex-1 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-xl text-sm shadow transition flex items-center justify-center gap-2">
                        <i class="fas fa-download text-xs"></i> Télécharger
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════ MODAL FICHE ÉLÈVE ═══════════════════════ --}}
<div id="ficheModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="fermerFicheModal()"></div>
    <div class="fixed inset-0 overflow-y-auto flex items-start justify-center p-4 pt-6">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-5xl animate-modalIn">
            <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-violet-600 rounded-t-2xl"></div>

            {{-- Header --}}
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-graduate text-indigo-600"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Fiche de notes — <span id="fiche-nom">Sélectionnez un élève</span></h3>
                        <p class="text-xs text-slate-400" id="fiche-subtitle">{{ $classe->name }} · {{ $activeYear->name ?? '' }}</p>
                    </div>
                </div>
                <button onclick="fermerFicheModal()" class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Filtres --}}
            <div class="px-6 py-4 border-b border-slate-50 flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Élève</label>
                    <select id="fiche-student-select" onchange="chargerFicheEleve()"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-slate-50">
                        <option value="">-- Sélectionnez un élève --</option>
                        @foreach($students as $s)
                        <option value="{{ $s->id }}">{{ strtoupper($s->last_name) }} {{ $s->first_name }}{{ $s->num_educ ? ' — '.$s->num_educ : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:w-48">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Trimestre</label>
                    <select id="fiche-trimestre-select" onchange="chargerFicheEleve()"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-slate-50">
                        <option value="">-- Tous --</option>
                        <option value="1">Trimestre 1</option>
                        <option value="2">Trimestre 2</option>
                        <option value="3">Trimestre 3</option>
                    </select>
                </div>
            </div>

            {{-- Contenu dynamique --}}
            <div id="fiche-content" class="p-6">
                <div class="flex flex-col items-center justify-center py-16 text-slate-300">
                    <i class="fas fa-user-graduate text-5xl mb-4"></i>
                    <p class="text-sm">Sélectionnez un élève pour afficher ses notes</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes modalIn { from { opacity:0; transform:scale(0.95) translateY(-10px); } to { opacity:1; transform:scale(1) translateY(0); } }
.animate-modalIn { animation: modalIn 0.2s ease-out forwards; }
</style>

<script>
const CLASSE_ID = {{ $classe->id }};
const CSRF = '{{ csrf_token() }}';
const URL_NOTES_AJAX = '{{ url("/censeur/classes/".$classe->id."/students/__STUDENT__/notes-ajax/__TRIMESTRE__") }}';

// ── Modal Fiche de notes PDF ─────────────────────────────────────────
let selectedTrimestre = null;
function openListe(t) {
    selectedTrimestre = t;
    document.getElementById('listeElevesTrimestreLabel').textContent = 'Trimestre ' + t;
    document.getElementById('listeElevesSubjectSelect').value = '';
    document.getElementById('listeElevesModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeListe() {
    document.getElementById('listeElevesModal').classList.add('hidden');
    document.body.style.overflow = '';
}
function telechargerListe() {
    const subjectId = document.getElementById('listeElevesSubjectSelect').value;
    if (!subjectId) { alert('Veuillez sélectionner une matière.'); return; }
    const url = '{{ url("/censeur/classes/".$classe->id."/trimestres/__T__/subjects/__S__/liste-eleves/pdf") }}'
        .replace('__T__', selectedTrimestre).replace('__S__', subjectId);
    closeListe();
    window.location.href = url;
}

// ── Modal Fiche Élève ─────────────────────────────────────────────────
function ouvrirFicheModal(trimestre) {
    if (trimestre) document.getElementById('fiche-trimestre-select').value = trimestre;
    document.getElementById('ficheModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    chargerFicheEleve();
}
function fermerFicheModal() {
    document.getElementById('ficheModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function chargerFicheEleve() {
    const studentId  = document.getElementById('fiche-student-select').value;
    const trimestreV = document.getElementById('fiche-trimestre-select').value;
    const content    = document.getElementById('fiche-content');
    const nom        = document.getElementById('fiche-nom');

    if (!studentId) {
        content.innerHTML = `<div class="flex flex-col items-center justify-center py-16 text-slate-300"><i class="fas fa-user-graduate text-5xl mb-4"></i><p class="text-sm">Sélectionnez un élève pour afficher ses notes</p></div>`;
        nom.textContent = 'Sélectionnez un élève';
        return;
    }

    const trimestres = trimestreV ? [parseInt(trimestreV)] : [1, 2, 3];
    content.innerHTML = `<div class="flex items-center justify-center py-12"><div class="w-8 h-8 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div></div>`;

    Promise.all(trimestres.map(t => {
        const url = URL_NOTES_AJAX.replace('__STUDENT__', studentId).replace('__TRIMESTRE__', t);
        return fetch(url, { headers: {'X-Requested-With':'XMLHttpRequest'} }).then(r => r.json());
    }))
    .then(results => {
        nom.textContent = results[0]?.student?.name ?? '';
        content.innerHTML = results.map(data => buildTrimestreTable(data)).join('');
    })
    .catch(() => {
        content.innerHTML = `<p class="text-center text-red-500 py-8">Erreur de chargement.</p>`;
    });
}

function buildTrimestreTable(data) {
    if (!data || !data.rows) return '';
    const t      = data.trimestre;
    const moy    = data.moy_gen;
    const moyStr = moy !== null ? moy.toFixed(2).replace('.', ',') : '—';
    const moyColor = moy !== null ? (moy >= 10 ? 'text-green-600' : 'text-red-600') : 'text-slate-400';
    const tColors = { 1:'bg-blue-600', 2:'bg-emerald-600', 3:'bg-violet-600' };
    const bgColor = tColors[t] || 'bg-indigo-600';

    let rows = data.rows.map(r => {
        const hasMoy  = r.moy !== null;
        const good    = hasMoy && r.moy >= 10;
        const moyDisp = hasMoy ? r.moy.toFixed(2).replace('.', ',') : '—';
        const coefDisp= r.moy_coef !== null ? r.moy_coef.toFixed(2).replace('.', ',') : '—';
        const moyIntDisp = r.moy_interro !== null ? r.moy_interro.toFixed(2).replace('.', ',') : '—';

        const fmtN = v => v !== null && v !== undefined ? parseFloat(v).toFixed(2).replace('.', ',') : '<span class="text-slate-300">—</span>';

        return `<tr class="hover:bg-slate-50 transition-colors border-b border-slate-50">
            <td class="px-3 py-2 text-xs font-semibold text-slate-800">${r.subject}</td>
            <td class="px-2 py-2 text-center text-xs text-slate-500">${r.coef}</td>
            <td class="px-2 py-2 text-center text-xs">${fmtN(r.i1)}</td>
            <td class="px-2 py-2 text-center text-xs">${fmtN(r.i2)}</td>
            <td class="px-2 py-2 text-center text-xs">${fmtN(r.i3)}</td>
            <td class="px-2 py-2 text-center text-xs">${fmtN(r.i4)}</td>
            <td class="px-2 py-2 text-center text-xs">${fmtN(r.i5)}</td>
            <td class="px-2 py-2 text-center text-xs font-semibold text-indigo-600">${moyIntDisp}</td>
            <td class="px-2 py-2 text-center text-xs">${fmtN(r.d1)}</td>
            <td class="px-2 py-2 text-center text-xs">${fmtN(r.d2)}</td>
            <td class="px-2 py-2 text-center text-xs font-bold ${hasMoy ? (good ? 'text-green-600' : 'text-red-600') : 'text-slate-300'}">${moyDisp}</td>
            <td class="px-2 py-2 text-center text-xs text-slate-600">${coefDisp}</td>
            <td class="px-2 py-2 text-xs text-slate-400">${r.appreciation}</td>
        </tr>`;
    }).join('');

    return `
    <div class="mb-6 overflow-hidden rounded-xl border border-slate-200">
        <div class="${bgColor} px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center text-white font-black text-sm">${t}</span>
                <span class="text-white font-bold text-sm">Trimestre ${t}</span>
            </div>
            <div class="flex items-center gap-3 text-white text-xs">
                <span>Conduite : <strong>${data.conduite}/20</strong></span>
                <span class="px-2.5 py-1 rounded-full font-bold ${moy !== null ? (moy >= 10 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') : 'bg-white/20 text-white'}">
                    Moy. ${moyStr}/20
                </span>
            </div>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-xs min-w-[750px]">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold text-slate-600 w-32">Matière</th>
                    <th class="px-2 py-2 text-center font-semibold text-slate-500 w-10">Coef</th>
                    <th class="px-2 py-2 text-center font-semibold text-slate-400 w-9">I1</th>
                    <th class="px-2 py-2 text-center font-semibold text-slate-400 w-9">I2</th>
                    <th class="px-2 py-2 text-center font-semibold text-slate-400 w-9">I3</th>
                    <th class="px-2 py-2 text-center font-semibold text-slate-400 w-9">I4</th>
                    <th class="px-2 py-2 text-center font-semibold text-slate-400 w-9">I5</th>
                    <th class="px-2 py-2 text-center font-semibold text-indigo-500 w-14">Moy.I</th>
                    <th class="px-2 py-2 text-center font-semibold text-slate-400 w-10">D1</th>
                    <th class="px-2 py-2 text-center font-semibold text-slate-400 w-10">D2</th>
                    <th class="px-2 py-2 text-center font-bold text-slate-700 w-14">Moy/20</th>
                    <th class="px-2 py-2 text-center font-semibold text-slate-500 w-12">×Coef</th>
                    <th class="px-2 py-2 text-left font-semibold text-slate-400">Appréciation</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
            <tfoot>
                <tr class="bg-slate-100 border-t-2 border-slate-200 font-semibold text-slate-700">
                    <td class="px-3 py-2 text-xs" colspan="11">
                        Total coeff : ${data.total_coef} &nbsp;|&nbsp; Total pts : ${data.total_pts?.toFixed(2).replace('.', ',')}
                    </td>
                    <td class="px-2 py-2 text-center text-xs">${data.total_pts?.toFixed(2).replace('.', ',')}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>`;
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeListe(); fermerFicheModal(); }
});
</script>
@endsection
