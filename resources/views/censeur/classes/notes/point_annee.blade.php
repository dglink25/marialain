@extends('layouts.app')

@php
    $pageTitle = "Point Année - " . $classe->name;
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">

        {{-- En-tête --}}
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Point de l'Année Académique {{ $activeYear->name }} de la classe de {{ $classe->name }}</h1>   
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ url()->previous() }}"
                       class="inline-flex items-center px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i> Retour
                    </a>
                </div>
            </div>
        </div>

        {{-- Messages flash --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-start">
                <i class="fas fa-check-circle text-green-500 text-lg mt-0.5 mr-3"></i>
                <p class="text-green-700 text-sm">{{ session('success') }}</p>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
                <p class="text-red-700 text-sm">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Tableau principal --}}
        @if(count($tableauEleves) > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-x-auto">
            <table class="min-w-full text-sm" id="tableau-annuel">
                <thead>
                    <tr class="bg-gray-800 text-white">
                        <th rowspan="2" class="px-3 py-3 text-center font-semibold border-r border-gray-700 w-10">N° Matricule</th>
                        <th rowspan="2" class="px-4 py-3 text-left font-semibold border-r border-gray-700 min-w-[200px]">Nom & Prénom(s)</th>
                        {{-- Trimestre 1 --}}
                        <th colspan="3" class="px-3 py-2 text-center font-semibold border-r border-gray-700 bg-blue-800">Trimestre 1</th>
                        {{-- Trimestre 2 --}}
                        <th colspan="3" class="px-3 py-2 text-center font-semibold border-r border-gray-700 bg-indigo-800">Trimestre 2</th>
                        {{-- Trimestre 3 --}}
                        <th colspan="3" class="px-3 py-2 text-center font-semibold border-r border-gray-700 bg-purple-800">Trimestre 3</th>
                        {{-- Fin d'année --}}
                        <th colspan="3" class="px-3 py-2 text-center font-semibold bg-gray-700">Fin d'Année</th>
                    </tr>
                    <tr class="text-xs">
                        {{-- T1 --}}
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-blue-700 text-white">Conduite</th>
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-blue-700 text-white">Moy.</th>
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-blue-700 text-white">Rang</th>
                        {{-- T2 --}}
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-indigo-700 text-white">Conduite</th>
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-indigo-700 text-white">Moy.</th>
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-indigo-700 text-white">Rang</th>
                        {{-- T3 --}}
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-purple-700 text-white">Conduite</th>
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-purple-700 text-white">Moy.</th>
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-purple-700 text-white">Rang</th>
                        {{-- Annuel --}}
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-gray-600 text-white">Moy. Ann.</th>
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-gray-600 text-white">Rang Ann.</th>
                        <th class="px-3 py-2 text-center font-medium bg-gray-600 text-white">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($tableauEleves as $row)
                    @php
                        $isRedouble = $row['statut'] === 'Redouble';
                        $rowBg = $isRedouble ? 'bg-red-50 hover:bg-red-100' : 'bg-white hover:bg-gray-50';
                        $fmtMoy = fn($v) => $v !== null ? number_format($v, 2, ',', '') : '—';
                    @endphp
                    <tr class="{{ $rowBg }} transition-colors duration-150">
                        {{-- N° --}}
                        <td class="px-3 py-2.5 text-center text-gray-500 font-medium border-r border-gray-100">{{ $row['student']->num_educ }}</td>

                        {{-- Nom --}}
                        <td class="px-4 py-2.5 border-r border-gray-100">
                            <div class="font-semibold text-gray-900">{{ strtoupper($row['student']->last_name) }}</div>
                            <div class="text-gray-500 text-xs">{{ $row['student']->first_name }}</div>
                        </td>

                        {{-- T1 --}}
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 text-gray-600 text-xs">
                            {{ $row['conduite_t1'] > 0 ? number_format($row['conduite_t1'], 2, ',', '') : '—' }}
                        </td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 font-semibold
                            {{ $row['moy_t1'] !== null ? ($row['moy_t1'] >= 10 ? 'text-green-700' : 'text-red-700') : 'text-gray-400' }}">
                            {{ $fmtMoy($row['moy_t1']) }}
                        </td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 text-gray-500 text-xs">
                            <button
                                onclick="ouvrirBulletin({{ $row['student']->id }}, 1)"
                                class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 hover:underline text-xs font-medium">
                                {{ $row['rang_t1'] }}
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                        </td>

                        {{-- T2 --}}
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 text-gray-600 text-xs">
                            {{ $row['conduite_t2'] > 0 ? number_format($row['conduite_t2'], 2, ',', '') : '—' }}
                        </td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 font-semibold
                            {{ $row['moy_t2'] !== null ? ($row['moy_t2'] >= 10 ? 'text-green-700' : 'text-red-700') : 'text-gray-400' }}">
                            {{ $fmtMoy($row['moy_t2']) }}
                        </td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 text-gray-500 text-xs">
                            <button
                                onclick="ouvrirBulletin({{ $row['student']->id }}, 2)"
                                class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 hover:underline text-xs font-medium">
                                {{ $row['rang_t2'] }}
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                        </td>

                        {{-- T3 --}}
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 text-gray-600 text-xs">
                            {{ $row['conduite_t3'] > 0 ? number_format($row['conduite_t3'], 2, ',', '') : '—' }}
                        </td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 font-semibold
                            {{ $row['moy_t3'] !== null ? ($row['moy_t3'] >= 10 ? 'text-green-700' : 'text-red-700') : 'text-gray-400' }}">
                            {{ $fmtMoy($row['moy_t3']) }}
                        </td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 text-gray-500 text-xs">
                            <button
                                onclick="ouvrirBulletin({{ $row['student']->id }}, 3)"
                                class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 hover:underline text-xs font-medium">
                                {{ $row['rang_t3'] }}
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                        </td>

                        {{-- Fin d'année --}}
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 font-bold text-base
                            {{ $row['moy_annuelle'] !== null ? ($row['moy_annuelle'] >= 10 ? 'text-green-800' : 'text-red-800') : 'text-gray-400' }}">
                            {{ $fmtMoy($row['moy_annuelle']) }}
                        </td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 font-medium text-gray-700 text-xs">
                            {{ $row['rang_annuel'] }}
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            @if($row['statut'] === 'Passé')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-300">
                                    <i class="fas fa-check-circle mr-1"></i> Passé
                                </span>
                            @elseif($row['statut'] === 'Redouble')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-300">
                                    <i class="fas fa-times-circle mr-1"></i> Redouble
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Statistiques de bas de page --}}
        @php
            $nbPasses    = collect($tableauEleves)->where('statut', 'Passé')->count();
            $nbRedoubles = collect($tableauEleves)->where('statut', 'Redouble')->count();
            $nbTotal     = count($tableauEleves);
            $tauxReussite = $nbTotal > 0 ? round(($nbPasses / $nbTotal) * 100, 1) : 0;
        @endphp
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-green-50 rounded-xl border border-green-200 p-4 text-center">
                <div class="text-3xl font-bold text-green-700">{{ $nbPasses }}</div>
                <div class="text-sm text-green-600 mt-1">Élèves Passés</div>
            </div>
            <div class="bg-red-50 rounded-xl border border-red-200 p-4 text-center">
                <div class="text-3xl font-bold text-red-700">{{ $nbRedoubles }}</div>
                <div class="text-sm text-red-600 mt-1">Élèves Redoublants</div>
            </div>
            <div class="bg-blue-50 rounded-xl border border-blue-200 p-4 text-center">
                <div class="text-3xl font-bold text-blue-700">{{ $tauxReussite }}%</div>
                <div class="text-sm text-blue-600 mt-1">Taux de Réussite</div>
            </div>
        </div>

        @else
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-users text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900">Aucun élève validé dans cette classe</h3>
            <p class="text-gray-500 mt-2">Les élèves apparaîtront ici une fois validés.</p>
        </div>
        @endif
    </div>
</div>


<div id="bulletinModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    {{-- Overlay --}}
    <div id="bulletinOverlay" class="fixed inset-0 bg-blue-600 bg-opacity-60 transition-opacity" onclick="fermerBulletin()"></div>

    {{-- Conteneur --}}
    <div class="relative min-h-screen flex items-start justify-center p-4 pt-8">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-5xl z-10">
            {{-- Header modal --}}
            <div class="flex items-center justify-between px-6 py-4 bg-blue-400 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-white/10 rounded-full flex items-center justify-center">
                        <i class="fas fa-file-alt text-white"></i>
                    </div>
                    <div>
                        <h3 id="bulletinModalTitle" class="text-white font-semibold text-lg">Bulletin de Notes</h3>
                        <p class="text-gray-300 text-xs mt-0.5" id="bulletinModalSubtitle">Chargement...</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="fermerBulletin()"
                        class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            {{-- Loader --}}
            <div id="bulletinLoader" class="flex items-center justify-center py-20">
                <div class="text-center">
                    <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                    <p class="text-gray-500 text-sm">Chargement du bulletin...</p>
                </div>
            </div>

            {{-- Contenu du bulletin --}}
            <div id="bulletinContent" class="hidden p-6 overflow-x-auto">
                {{-- Injecté via JS --}}
            </div>
        </div>
    </div>
</div>

{{-- Style impression --}}
<style>
@media print {
    body > *:not(#printArea) { display: none !important; }
    #printArea { display: block !important; }
    .no-print { display: none !important; }
}
</style>

<div id="printArea" style="display:none;"></div>

<script>
    const CLASSE_ID = {{ $classe->id }};

    function ouvrirBulletin(studentId, trimestre) {
        const modal = document.getElementById('bulletinModal');
        const loader = document.getElementById('bulletinLoader');
        const content = document.getElementById('bulletinContent');
        const subtitle = document.getElementById('bulletinModalSubtitle');

        modal.classList.remove('hidden');
        loader.classList.remove('hidden');
        content.classList.add('hidden');
        subtitle.textContent = 'Trimestre ' + trimestre + ' — Chargement...';

        fetch(`/censeur/classes/${CLASSE_ID}/students/${studentId}/bulletin/${trimestre}/modal`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                content.innerHTML = `<div class="text-center py-10 text-red-600"><i class="fas fa-exclamation-triangle text-3xl mb-3"></i><p>${data.error}</p></div>`;
            } else {
                content.innerHTML = data.html;
            }
            loader.classList.add('hidden');
            content.classList.remove('hidden');
            subtitle.textContent = 'Trimestre ' + trimestre;
        })
        .catch(err => {
            content.innerHTML = `<div class="text-center py-10 text-red-600"><i class="fas fa-exclamation-triangle text-3xl mb-3"></i><p>Erreur lors du chargement.</p></div>`;
            loader.classList.add('hidden');
            content.classList.remove('hidden');
        });
    }

    function fermerBulletin() {
        document.getElementById('bulletinModal').classList.add('hidden');
        document.getElementById('bulletinContent').innerHTML = '';
    }

    function imprimerBulletin() {
        const contenu = document.getElementById('bulletinContent').innerHTML;
        const printArea = document.getElementById('printArea');
        printArea.innerHTML = contenu;
        printArea.style.display = 'block';
        window.print();
        printArea.style.display = 'none';
        printArea.innerHTML = '';
    }

    // Fermer sur Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') fermerBulletin();
    });
</script>
@endsection