@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Carte principale du bulletin -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            
            <!-- En-tête avec fond coloré -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-6">
                <div class="text-center text-white">
                    <h1 class="text-3xl font-bold mb-2">BULLETIN SCOLAIRE</h1>
                    <div class="flex flex-col sm:flex-row justify-center items-center gap-4 text-blue-100">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            <span>Trimestre {{ $trimestre }}</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            <span>{{ $classe->academicYear->name ?? '' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations élève -->
            <div class="px-8 py-6 border-b border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-user text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Élève</p>
                            <p class="font-semibold text-gray-900">{{ strtoupper($student->last_name) }} {{ ucfirst($student->first_name) }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-id-card text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Matricule</p>
                            <p class="font-semibold text-gray-900">{{ $student->num_educ ?? '—' }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-users text-purple-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Classe</p>
                            <p class="font-semibold text-gray-900">{{ $classe->name }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-mars-and-venus text-orange-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Sexe</p>
                            <p class="font-semibold text-gray-900">{{ strtoupper($student->gender ?? '-') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableau des notes -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr class="border-b border-gray-200">
                            <th rowspan="2" class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Matière</th>
                            <th rowspan="2" class="px-4 py-4 text-center text-sm font-semibold text-gray-700">Coef</th>
                            <th colspan="5" class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-l border-gray-200">
                                Interrogations
                            </th>
                            <th colspan="2" class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-l border-gray-200">
                                Devoirs
                            </th>
                            <th rowspan="2" class="px-4 py-4 text-center text-sm font-semibold text-gray-700 border-l border-gray-200">Moyenne</th>
                            <th rowspan="2" class="px-4 py-4 text-center text-sm font-semibold text-gray-700">Moy x Coef</th>
                            <th rowspan="2" class="px-4 py-4 text-center text-sm font-semibold text-gray-700 border-l border-gray-200">Appréciation</th>
                        </tr>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-3 py-2 text-xs font-medium text-gray-500">I1</th>
                            <th class="px-3 py-2 text-xs font-medium text-gray-500">I2</th>
                            <th class="px-3 py-2 text-xs font-medium text-gray-500">I3</th>
                            <th class="px-3 py-2 text-xs font-medium text-gray-500">I4</th>
                            <th class="px-3 py-2 text-xs font-medium text-gray-500 border-r border-gray-200">I5</th>
                            <th class="px-3 py-2 text-xs font-medium text-gray-500">D1</th>
                            <th class="px-3 py-2 text-xs font-medium text-gray-500 border-r border-gray-200">D2</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($bulletin as $ligne)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 text-left font-medium text-gray-900">{{ $ligne['subject'] }}</td>
                            <td class="px-4 py-4 text-center text-gray-600">{{ $ligne['coef'] }}</td>
                            
                            {{-- Interrogations --}}
                            @for ($i = 1; $i <= 5; $i++)
                                <td class="px-3 py-4 text-center {{ $i === 5 ? 'border-r border-gray-200' : '' }}">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full 
                                        {{ isset($ligne['interros'][$i]) ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-400' }}">
                                        {{ $ligne['interros'][$i] ?? '-' }}
                                    </span>
                                </td>
                            @endfor

                            {{-- Devoirs --}}
                            <td class="px-3 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full 
                                    {{ isset($ligne['devoirs'][1]) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                                    {{ $ligne['devoirs'][1] ?? '-' }}
                                </span>
                            </td>
                            <td class="px-3 py-4 text-center border-r border-gray-200">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full 
                                    {{ isset($ligne['devoirs'][2]) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                                    {{ $ligne['devoirs'][2] ?? '-' }}
                                </span>
                            </td>

                            {{-- Moyennes --}}
                            <td class="px-4 py-4 text-center border-r border-gray-200">
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-sm font-semibold
                                    {{ isset($ligne['moyenne']) ? 
                                       ($ligne['moyenne'] >= 10 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') : 
                                       'bg-gray-100 text-gray-600' }}">
                                    {{ $ligne['moyenne'] ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center font-semibold text-gray-700">
                                {{ $ligne['moyCoeff'] ?? '-' }}
                            </td>
                            <td class="px-4 py-4 text-center border-l border-gray-200 italic text-gray-600">
                                {{ $ligne['appreciation'] ?? '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Résultats généraux -->
            <div class="bg-gray-50 border-t border-gray-200 px-8 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-sm text-gray-500 mb-1">Conduite</div>
                        <div class="text-2xl font-bold text-blue-600">{{ $conduiteFinale ?? '-' }}/20</div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-sm text-gray-500 mb-1">Moyenne Générale</div>
                        <div class="text-2xl font-bold 
                            {{ isset($moyenneGenerale) ? 
                               ($moyenneGenerale >= 10 ? 'text-green-600' : 'text-red-600') : 
                               'text-gray-600' }}">
                            {{ $moyenneGenerale ?? '-' }}/20
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-sm text-gray-500 mb-1">Rang Général</div>
                        <div class="text-2xl font-bold text-purple-600">
                            {{ $rang ?? '-' }}/{{ $effectif->students->count() }}
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-sm text-gray-500 mb-1">Appréciation</div>
                        <div class="text-lg font-semibold text-gray-700">{{ $appreciationGenerale ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bouton d'action -->
        <div class="flex justify-end">
            <a href="{{ route('censeur.classes.notes.bulletin.pdf', [$classe->id, $student->id, $trimestre]) }}"
               class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 shadow-sm">
                <i class="fas fa-file-pdf mr-2"></i>
                Télécharger le bulletin PDF
            </a>
        </div>
    </div>
</div>

<!-- Styles pour le responsive -->
<style>
    @media (max-width: 768px) {
        table {
            font-size: 0.75rem;
        }
        .px-6 {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .px-8 {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation d'apparition progressive
        const cards = document.querySelectorAll('.bg-white');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>
@endsection