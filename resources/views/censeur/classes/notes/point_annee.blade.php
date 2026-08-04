@extends('layouts.app')

@php
    $pageTitle = "Point Année - " . $classe->name;
    // IDs autorisés : Directeur Fondateur & Secrétaire Comptable
    $isAuthorized = in_array(auth()->id(), [6, 7]);
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">

        {{-- En-tête --}}
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Point de l'Année Académique {{ $activeYear->name }} - {{ $classe->name }}</h1>
                </div>
                <div class="flex flex-wrap gap-3">

                    {{-- Bouton Délibérer --}}
                    <button id="btnDeliberer"
                            onclick="ouvrirModalDeliberation()"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-700 text-white font-bold rounded-xl shadow-lg hover:from-indigo-700 hover:to-purple-800 transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Délibérer
                    </button>

                    {{-- ================================================================
                         Boutons Bulletins Fin d'Année — RÉSERVÉS Directeur & Secrétaire
                    ================================================================ --}}
                    @if($isAuthorized)
                        {{-- Télécharger PDF --}}
                        <a href="{{ route('censeur.classes.bulletin.fin-annee.all-pdf', $classe->id) }}"
                           class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold rounded-xl shadow-md hover:from-emerald-700 hover:to-teal-700 transition-all duration-200 hover:shadow-lg">
                            <i class="fas fa-file-archive mr-2"></i>
                            Bulletins Fin d'Année (PDF)
                        </a>

                        {{-- Imprimer directement --}}
                        <a href="{{ route('censeur.classes.bulletin.fin-annee.print', $classe->id) }}"
                           target="_blank"
                           class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-semibold rounded-xl shadow-md hover:from-blue-700 hover:to-cyan-700 transition-all duration-200 hover:shadow-lg">
                            <i class="fas fa-print mr-2"></i>
                            Imprimer Bulletins
                        </a>
                    @else
                        <button onclick="ouvrirModalAccesRefuse()"
                                class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold rounded-xl shadow-md hover:from-emerald-700 hover:to-teal-700 transition-all duration-200 hover:shadow-lg opacity-75 cursor-not-allowed relative">
                            <i class="fas fa-lock mr-2 text-xs"></i>
                            <i class="fas fa-file-archive mr-2"></i>
                            Bulletins Fin d'Année (PDF)
                        </button>
                        <button onclick="ouvrirModalAccesRefuse()"
                                class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-semibold rounded-xl shadow-md hover:from-blue-700 hover:to-cyan-700 transition-all duration-200 hover:shadow-lg opacity-75 cursor-not-allowed relative">
                            <i class="fas fa-lock mr-2 text-xs"></i>
                            <i class="fas fa-print mr-2"></i>
                            Imprimer Bulletins
                        </button>
                    @endif

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
                        <th colspan="3" class="px-3 py-2 text-center font-semibold border-r border-gray-700 bg-blue-800">Trimestre 1</th>
                        <th colspan="3" class="px-3 py-2 text-center font-semibold border-r border-gray-700 bg-indigo-800">Trimestre 2</th>
                        <th colspan="3" class="px-3 py-2 text-center font-semibold border-r border-gray-700 bg-purple-800">Trimestre 3</th>
                        <th colspan="3" class="px-3 py-2 text-center font-semibold bg-gray-700">Fin d'Année</th>
                        <th rowspan="2" class="px-3 py-3 text-center font-semibold bg-emerald-800 border-l border-gray-700 min-w-[130px]">Bulletin</th>
                    </tr>
                    <tr class="text-xs">
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-blue-700 text-white">Conduite</th>
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-blue-700 text-white">Moy.</th>
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-blue-700 text-white">Rang</th>
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-indigo-700 text-white">Conduite</th>
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-indigo-700 text-white">Moy.</th>
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-indigo-700 text-white">Rang</th>
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-purple-700 text-white">Conduite</th>
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-purple-700 text-white">Moy.</th>
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-purple-700 text-white">Rang</th>
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-gray-600 text-white">Moy. Ann.</th>
                        <th class="px-3 py-2 text-center font-medium border-r border-gray-600 bg-gray-600 text-white">Rang Ann.</th>
                        <th class="px-3 py-2 text-center font-medium bg-gray-600 text-white">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($tableauEleves as $row)
                    @php
                        $isRedouble = $row['statut'] === 'Redouble';
                        $rowBg      = $isRedouble ? 'bg-red-50 hover:bg-red-100' : 'bg-white hover:bg-gray-50';
                        $fmtMoy     = fn($v) => $v !== null ? number_format($v, 2, ',', '') : '—';
                    @endphp
                    <tr class="{{ $rowBg }} transition-colors duration-150">
                        <td class="px-3 py-2.5 text-center text-gray-500 font-medium border-r border-gray-100">{{ $row['student']->num_educ }}</td>
                        <td class="px-4 py-2.5 border-r border-gray-100">
                            <div class="font-semibold text-gray-900">{{ strtoupper($row['student']->last_name) }}</div>
                            <div class="text-gray-500 text-xs">{{ $row['student']->first_name }}</div>
                        </td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 text-gray-600 text-xs">{{ $row['conduite_t1'] > 0 ? number_format($row['conduite_t1'], 2, ',', '') : '—' }}</td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 font-semibold {{ $row['moy_t1'] !== null ? ($row['moy_t1'] >= 10 ? 'text-green-700' : 'text-red-700') : 'text-gray-400' }}">{{ $fmtMoy($row['moy_t1']) }}</td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 text-gray-500 text-xs">
                            <button onclick="ouvrirBulletin({{ $row['student']->id }}, 1)" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 hover:underline text-xs font-medium">{{ $row['rang_t1'] }} <i class="fas fa-eye text-xs"></i></button>
                        </td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 text-gray-600 text-xs">{{ $row['conduite_t2'] > 0 ? number_format($row['conduite_t2'], 2, ',', '') : '—' }}</td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 font-semibold {{ $row['moy_t2'] !== null ? ($row['moy_t2'] >= 10 ? 'text-green-700' : 'text-red-700') : 'text-gray-400' }}">{{ $fmtMoy($row['moy_t2']) }}</td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 text-gray-500 text-xs">
                            <button onclick="ouvrirBulletin({{ $row['student']->id }}, 2)" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 hover:underline text-xs font-medium">{{ $row['rang_t2'] }} <i class="fas fa-eye text-xs"></i></button>
                        </td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 text-gray-600 text-xs">{{ $row['conduite_t3'] > 0 ? number_format($row['conduite_t3'], 2, ',', '') : '—' }}</td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 font-semibold {{ $row['moy_t3'] !== null ? ($row['moy_t3'] >= 10 ? 'text-green-700' : 'text-red-700') : 'text-gray-400' }}">{{ $fmtMoy($row['moy_t3']) }}</td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 text-gray-500 text-xs">
                            <button onclick="ouvrirBulletin({{ $row['student']->id }}, 3)" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 hover:underline text-xs font-medium">{{ $row['rang_t3'] }} <i class="fas fa-eye text-xs"></i></button>
                        </td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 font-bold text-base {{ $row['moy_annuelle'] !== null ? ($row['moy_annuelle'] >= 10 ? 'text-green-800' : 'text-red-800') : 'text-gray-400' }}">{{ $fmtMoy($row['moy_annuelle']) }}</td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100 font-medium text-gray-700 text-xs">{{ $row['rang_annuel'] }}</td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100">
                            @if($row['statut'] === 'Passé')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-300"><i class="fas fa-check-circle mr-1"></i>PASSE</span>
                            @elseif($row['statut'] === 'Redouble')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-300"><i class="fas fa-times-circle mr-1"></i>REDOUBLE</span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>

                        {{-- ============================================================
                             Colonne PDF Fin d'Année — RÉSERVÉE Directeur & Secrétaire
                        ============================================================ --}}
                        <td class="px-3 py-2.5 text-center border-l border-gray-200 bg-emerald-50">
                            @if($isAuthorized)
                                <a href="{{ route('censeur.classes.bulletin.fin-annee.student-pdf', [$classe->id, $row['student']->id]) }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-semibold rounded-lg hover:bg-emerald-700 transition-colors duration-150 shadow-sm">
                                    <i class="fas fa-file-pdf text-xs"></i> Fin d'Année
                                </a>
                            @else
                                <button onclick="ouvrirModalAccesRefuse()"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-400 text-white text-xs font-semibold rounded-lg cursor-not-allowed shadow-sm"
                                        title="Réservé au Directeur Fondateur & Secrétaire Comptable">
                                    <i class="fas fa-lock text-xs"></i> Fin d'Année
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Statistiques --}}
        @php
            $nbPasses    = collect($tableauEleves)->where('statut', 'Passé')->count();
            $nbRedoubles = collect($tableauEleves)->where('statut', 'Redouble')->count();
            $nbTotal     = count($tableauEleves);
            $tauxReussite = $nbTotal > 0 ? round(($nbPasses / $nbTotal) * 100, 1) : 0;
        @endphp
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-green-50 rounded-xl border border-green-200 p-4 text-center">
                <div class="text-3xl font-bold text-green-700">{{ $nbPasses }}</div>
                <div class="text-sm text-green-600 mt-1">Élèves passent en classe supérieure</div>
            </div>
            <div class="bg-red-50 rounded-xl border border-red-200 p-4 text-center">
                <div class="text-3xl font-bold text-red-700">{{ $nbRedoubles }}</div>
                <div class="text-sm text-red-600 mt-1">Élèves redoublent</div>
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


