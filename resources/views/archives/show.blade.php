@extends('layouts.app')
@section('content')
@php
    $pageTitle = 'Archives – ' . $year->name;
    $user = auth()->user();
@endphp

<div class="bg-white p-6 rounded-xl shadow">

    {{-- En-tête ----------------------------------------------------------------}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Archives – {{ $year->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">Données de l'année académique terminée</p>
        </div>
        <a href="{{ route('archives.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm">
            <i class="fas fa-arrow-left"></i> Retour aux archives
        </a>
    </div>

    {{-- Stats globales de paiement (admin / secrétaire) -----------------------}}
    @if($canViewPayments && $paymentStats)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-blue-700">{{ $paymentStats['total_students'] }}</div>
                <div class="text-xs text-blue-600 mt-1">Élèves inscrits</div>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-green-700">{{ number_format($paymentStats['total_paid'], 0, ',', ' ') }}</div>
                <div class="text-xs text-green-600 mt-1">FCFA encaissés</div>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-red-700">{{ number_format($paymentStats['total_remaining'], 0, ',', ' ') }}</div>
                <div class="text-xs text-red-600 mt-1">FCFA restants</div>
            </div>
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-purple-700">{{ $paymentStats['rate'] }}%</div>
                <div class="text-xs text-purple-600 mt-1">Taux de paiement</div>
                <div class="w-full bg-purple-200 rounded-full h-1.5 mt-2">
                    <div class="bg-purple-600 h-1.5 rounded-full" style="width: {{ $paymentStats['rate'] }}%"></div>
                </div>
            </div>
        </div>
    @endif

    {{-- Grille de classes --------------------------------------------------------}}
    @if($classes->isEmpty())
        <p class="text-gray-500 text-center py-12">
            <i class="fas fa-folder-open text-4xl mb-3 block text-gray-300"></i>
            Aucune classe disponible pour cet utilisateur dans cette archive.
        </p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($classes as $class)
                <div class="border rounded-2xl shadow-sm hover:shadow-md transition bg-white flex flex-col">
                    <div class="p-5 flex flex-col h-full">

                        {{-- Titre -----------------------------------------------}}
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-lg font-bold text-gray-800">{{ $class->name }}</h2>
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">
                                {{ $class->entity->name ?? '-' }}
                            </span>
                        </div>

                        {{-- Effectif via records archivés -------------------------}}
                        <p class="text-sm text-gray-600 mb-1">
                            <i class="fas fa-users text-gray-400 w-4 mr-1"></i>
                            Effectif archivé : <span class="font-semibold">{{ $class->studentsCount }}</span> élève(s)
                        </p>

                        {{-- Stats résultats (admis / redoublants) ----------------}}
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
                            <div class="flex gap-3 text-xs mt-1 mb-2">
                                <span class="text-green-700 font-semibold">✓ {{ $admis }} admis</span>
                                <span class="text-red-600 font-semibold">↻ {{ $redoub }} redoublant(s)</span>
                            </div>
                        @endif

                        {{-- Stats paiement par classe (admin/sec) ----------------}}
                        @if($canViewPayments)
                            @php
                                $records = \App\Models\StudentAcademicRecord::where('academic_year_id', $year->id)->where('class_id', $class->id)->get();
                                $pTotal  = $records->sum('total_fees');
                                $pPaid   = $records->sum('amount_paid');
                                $pRate   = $pTotal > 0 ? round(($pPaid / $pTotal) * 100) : 0;
                            @endphp
                            @if($pTotal > 0)
                                <div class="mt-2 mb-3">
                                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                                        <span>Paiements</span>
                                        <span class="font-semibold {{ $pRate >= 80 ? 'text-green-600' : ($pRate >= 50 ? 'text-yellow-600' : 'text-red-600') }}">{{ $pRate }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full {{ $pRate >= 80 ? 'bg-green-500' : ($pRate >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                             style="width: {{ $pRate }}%"></div>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        {{ number_format($pPaid, 0, ',', ' ') }} / {{ number_format($pTotal, 0, ',', ' ') }} FCFA
                                    </div>
                                </div>
                            @endif
                        @endif

                        {{-- Boutons d'action ------------------------------------}}
                        <div class="mt-auto flex flex-wrap gap-2">
                            <a href="{{ route('archives.classes.students', [$year->id, $class->id]) }}"
                               class="flex-1 text-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                                <i class="fas fa-users mr-1"></i> Élèves
                            </a>

                            @if($canViewTimetable)
                                <a href="{{ route('archives.class_timetables', [$year->id, $class->id]) }}"
                                   class="flex-1 text-center px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                                    <i class="fas fa-calendar-alt mr-1"></i> EDT
                                </a>
                            @endif

                            @if($canViewNotes)
                                <a href="{{ route('archives.class_notes', [$year->id, $class->id]) }}"
                                   class="flex-1 text-center px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                                    <i class="fas fa-chart-bar mr-1"></i> Notes
                                </a>
                            @endif

                            @if($canViewPayments)
                                <a href="{{ route('archives.class_payment_stats', [$year->id, $class->id]) }}"
                                   class="flex-1 text-center px-3 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition text-sm font-medium">
                                    <i class="fas fa-money-bill-wave mr-1"></i> Paiements
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
