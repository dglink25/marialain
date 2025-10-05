@extends('layouts.app')

@section('content')
@php
    $pageTitle = "Récapitulatif Notes";
@endphp

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- En-tête principal -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Récapitulatif des Notes</h1>
                <div class="flex flex-wrap justify-center items-center gap-4 text-gray-600 mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-users mr-2"></i>
                        <span>Classe {{ $classe->name }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <span>Trimestre {{ $trimestre }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        <span>{{ $activeYear->name ?? $activeYear->label ?? 'N/A' }}</span>
                    </div>
                </div>
                
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200 inline-block">
                    <h2 class="text-lg font-semibold text-blue-800">
                        <i class="fas fa-book mr-2"></i>
                        {{ $subjects->name }} 
                        <span class="text-blue-600">(Coefficient : {{ $subjects->coefficient ?? 1 }})</span>
                    </h2>
                </div>
            </div>

            <!-- Bouton d'export -->
            <div class="flex justify-center mt-6">
                <a href="{{ route('censeur.notes.export.pdf', [$classe->id, $trimestre, $subjects->id]) }}" 
                   class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 shadow-sm font-medium">
                    <i class="fas fa-file-pdf mr-2"></i>
                    Télécharger le PDF
                </a>
            </div>
        </div>

        <!-- Messages flash améliorés -->
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <span class="text-red-700">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Tableau responsive -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr class="border-b border-gray-200">
                            <th rowspan="2" class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-r border-gray-200">Élève</th>
                            <th colspan="5" class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-r border-gray-200">
                                Interrogations
                            </th>
                            <th rowspan="2" class="px-3 py-3 text-center text-sm font-semibold text-gray-700 border-r border-gray-200">Moy. I</th>
                            <th rowspan="2" class="px-3 py-3 text-center text-sm font-semibold text-gray-700 border-r border-gray-200">Coef</th>
                            <th colspan="2" class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-r border-gray-200">
                                Devoirs
                            </th>
                            <th rowspan="2" class="px-3 py-3 text-center text-sm font-semibold text-gray-700 border-r border-gray-200">Moy/20</th>
                            <th rowspan="2" class="px-3 py-3 text-center text-sm font-semibold text-gray-700 border-r border-gray-200">Moy.Coef</th>
                            <th rowspan="2" class="px-3 py-3 text-center text-sm font-semibold text-gray-700">Rang</th>
                        </tr>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-2 py-2 text-xs font-medium text-gray-500">I1</th>
                            <th class="px-2 py-2 text-xs font-medium text-gray-500">I2</th>
                            <th class="px-2 py-2 text-xs font-medium text-gray-500">I3</th>
                            <th class="px-2 py-2 text-xs font-medium text-gray-500">I4</th>
                            <th class="px-2 py-2 text-xs font-medium text-gray-500 border-r border-gray-200">I5</th>
                            <th class="px-2 py-2 text-xs font-medium text-gray-500">D1</th>
                            <th class="px-2 py-2 text-xs font-medium text-gray-500 border-r border-gray-200">D2</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($classe->students as $student)
                            @php
                                $grades = $gradesData[$student->id][$subjects->id] ?? null;
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <!-- Informations élève -->
                                <td class="px-4 py-3 border-r border-gray-200">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-blue-600 text-sm font-semibold">{{ $loop->iteration }}</span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">
                                                {{ $student->last_name }} {{ $student->first_name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $student->gender }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Interrogations -->
                                @for($i = 1; $i <= 5; $i++)
                                    <td class="px-2 py-2 text-center text-sm border-r {{ $i === 5 ? 'border-gray-200' : '' }} 
                                        {{ isset($grades['interros'][$i]) ? 'text-gray-700' : 'text-gray-400' }}">
                                        {{ $grades['interros'][$i] ?? '-' }}
                                    </td>
                                @endfor

                                <!-- Moyenne Interros -->
                                <td class="px-3 py-2 text-center text-sm font-semibold border-r border-gray-200
                                    {{ isset($grades['moyenneInterro']) ? 'text-blue-600' : 'text-gray-400' }}">
                                    {{ $grades['moyenneInterro'] ?? '-' }}
                                </td>

                                <!-- Coefficient -->
                                <td class="px-3 py-2 text-center text-sm text-gray-600 border-r border-gray-200">
                                    {{ $grades['coef'] ?? ($subjects->coefficient ?? 1) }}
                                </td>

                                <!-- Devoirs -->
                                @for($i = 1; $i <= 2; $i++)
                                    <td class="px-2 py-2 text-center text-sm border-r {{ $i === 2 ? 'border-gray-200' : '' }}
                                        {{ isset($grades['devoirs'][$i]) ? 'text-gray-700' : 'text-gray-400' }}">
                                        {{ $grades['devoirs'][$i] ?? '-' }}
                                    </td>
                                @endfor

                                <!-- Moyennes -->
                                <td class="px-3 py-2 text-center text-sm font-bold border-r border-gray-200
                                    {{ isset($grades['moyenne']) ? 'text-blue-600' : 'text-gray-400' }}">
                                    {{ $grades['moyenne'] ?? '-' }}
                                </td>
                                <td class="px-3 py-2 text-center text-sm font-bold border-r border-gray-200
                                    {{ isset($grades['moyenneMat']) ? 'text-blue-600' : 'text-gray-400' }}">
                                    {{ $grades['moyenneMat'] ?? '-' }}
                                </td>
                                <td class="px-3 py-2 text-center text-sm font-bold
                                    {{ isset($grades['rang']) ? 'text-purple-600' : 'text-gray-400' }}">
                                    {{ $grades['rang'] ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Message si aucun élève -->
            @if($classe->students->count() === 0)
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-gray-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun élève dans cette classe</h3>
                <p class="text-gray-500">Les élèves apparaîtront ici une fois inscrits.</p>
            </div>
            @endif
        </div>

        <!-- Légende -->
        <div class="mt-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="flex flex-wrap items-center justify-center gap-4 text-sm text-gray-600">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-blue-500 rounded mr-2"></div>
                    <span>Moyennes calculées</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-minus text-gray-400 mr-2"></i>
                    <span>Note non saisie</span>
                </div>
            </div>
        </div>
        <br>
        <!-- Bouton retour intelligent -->
        <div class="mb-6">
            <button onclick="smartBack()" 
                    class="inline-flex items-center px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200 shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour
            </button>
        </div>

    </div>
</div>

<!-- Styles pour le responsive -->
<style>
    @media (max-width: 768px) {
        table {
            font-size: 0.75rem;
        }
        .px-4 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        .px-3 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        .px-2 {
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }
    }
    
    @media (max-width: 640px) {
        .text-3xl {
            font-size: 1.5rem;
        }
        .text-lg {
            font-size: 1rem;
        }
    }
</style>

<!-- Script pour le bouton retour intelligent -->
<script>
    function smartBack() {
        // Vérifie si on peut revenir en arrière dans l'historique
        if (document.referrer && document.referrer !== window.location.href) {
            history.back();
        } else {
            // Redirection vers une page par défaut (liste des matières)
            window.location.href = "{{ route('censeur.classes.trimestre.matiere', [$classe->id, $trimestre]) }}";
        }
    }

    // Raccourci clavier Alt + ← pour le retour
    document.addEventListener('keydown', function(e) {
        if (e.altKey && e.key === 'ArrowLeft') {
            smartBack();
        }
    });

    // Animation d'apparition
    document.addEventListener('DOMContentLoaded', function() {
        const elements = document.querySelectorAll('.bg-white');
        elements.forEach((element, index) => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(10px)';
            
            setTimeout(() => {
                element.style.transition = 'all 0.4s ease';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>
@endsection