{{-- ═══════════════════════════════════════════════════════════════════
     MODAL 1 : ACCÈS REFUSÉ
═══════════════════════════════════════════════════════════════════ --}}
<div id="modalAccesRefuse" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="fermerModalAccesRefuse()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-modalIn">
            <div class="h-2 bg-gradient-to-r from-red-500 to-rose-600"></div>
            <div class="p-8">
                <div class="flex justify-center mb-5">
                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center ring-8 ring-red-50">
                        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">Accès Refusé</h2>
                <p class="text-gray-500 text-center text-sm mb-6">Zone à accès restreint</p>
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                    <p class="text-red-800 text-sm font-medium mb-3">Cette fonctionnalité est réservée à :</p>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-sm text-red-700">
                            <i class="fas fa-crown text-yellow-500 w-5"></i>
                            <span>Le Directeur Fondateur</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-red-700">
                            <i class="fas fa-user-tie text-blue-500 w-5"></i>
                            <span>La Secrétaire Comptable</span>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 mb-6 text-xs text-gray-500 flex items-start gap-2">
                    <i class="fas fa-info-circle text-blue-400 mt-0.5"></i>
                    Contactez l'administrateur pour obtenir les droits nécessaires.
                </div>
                <button onclick="fermerModalAccesRefuse()"
                        class="w-full py-3 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-700 transition-colors duration-200">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════════════
     MODAL 2 : FORMULAIRE DÉLIBÉRATION
