@extends('layouts.app')
@section('content')
@php
    $pageTitle = 'Archives – ' . $year->name;
    $user = auth()->user();
@endphp

<div class="bg-white p-6 rounded-xl shadow-sm">
    {{-- En-tête avec animation ----------------------------------------------------------------}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8 animate-fadeIn">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Archives – {{ $year->name }}</h1>
            <p class="text-sm text-gray-500 mt-1 flex items-center gap-2">
                <span class="inline-block w-1 h-1 bg-blue-500 rounded-full"></span>
                Données de l'année académique terminée
                <span class="inline-block w-1 h-1 bg-blue-500 rounded-full"></span>
            </p>
        </div>
        <a href="{{ route('archives.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition-all duration-300 text-sm border border-gray-200 hover:border-gray-300 hover:shadow-sm group">
            <i class="fas fa-arrow-left text-xs group-hover:-translate-x-1 transition-transform duration-300"></i> 
            Retour aux archives
        </a>
    </div>

    {{-- Stats globales de paiement avec animations ---------------------------------------}}
    @if($canViewPayments && $paymentStats)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-10">
            <div class="bg-white border-l-4 border-blue-500 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 animate-slideUp" style="animation-delay: 0s;">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-blue-600">{{ $paymentStats['total_students'] }}</div>
                        <div class="text-xs text-gray-500 mt-1 uppercase tracking-wide">Élèves inscrits</div>
                    </div>
                    <i class="fas fa-users text-blue-200 text-3xl"></i>
                </div>
            </div>
            <div class="bg-white border-l-4 border-green-500 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 animate-slideUp" style="animation-delay: 0.1s;">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-green-600">{{ number_format($paymentStats['total_paid'], 0, ',', ' ') }}</div>
                        <div class="text-xs text-gray-500 mt-1 uppercase tracking-wide">FCFA encaissés</div>
                    </div>
                    <i class="fas fa-money-bill-wave text-green-200 text-3xl"></i>
                </div>
            </div>
            <div class="bg-white border-l-4 border-red-500 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 animate-slideUp" style="animation-delay: 0.2s;">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-red-600">{{ number_format($paymentStats['total_remaining'], 0, ',', ' ') }}</div>
                        <div class="text-xs text-gray-500 mt-1 uppercase tracking-wide">FCFA restants</div>
                    </div>
                    <i class="fas fa-chart-line text-red-200 text-3xl"></i>
                </div>
            </div>
            <div class="bg-white border-l-4 border-purple-500 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 animate-slideUp" style="animation-delay: 0.3s;">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-purple-600">{{ $paymentStats['rate'] }}%</div>
                        <div class="text-xs text-gray-500 mt-1 uppercase tracking-wide">Taux de paiement</div>
                    </div>
                    <i class="fas fa-percent text-purple-200 text-3xl"></i>
                </div>
                <div class="w-full bg-purple-100 rounded-full h-2 mt-3">
                    <div class="bg-purple-500 h-2 rounded-full transition-all duration-1000" style="width: {{ $paymentStats['rate'] }}%"></div>
                </div>
            </div>
        </div>
    @endif

    {{-- Grille de classes avec animations ----------------------------------------}}
    @if($classes->isEmpty())
        <div class="text-center py-16 animate-fadeIn">
            <div class="inline-block p-6 bg-gray-50 rounded-full mb-4">
                <i class="fas fa-folder-open text-5xl text-gray-300"></i>
            </div>
            <p class="text-gray-500 text-lg">Aucune classe disponible pour cette archive</p>
            <p class="text-gray-400 text-sm mt-2">Veuillez sélectionner une autre année académique</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($classes as $index => $class)
                <div class="group bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-2 border border-gray-100 overflow-hidden animate-cardReveal" style="animation-delay: {{ $index * 0.05 }}s">
                    <div class="p-6">
                        {{-- Titre avec badge d'entité --}}
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h2 class="text-xl font-bold text-gray-800 group-hover:text-blue-600 transition-colors duration-300">{{ $class->name }}</h2>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-block w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                    <span class="text-xs text-gray-500">{{ $class->entity->name ?? 'Non assigné' }}</span>
                                </div>
                            </div>
                            <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center group-hover:bg-blue-100 transition-colors duration-300">
                                <i class="fas fa-graduation-cap text-blue-500 text-sm"></i>
                            </div>
                        </div>

                        {{-- Effectif archivé --}}
                        <div class="flex items-center gap-2 mb-3 p-2 bg-gray-50 rounded-lg">
                            <i class="fas fa-users text-blue-400 text-sm"></i>
                            <span class="text-sm text-gray-600">Effectif archivé :</span>
                            <span class="text-sm font-semibold text-gray-800 ml-auto">{{ $class->studentsCount }} élève(s)</span>
                        </div>

                        {{-- Résultats académiques --}}
                        @php
                            $admis = \App\Models\StudentAcademicRecord::where('academic_year_id', $year->id)
                                ->where('class_id', $class->id)
                                ->where('statut_deliberation', 'passed')
                                ->count();

                            $redoub = \App\Models\StudentAcademicRecord::where('academic_year_id', $year->id)
                                ->where('class_id', $class->id)
                                ->where('statut_deliberation', 'repeated')
                                ->count();
                        @endphp
                        @if($admis + $redoub > 0)
                            <div class="flex gap-3 mb-4">
                                <div class="flex-1 flex items-center gap-2 px-2 py-1.5 bg-green-50 rounded-lg">
                                    <i class="fas fa-check-circle text-green-500 text-xs"></i>
                                    <span class="text-xs text-green-700 font-medium">{{ $admis }} admis</span>
                                </div>
                                <div class="flex-1 flex items-center gap-2 px-2 py-1.5 bg-red-50 rounded-lg">
                                    <i class="fas fa-sync-alt text-red-500 text-xs"></i>
                                    <span class="text-xs text-red-600 font-medium">{{ $redoub }} redoublant(s)</span>
                                </div>
                            </div>
                        @endif

                        {{-- Paiements par classe --}}
                        @if($canViewPayments)
                            @php
                                $records = \App\Models\StudentAcademicRecord::where('academic_year_id', $year->id)->where('class_id', $class->id)->get();
                                $pTotal  = $records->sum('total_fees');
                                $pPaid   = $records->sum('amount_paid');
                                $pRate   = $pTotal > 0 ? round(($pPaid / $pTotal) * 100) : 0;
                            @endphp
                            @if($pTotal > 0)
                                <div class="mb-4">
                                    <div class="flex justify-between text-xs text-gray-600 mb-1.5">
                                        <span class="font-medium">Taux de paiement</span>
                                        <span class="font-semibold 
                                            {{ $pRate >= 80 ? 'text-green-600' : ($pRate >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ $pRate }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                        <div class="h-2 rounded-full transition-all duration-1000 
                                            {{ $pRate >= 80 ? 'bg-green-500' : ($pRate >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                            style="width: 0%"
                                            data-width="{{ $pRate }}%">
                                        </div>
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-400 mt-1.5">
                                        <span>{{ number_format($pPaid, 0, ',', ' ') }} FCFA</span>
                                        <span>{{ number_format($pTotal, 0, ',', ' ') }} FCFA</span>
                                    </div>
                                </div>
                            @endif
                        @endif

                        {{-- Boutons d'action --}}
                        <div class="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-gray-100">
                            <a href="{{ route('archives.classes.students', [$year->id, $class->id]) }}"
                               class="text-center px-2 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-600 hover:text-white transition-all duration-300 text-sm font-medium group/btn">
                                <i class="fas fa-users mr-1 group-hover/btn:scale-110 inline-block transition-transform"></i> 
                                <span class="hidden sm:inline">Élèves</span>
                            </a>

                            @if($canViewTimetable)
                                <a href="{{ route('archives.class_timetables', [$year->id, $class->id]) }}"
                                   class="text-center px-2 py-2 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-600 hover:text-white transition-all duration-300 text-sm font-medium group/btn">
                                    <i class="fas fa-calendar-alt mr-1 group-hover/btn:scale-110 inline-block transition-transform"></i> 
                                    <span class="hidden sm:inline">Emploi du Temps</span>
                                </a>
                            @endif

                            @if($canViewNotes)
                                <a href="{{ route('archives.class_notes', [$year->id, $class->id]) }}"
                                   class="text-center px-2 py-2 bg-green-50 text-green-700 rounded-lg hover:bg-green-600 hover:text-white transition-all duration-300 text-sm font-medium group/btn">
                                    <i class="fas fa-chart-bar mr-1 group-hover/btn:scale-110 inline-block transition-transform"></i> 
                                    <span class="hidden sm:inline">Notes</span>
                                </a>
                            @endif

                            @if($canViewPayments)
                                <a href="{{ route('archives.class_payment_stats', [$year->id, $class->id]) }}"
                                   class="text-center px-2 py-2 bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-600 hover:text-white transition-all duration-300 text-sm font-medium group/btn">
                                    <i class="fas fa-money-bill-wave mr-1 group-hover/btn:scale-110 inline-block transition-transform"></i> 
                                    <span class="hidden sm:inline">Paiements</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes cardReveal {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.6s ease-out;
}

.animate-slideUp {
    animation: slideUp 0.5s ease-out forwards;
    opacity: 0;
}

.animate-cardReveal {
    animation: cardReveal 0.4s ease-out forwards;
    opacity: 0;
}

/* Animation hover smooth */
.group:hover .group-hover\:translate-x-1 {
    transform: translateX(4px);
}

/* Transition smooth pour toutes les interactions */
* {
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Scrollbar personnalisée */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

<script>
// Animation pour les barres de progression
document.addEventListener('DOMContentLoaded', function() {
    const progressBars = document.querySelectorAll('[data-width]');
    progressBars.forEach(bar => {
        const width = bar.getAttribute('data-width');
        setTimeout(() => {
            bar.style.width = width;
        }, 200);
    });
});
</script>
@endsection