@extends('layouts.app')

@php
    $pageTitle = "Points des Notes Disponibles";
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- En-tête -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Notes disponibles - Trimestre {{ $trimestre }}</h1>
                <p class="text-gray-600 mt-1">
                    Classe : {{ $classe->name }}
                </p>
            </div>
            <a href="{{ route('censeur.classes.trimestres', $classe->id) }}" 
               class="inline-flex items-center px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition">
                <i class="fas fa-arrow-left mr-2"></i> Retour
            </a>
        </div>

        <!-- Tableau -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">N°</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Matière</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700">Coef.</th>
                        <th colspan="{{ count($interrogations) }}" class="px-4 py-3 text-center font-semibold text-blue-700">
                            Interrogations
                        </th>
                        <th colspan="{{ count($devoirs) }}" class="px-4 py-3 text-center font-semibold text-purple-700">
                            Devoirs
                        </th>
                        <th class="px-4 py-3 text-center font-semibold text-green-700">Total</th>
                    </tr>
                    <tr class="bg-gray-50">
                        <th></th><th></th><th></th>
                        @foreach($interrogations as $i)
                            <th class="px-4 py-2 text-center text-gray-600">{{ $i }}</th>
                        @endforeach
                        @foreach($devoirs as $d)
                            <th class="px-4 py-2 text-center text-gray-600">{{ $d }}</th>
                        @endforeach
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($matieres as $index => $m)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $m->subject->name }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $m->coefficient ?? '-' }}</td>

                        @foreach($interrogations as $i)
                            <td class="px-4 py-3 text-center">
                                @if($notesDisponibles[$m->subject->name][$i])
                                    <i class="fas fa-check text-green-500"></i>
                                    <form action="{{ route('censeur.notes.autoriserModification') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="teacher_id" value="{{ $m->teacher_id ?? 0 }}">
                                        <input type="hidden" name="class_id" value="{{ $classe->id }}">
                                        <input type="hidden" name="subject_id" value="{{ $m->subject->id }}">
                                        <input type="hidden" name="trimestre" value="{{ $trimestre }}">
                                        <input type="hidden" name="type" value="{{ $i }}">
                                        
                                        <button type="submit" 
                                            class="bg-green-100 text-green-700 px-2 py-1 rounded-lg text-xs hover:bg-green-200 transition">
                                            <i class="fas fa-lock-open mr-1"></i>Autoriser
                                        </button>
                                    </form>
                                @else
                                    <i class="fas fa-times text-gray-300"></i>
                                @endif
                            </td>
                        @endforeach

                        @foreach($devoirs as $d)
                            <td class="px-4 py-3 text-center">
                                @if($notesDisponibles[$m->subject->name][$d])
                                    <i class="fas fa-check text-indigo-500"></i>
                                    <form action="{{ route('censeur.notes.autoriserModification') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="teacher_id" value="{{ $m->teacher_id ?? 0 }}">
                                        <input type="hidden" name="class_id" value="{{ $classe->id }}">
                                        <input type="hidden" name="subject_id" value="{{ $m->subject->id }}">
                                        <input type="hidden" name="trimestre" value="{{ $trimestre }}">
                                        <input type="hidden" name="type" value="{{ $i }}">
                                        
                                        <button type="submit" 
                                            class="bg-green-100 text-green-700 px-2 py-1 rounded-lg text-xs hover:bg-green-200 transition">
                                            <i class="fas fa-lock-open mr-1"></i>Autoriser
                                        </button>
                                    </form>

                                @else
                                    <i class="fas fa-times text-gray-300"></i>
                                @endif
                            </td>
                        @endforeach

                        <td class="px-4 py-3 text-center font-semibold text-green-600">
                            {{ $notesDisponibles[$m->subject->name]['total'] }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-4 py-6 text-center text-gray-500">
                            Aucune matière enregistrée pour cette classe dans l’année académique active.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Légende -->
        <div class="mt-6 flex items-center space-x-6 text-sm text-gray-600">
            <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i> Note(s) disponible(s)</div>
            <div class="flex items-center"><i class="fas fa-times text-gray-300 mr-2"></i> Aucune note enregistrée</div>
        </div>

    </div>
</div>
@endsection