═══════════════════════════════════════════════════════════════════ --}}
<div id="modalDeliberation" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="fermerModalDeliberation()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden animate-modalIn my-4">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-indigo-600 to-purple-700 px-6 py-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white">Délibération</h2>
                            <p class="text-indigo-200 text-sm">{{ $classe->name }} — Année active : {{ $activeYear->name }}</p>
                        </div>
                    </div>
                    <button onclick="fermerModalDeliberation()" class="text-white/70 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Spinner chargement --}}
            <div id="deliLoadingState" class="p-12 text-center">
                <div class="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                <p class="text-gray-500 text-sm">Chargement des données...</p>
            </div>

            {{-- Contenu du formulaire --}}
            <div id="deliFormState" class="hidden">
                {{-- Alerte délibération existante --}}
                <div id="deliExistingAlert" class="hidden mx-6 mt-5 bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                        <div>
                            <p class="text-amber-800 font-semibold text-sm">Délibération déjà effectuée</p>
                            <p id="deliExistingInfo" class="text-amber-700 text-xs mt-1"></p>
                            <button id="btnAnnulerDeliberation"
                                    onclick="demanderAnnulation()"
                                    class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:text-red-800 underline">
                                <i class="fas fa-undo"></i> Annuler cette délibération
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-4" id="deliForm">

                    {{-- Ligne 1 : Année + Seuil --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                <i class="fas fa-calendar-alt text-indigo-500 mr-1"></i>Année de destination *
                            </label>
                            <select id="selectTargetYear" onchange="recalculerStatuts()"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-gray-700 bg-white text-sm">
                                <option value="">-- Année inactive --</option>
                            </select>
                            <p id="yearMissingMsg" class="hidden mt-1 text-xs text-red-600"><i class="fas fa-info-circle mr-1"></i>Aucune année inactive disponible.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                <i class="fas fa-sliders-h text-indigo-500 mr-1"></i>Seuil de passage /20
                            </label>
                            <input type="number" id="seuilPassage" value="10" min="0" max="20" step="0.5"
                                   onchange="recalculerStatuts()"
                                   class="w-full px-3 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-gray-700 text-sm">
                        </div>
                    </div>

                    {{-- Classe par défaut pour les admis --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            <i class="fas fa-school text-indigo-500 mr-1"></i>
                            Classe par défaut pour les admis *
                            <span class="text-xs font-normal text-gray-400 ml-1">(modifiable par élève ci-dessous)</span>
                        </label>
                        <select id="selectTargetClass" onchange="appliquerClasseParDefaut()"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-gray-700 bg-white text-sm">
                            <option value="">-- Sélectionnez la classe --</option>
                        </select>
                        <p id="classMissingMsg" class="hidden mt-1 text-xs text-red-600"><i class="fas fa-info-circle mr-1"></i>Aucune classe disponible.</p>
                    </div>

                    {{-- Option emploi du temps --}}
                    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-700">
                                <i class="fas fa-calendar-week text-indigo-500 mr-1"></i>Copier l'emploi du temps
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">Copier les EDT & relations enseignant-matière vers la nouvelle année</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer ml-4 flex-shrink-0">
                            <input type="checkbox" id="keepTimetable" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    {{-- Tableau des élèves avec affectation individuelle --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-bold text-gray-700">
                                <i class="fas fa-users text-indigo-500 mr-1"></i>Affectation individuelle des élèves
                            </h3>
                            <div class="flex gap-2 text-xs">
                                <span id="compteurAdmis" class="bg-green-100 text-green-700 font-bold px-2 py-1 rounded-full">0 admis</span>
                                <span id="compteurRedoublants" class="bg-red-100 text-red-700 font-bold px-2 py-1 rounded-full">0 redoublants</span>
                            </div>
                        </div>
                        <div class="border border-gray-200 rounded-xl overflow-hidden">
                            <div class="overflow-x-auto max-h-64 overflow-y-auto">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-gray-100 sticky top-0 z-10">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600 w-8">#</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600">Élève</th>
                                            <th class="px-3 py-2 text-center font-semibold text-gray-600 w-20">Moy. Ann.</th>
                                            <th class="px-3 py-2 text-center font-semibold text-gray-600 w-24">Statut auto</th>
                                            <th class="px-3 py-2 text-left font-semibold text-gray-600 min-w-[160px]">Classe de destination</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableauElevesDelib" class="divide-y divide-gray-100 bg-white">
                                        <tr>
                                            <td colspan="5" class="px-3 py-6 text-center text-gray-400">
                                                <i class="fas fa-info-circle mr-1"></i>Sélectionnez une classe par défaut pour afficher les élèves.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">
                            <i class="fas fa-lightbulb text-yellow-400 mr-1"></i>
                            Les redoublants restent dans <strong>{{ $classe->name }}</strong>. Vous pouvez choisir une autre classe pour les admis.
                        </p>
                    </div>

                    {{-- Bouton soumettre --}}
                    <button id="btnSoumettreDeli"
                            onclick="demanderConfirmation()"
                            class="w-full py-3.5 bg-gradient-to-r from-indigo-600 to-purple-700 text-white font-bold rounded-xl hover:from-indigo-700 hover:to-purple-800 transition-all duration-200 shadow-lg flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Lancer la délibération
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════════════
     MODAL 3 : CONFIRMATION IRRÉVERSIBLE
═══════════════════════════════════════════════════════════════════ --}}
<div id="modalConfirmation" class="fixed inset-0 z-60 hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" onclick="fermerModalConfirmation()"></div>
    <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md animate-modalIn">
                <div class="h-2 bg-gradient-to-r from-orange-500 to-red-500 rounded-t-2xl"></div>
                <div class="p-6 sm:p-8">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-orange-100 rounded-full flex items-center justify-center ring-8 ring-orange-50">
                            <svg class="w-8 h-8 sm:w-10 sm:h-10 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                    </div>

                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 text-center mb-1">Action Irréversible !</h2>
                    <p class="text-gray-500 text-center text-sm mb-4">Veuillez lire attentivement avant de confirmer</p>

                    <div class="bg-orange-50 border border-orange-200 rounded-xl p-3 mb-4">
                        <ul class="space-y-2 text-sm text-orange-800">
                            <li class="flex items-start gap-2">
                                <i class="fas fa-exclamation-circle text-orange-500 mt-0.5 flex-shrink-0"></i>
                                <span>Les élèves admis seront <strong>transférés</strong> dans leur classe choisie.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-exclamation-circle text-orange-500 mt-0.5 flex-shrink-0"></i>
                                <span>Les redoublants restent dans la même classe pour la nouvelle année.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-exclamation-circle text-orange-500 mt-0.5 flex-shrink-0"></i>
                                <span>Les paiements seront <strong>remis à zéro</strong> pour la nouvelle année.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-shield-alt text-orange-500 mt-0.5 flex-shrink-0"></i>
                                <span>Un <strong>snapshot</strong> des données sera conservé pour les archives.</span>
                            </li>
                        </ul>
                    </div>

                    <div id="confirmSummary" class="bg-indigo-50 border border-indigo-200 rounded-xl p-3 mb-5 text-sm text-indigo-800 max-h-48 overflow-y-auto">
                        {{-- Rempli dynamiquement --}}
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button onclick="fermerModalConfirmation()"
                                class="flex-1 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                            Annuler
                        </button>
                        <button id="btnConfirmerDeli"
                                onclick="executerDeliberation()"
                                class="flex-1 py-3 bg-gradient-to-r from-orange-500 to-red-600 text-white font-bold rounded-xl hover:from-orange-600 hover:to-red-700 transition-all shadow-lg flex items-center justify-center gap-2">
                            <i class="fas fa-gavel"></i>
                            Confirmer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════════════
     MODAL 4 : CONFIRMATION ANNULATION
═══════════════════════════════════════════════════════════════════ --}}
<div id="modalAnnulation" class="fixed inset-0 z-60 hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" onclick="fermerModalAnnulation()"></div>
    <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md animate-modalIn">
                <div class="h-2 bg-gradient-to-r from-red-600 to-rose-700 rounded-t-2xl"></div>
                <div class="p-6 sm:p-8">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-red-100 rounded-full flex items-center justify-center ring-8 ring-red-50">
                            <i class="fas fa-undo text-red-600 text-2xl sm:text-3xl"></i>
                        </div>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 text-center mb-1">Annuler la délibération ?</h2>
                    <p class="text-gray-500 text-center text-sm mb-4">Cette action restaurera tous les élèves à leur état précédent.</p>

                    <div class="bg-red-50 border border-red-200 rounded-xl p-3 mb-5">
                        <ul class="space-y-2 text-sm text-red-800">
                            <li class="flex items-start gap-2">
                                <i class="fas fa-undo text-red-500 mt-0.5 flex-shrink-0"></i>
                                <span>Les élèves seront replacés dans leurs classes et années d'origine.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 flex-shrink-0"></i>
                                <span>L'emploi du temps copié (si activé) sera également supprimé.</span>
                            </li>
                        </ul>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button onclick="fermerModalAnnulation()"
                                class="flex-1 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                            Ne pas annuler
                        </button>
                        <button id="btnConfirmerAnnulation"
                                onclick="executerAnnulation()"
                                class="flex-1 py-3 bg-gradient-to-r from-red-600 to-rose-700 text-white font-bold rounded-xl hover:from-red-700 hover:to-rose-800 transition-all shadow-lg flex items-center justify-center gap-2">
                            <i class="fas fa-undo"></i>
                            Confirmer l'annulation
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════════════
     MODAL 5 : SUCCÈS
═══════════════════════════════════════════════════════════════════ --}}
<div id="modalSucces" class="fixed inset-0 z-70 hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>
    <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm animate-modalIn">
                <div class="h-2 bg-gradient-to-r from-green-500 to-emerald-600 rounded-t-2xl"></div>
                <div class="p-6 text-center">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center ring-8 ring-green-50">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-1">Délibération Réussie !</h2>
                    <p class="text-gray-500 text-sm mb-4">Tous les transferts ont été effectués avec succès.</p>
                    <div id="succesStats" class="grid grid-cols-2 gap-3 mb-5">
                        {{-- Rempli dynamiquement --}}
                    </div>
                    <button onclick="location.reload()"
                            class="w-full py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all shadow-lg">
                        <i class="fas fa-sync-alt mr-2"></i>Actualiser la page
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- Modal bulletin trimestriel --}}
<div id="bulletinModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div id="bulletinOverlay" class="fixed inset-0 bg-blue-600 bg-opacity-60 transition-opacity" onclick="fermerBulletin()"></div>
    <div class="relative min-h-screen flex items-start justify-center p-4 pt-8">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-5xl z-10">
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
                <button onclick="fermerBulletin()" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="bulletinLoader" class="flex items-center justify-center py-20">
                <div class="text-center">
                    <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                    <p class="text-gray-500 text-sm">Chargement du bulletin...</p>
                </div>
            </div>
            <div id="bulletinContent" class="hidden p-6 overflow-x-auto"></div>
        </div>
    </div>
</div>


<style>
@keyframes modalIn {
    from { opacity: 0; transform: scale(0.92) translateY(-16px); }
    to   { opacity: 1; transform: scale(1)  translateY(0); }
}
.animate-modalIn { animation: modalIn 0.25s ease-out forwards; }
.z-60 { z-index: 60; }
.z-70 { z-index: 70; }
</style>


<script>
// ── Constantes ──────────────────────────────────────────────────────
const CLASSE_ID          = {{ $classe->id }};
const USER_ID            = {{ auth()->id() }};
const AUTHORIZED_IDS     = [6, 7];
const URL_MODAL_DATA     = "{{ route('censeur.deliberation.modal-data', $classe->id) }}";
const URL_DELIBERATE     = "{{ route('censeur.deliberation.store', $classe->id) }}";
const URL_CANCEL_BASE    = "{{ url('/censeur/deliberation') }}";
const CSRF               = "{{ csrf_token() }}";

let deliberationData     = { inactiveYears: [], targetClasses: [] };
let existingDeliberation = null;

// ── Helpers modal ────────────────────────────────────────────────────
function showModal(id)  { document.getElementById(id).classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
function hideModal(id)  { document.getElementById(id).classList.add('hidden'); document.body.style.overflow = ''; }

function fermerModalAccesRefuse()   { hideModal('modalAccesRefuse'); }
function fermerModalDeliberation()  { hideModal('modalDeliberation'); }
function fermerModalConfirmation()  { hideModal('modalConfirmation'); }
function fermerModalAnnulation()    { hideModal('modalAnnulation'); }

// ── Ouvrir modal accès refusé (depuis les boutons PDF) ───────────────
function ouvrirModalAccesRefuse() {
    showModal('modalAccesRefuse');
}

// ── Ouvrir le modal principal délibération ───────────────────────────
function ouvrirModalDeliberation() {
    if (!AUTHORIZED_IDS.includes(USER_ID)) {
        showModal('modalAccesRefuse');
        return;
    }

    showModal('modalDeliberation');
    document.getElementById('deliLoadingState').classList.remove('hidden');
    document.getElementById('deliFormState').classList.add('hidden');

    fetch(URL_MODAL_DATA, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            deliberationData = data;
            remplirFormulaire(data);
            document.getElementById('deliLoadingState').classList.add('hidden');
            document.getElementById('deliFormState').classList.remove('hidden');
        })
        .catch(err => {
            console.error(err);
            document.getElementById('deliLoadingState').innerHTML =
                '<p class="text-red-500 text-sm p-4">Erreur lors du chargement. Rechargez la page.</p>';
        });
}

// ── Remplir le formulaire ────────────────────────────────────────────
function remplirFormulaire(data) {
    // ── Années INACTIVES
    const selYear        = document.getElementById('selectTargetYear');
    const yearMissingMsg = document.getElementById('yearMissingMsg');
    selYear.innerHTML    = '<option value="">-- Sélectionnez l\'année inactive --</option>';

    if (!data.inactive_years || data.inactive_years.length === 0) {
        selYear.disabled = true;
        yearMissingMsg.classList.remove('hidden');
    } else {
        selYear.disabled = false;
        yearMissingMsg.classList.add('hidden');
        data.inactive_years.forEach(y => {
            const opt = document.createElement('option');
            opt.value = y.id;
            opt.textContent = y.name;
            selYear.appendChild(opt);
        });
    }

    // ── Classes disponibles (année active)
    const selClass        = document.getElementById('selectTargetClass');
    const classMissingMsg = document.getElementById('classMissingMsg');
    selClass.innerHTML    = '<option value="">-- Sélectionnez la classe par défaut --</option>';

    if (!data.target_classes || data.target_classes.length === 0) {
        selClass.disabled = true;
        classMissingMsg.classList.remove('hidden');
    } else {
        selClass.disabled = false;
        classMissingMsg.classList.add('hidden');
        data.target_classes.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.name;
            selClass.appendChild(opt);
        });
    }

    // ── Délibération existante ?
    existingDeliberation = data.existing_deliberation;
    const alertDiv = document.getElementById('deliExistingAlert');
    const deliForm = document.getElementById('deliForm');

    if (existingDeliberation) {
        alertDiv.classList.remove('hidden');
        document.getElementById('deliExistingInfo').textContent =
            `Délibération du ${existingDeliberation.deliberated_at} — ${existingDeliberation.passed_count} admis, ${existingDeliberation.repeated_count} redoublants.`;
        deliForm.querySelectorAll('select, input, button').forEach(el => el.disabled = true);
        document.getElementById('btnSoumettreDeli').classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        alertDiv.classList.add('hidden');
        deliForm.querySelectorAll('select, input, button').forEach(el => el.disabled = false);
        document.getElementById('btnSoumettreDeli').classList.remove('opacity-50', 'cursor-not-allowed');
    }
}

// ── Recalculer les statuts + reconstruire le tableau élèves ──────────
function recalculerStatuts() {
    appliquerClasseParDefaut();
}

// ── Construire le tableau élèves avec select individuel ──────────────
function appliquerClasseParDefaut() {
    const defaultClassId  = document.getElementById('selectTargetClass').value;
    const seuil           = parseFloat(document.getElementById('seuilPassage').value) || 10;
    const students        = deliberationData.students || [];
    const targetClasses   = deliberationData.target_classes || [];
    const tbody           = document.getElementById('tableauElevesDelib');

    if (students.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-3 py-6 text-center text-gray-400"><i class="fas fa-users-slash mr-1"></i>Aucun élève validé.</td></tr>';
        return;
    }

    // Options classes + option spéciale Diplômé
    const classOptionsHtml = targetClasses.map(c =>
        `<option value="${c.id}">${c.name}</option>`
    ).join('');

    const allOptionsHtml = classOptionsHtml +
        `<option value="diplome" class="text-purple-700 font-semibold"> Diplômé / Terminé</option>`;

    let admisCount = 0, redoublantCount = 0, diplomeCount = 0;
    let rows = '';

    students.forEach((s, idx) => {
        const moy   = s.moy_annuelle;
        const admis = moy !== null && moy >= seuil;
        if (admis) admisCount++; else redoublantCount++;

        const moyStr   = moy !== null ? moy.toFixed(2).replace('.', ',') : '—';
        const moyColor = moy !== null ? (admis ? 'text-green-700 font-bold' : 'text-red-700 font-bold') : 'text-gray-400';
        const statutBadge = admis
            ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800"><i class="fas fa-check mr-1"></i>Admis</span>'
            : '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800"><i class="fas fa-times mr-1"></i>Redouble</span>';

        let classeCell;
        if (admis) {
            // Pré-sélectionner la classe par défaut si choisie
            let opts = `<option value="">-- Choisir --</option>` + allOptionsHtml;
            if (defaultClassId) {
                opts = `<option value="">-- Choisir --</option>` +
                    allOptionsHtml.replace(`value="${defaultClassId}"`, `value="${defaultClassId}" selected`);
            }
            classeCell = `<select data-student-id="${s.id}"
                class="student-class-select w-full px-2 py-1 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-indigo-400 bg-white"
                onchange="onDestinationChange(this)">
                ${opts}
            </select>`;
        } else {
            classeCell = `<span class="text-gray-500 text-xs italic">Redouble (même classe)</span>`;
        }

        const rowBg = admis ? 'hover:bg-green-50' : 'bg-red-50 hover:bg-red-100';
        rows += `<tr class="${rowBg} transition-colors" data-student-id="${s.id}" data-admis="${admis ? 1 : 0}" data-dest="">
            <td class="px-3 py-2 text-gray-400 text-xs">${idx + 1}</td>
            <td class="px-3 py-2">
                <div class="font-semibold text-gray-800 text-xs">${s.full_name}</div>
                <div class="text-gray-400 text-xs">${s.num_educ || ''}</div>
            </td>
            <td class="px-3 py-2 text-center ${moyColor} text-xs">${moyStr}</td>
            <td class="px-3 py-2 text-center">${statutBadge}</td>
            <td class="px-3 py-2">${classeCell}</td>
        </tr>`;
    });

    tbody.innerHTML = rows;
    mettreAJourCompteurs();
}

// ── Quand on change la destination d'un élève ────────────────────────
function onDestinationChange(sel) {
    const row = sel.closest('tr');
    if (sel.value === 'diplome') {
        row.dataset.dest = 'diplome';
        row.classList.add('bg-purple-50');
        row.classList.remove('hover:bg-green-50');
    } else {
        row.dataset.dest = sel.value ? 'classe' : '';
        row.classList.remove('bg-purple-50');
        row.classList.add('hover:bg-green-50');
    }
    mettreAJourCompteurs();
}

// ── Mettre à jour les compteurs ──────────────────────────────────────
function mettreAJourCompteurs() {
    let admis = 0, redoublants = 0, diplomes = 0;
    document.querySelectorAll('#tableauElevesDelib tr[data-student-id]').forEach(row => {
        if (row.dataset.admis === '1') {
            const sel = row.querySelector('.student-class-select');
            if (sel?.value === 'diplome') diplomes++;
            else admis++;
        } else {
            redoublants++;
        }
    });
    document.getElementById('compteurAdmis').textContent       = admis + ' admis';
    document.getElementById('compteurRedoublants').textContent = redoublants + ' redoublants';
    // Afficher/cacher le compteur diplômés dynamiquement
    let diplomeEl = document.getElementById('compteurDiplomes');
    if (!diplomeEl) {
        diplomeEl = document.createElement('span');
        diplomeEl.id = 'compteurDiplomes';
        diplomeEl.className = 'bg-purple-100 text-purple-700 font-bold px-2 py-1 rounded-full text-xs';
        document.getElementById('compteurAdmis').parentElement.appendChild(diplomeEl);
    }
    diplomeEl.textContent = diplomes + ' diplômé(s)';
    diplomeEl.classList.toggle('hidden', diplomes === 0);
}

// ── Demander confirmation avant délibération ─────────────────────────
function demanderConfirmation() {
    const yearId = document.getElementById('selectTargetYear').value;
    const seuil  = parseFloat(document.getElementById('seuilPassage').value) || 10;
    const keepTt = document.getElementById('keepTimetable').checked;

    if (!yearId) { alert('Veuillez sélectionner une année académique de destination.'); return; }

    // Vérifier que tous les admis ont une destination (classe ou diplôme)
    const admisRows = document.querySelectorAll('#tableauElevesDelib tr[data-admis="1"]');
    for (const row of admisRows) {
        const sel = row.querySelector('.student-class-select');
        if (sel && !sel.value) {
            const name = row.querySelector('td:nth-child(2) .font-semibold')?.textContent || 'un élève';
            alert(`Veuillez choisir une destination pour ${name}.`);
            return;
        }
    }

    const yearName = document.getElementById('selectTargetYear').options[document.getElementById('selectTargetYear').selectedIndex].text;

    // Résumé des affectations
    const affectations = {};
    let diplomeCount = 0, redoublantCount = 0;

    document.querySelectorAll('#tableauElevesDelib tr[data-student-id]').forEach(row => {
        if (row.dataset.admis === '1') {
            const sel = row.querySelector('.student-class-select');
            if (sel?.value === 'diplome') {
                diplomeCount++;
            } else {
                const className = sel?.options[sel.selectedIndex]?.text || '?';
                affectations[className] = (affectations[className] || 0) + 1;
            }
        } else {
            redoublantCount++;
        }
    });

    const admisCount = admisRows.length - diplomeCount;
    const affectationsHtml = Object.entries(affectations)
        .map(([cls, count]) => `<div class="flex justify-between"><span>${cls}</span><span class="font-bold text-green-700">${count} élève(s)</span></div>`)
        .join('');

    document.getElementById('confirmSummary').innerHTML = `
        <div class="space-y-1.5 text-sm">
            <div class="flex justify-between"><span class="font-medium">Classe source :</span><span>{{ $classe->name }}</span></div>
            <div class="flex justify-between"><span class="font-medium">Année destination :</span><span>${yearName}</span></div>
            <div class="flex justify-between"><span class="font-medium">Seuil :</span><span>${seuil}/20</span></div>
            <div class="flex justify-between"><span class="font-medium">Emploi du temps :</span><span>${keepTt ? 'Copié ✓' : 'Non copié'}</span></div>
            <hr class="border-indigo-200 my-2">
            <div class="font-semibold text-gray-700 mb-1">Admis → classes :</div>
            ${affectationsHtml || '<div class="text-gray-400 text-xs">Aucun</div>'}
            <hr class="border-indigo-200 my-2">
            <div class="flex justify-between font-bold text-green-700"><span>Admis (classe sup.) :</span><span>${admisCount}</span></div>
            ${diplomeCount > 0 ? `<div class="flex justify-between font-bold text-purple-700"><span>Diplômés / Terminés :</span><span>${diplomeCount}</span></div>` : ''}
            <div class="flex justify-between font-bold text-red-700"><span>Redoublants :</span><span>${redoublantCount}</span></div>
        </div>
    `;

    hideModal('modalDeliberation');
    showModal('modalConfirmation');
}

// ── Exécuter la délibération ─────────────────────────────────────────
function executerDeliberation() {
    const yearId = document.getElementById('selectTargetYear').value;
    const seuil  = parseFloat(document.getElementById('seuilPassage').value) || 10;
    const keepTt = document.getElementById('keepTimetable').checked;
    const btn    = document.getElementById('btnConfirmerDeli');

    // Collecter les affectations par élève
    // target_class_id = ID (classe), "diplome" (diplômé), ou null (redoublant)
    const studentAssignments = [];
    document.querySelectorAll('#tableauElevesDelib tr[data-student-id]').forEach(row => {
        const studentId = parseInt(row.dataset.studentId);
        const admis     = row.dataset.admis === '1';
        const sel       = row.querySelector('.student-class-select');
        let   classId   = null;

        if (admis && sel) {
            classId = sel.value === 'diplome' ? 'diplome' : (sel.value || null);
        }
        studentAssignments.push({ student_id: studentId, target_class_id: classId });
    });

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>En cours...';

    fetch(URL_DELIBERATE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({
            target_academic_year_id: yearId,
            seuil_passage: seuil,
            keep_timetable: keepTt,
            student_assignments: studentAssignments,
        }),
    })
    .then(r => r.text().then(text => {
        try { return JSON.parse(text); }
        catch(e) { return { success: false, error: 'Erreur serveur. ' + text.substring(0, 200) }; }
    }))
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-gavel mr-2"></i>Confirmer';

        if (data.success) {
            hideModal('modalConfirmation');
            document.getElementById('succesStats').innerHTML = `
                <div class="bg-green-50 border border-green-200 rounded-xl p-3">
                    <div class="text-2xl font-bold text-green-700">${data.passed_count}</div>
                    <div class="text-xs text-green-600 mt-1">Admis</div>
                </div>
                <div class="bg-red-50 border border-red-200 rounded-xl p-3">
                    <div class="text-2xl font-bold text-red-700">${data.repeated_count}</div>
                    <div class="text-xs text-red-600 mt-1">Redoublants</div>
                </div>
                ${data.graduated_count > 0 ? `
                <div class="bg-purple-50 border border-purple-200 rounded-xl p-3 col-span-2">
                    <div class="text-2xl font-bold text-purple-700">${data.graduated_count}</div>
                    <div class="text-xs text-purple-600 mt-1"> Diplômés / Terminés</div>
                </div>` : ''}
            `;
            showModal('modalSucces');
        } else {
            alert('Erreur : ' + (data.error || 'Une erreur est survenue.'));
            showModal('modalConfirmation');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-gavel mr-2"></i>Confirmer';
        alert('Erreur réseau. Veuillez réessayer.');
        console.error(err);
    });
}

// ── Demander / Exécuter l'annulation ────────────────────────────────
function demanderAnnulation()   { showModal('modalAnnulation'); }
function fermerModalAnnulation(){ hideModal('modalAnnulation'); }

function executerAnnulation() {
    if (!existingDeliberation) return;

    const btn = document.getElementById('btnConfirmerAnnulation');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Annulation...';

    fetch(`${URL_CANCEL_BASE}/${existingDeliberation.id}/cancel`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({}),
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-undo mr-2"></i>Confirmer l\'annulation';

        if (data.success) {
            hideModal('modalAnnulation');
            hideModal('modalDeliberation');
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('Erreur : ' + (data.error || 'Une erreur est survenue.'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-undo mr-2"></i>Confirmer l\'annulation';
        alert('Erreur réseau. Veuillez réessayer.');
        console.error(err);
    });
}

// ── Bulletin trimestriel ────────────────────────────────────────────
function ouvrirBulletin(studentId, trimestre) {
    const modal    = document.getElementById('bulletinModal');
    const loader   = document.getElementById('bulletinLoader');
    const content  = document.getElementById('bulletinContent');
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
        content.innerHTML = data.error
            ? `<div class="text-center py-10 text-red-600"><i class="fas fa-exclamation-triangle text-3xl mb-3"></i><p>${data.error}</p></div>`
            : data.html;
        loader.classList.add('hidden');
        content.classList.remove('hidden');
        subtitle.textContent = 'Trimestre ' + trimestre;
    })
    .catch(() => {
        content.innerHTML = `<div class="text-center py-10 text-red-600"><i class="fas fa-exclamation-triangle text-3xl mb-3 block"></i><p>Erreur lors du chargement.</p></div>`;
        loader.classList.add('hidden');
        content.classList.remove('hidden');
    });
}

function fermerBulletin() {
    document.getElementById('bulletinModal').classList.add('hidden');
    document.getElementById('bulletinContent').innerHTML = '';
}

// ── Fermer avec Échap ────────────────────────────────────────────────
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        fermerBulletin();
        fermerModalConfirmation();
        fermerModalAnnulation();
        fermerModalAccesRefuse();
        fermerModalDeliberation();
    }
});
</script>
@endsection