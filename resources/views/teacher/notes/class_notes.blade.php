@extends('layouts.app')

@section('content')

@php
    $pageTitle = "Récapitulatif Notes";
@endphp

<div class="p-6">
    {{-- Titre --}}
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-800">
            Notes - Classe {{ $classe->name }}
        </h1>
        <p class="text-gray-600">
            Année académique : <span class="font-semibold">{{ $activeYear->name ?? $activeYear->label ?? 'N/A' }} / Trimestre {{ $trimestre }}</span>
        </p>
        <h2 class="text-lg font-semibold text-blue-700 mt-4">
            Matière : {{ $subject->name }} (Coef {{ $subject->coefficient ?? 1 }})
        </h2>
    </div>

    {{-- Messages flash --}}
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tableau responsive --}}
    <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
        <table class="min-w-full border border-gray-300 text-sm">
            <thead>
                <tr class="bg-gray-200 text-gray-700">
                    <th rowspan="2" class="px-3 py-2 border">N°</th>
                    <th rowspan="2" class="px-3 py-2 border">Nom</th>
                    <th rowspan="2" class="px-3 py-2 border">Prénoms</th>
                    <th rowspan="2" class="px-3 py-2 border">Sexe</th>
                    <th colspan="5" class="px-3 py-2 border text-center">Interrogations</th>
                    <th rowspan="2" class="px-3 py-2 border">Moy. I</th>
                    <th rowspan="2" class="px-3 py-2 border">Coef</th>
                    <th colspan="2" class="px-3 py-2 border text-center">Devoirs</th>
                    <th rowspan="2" class="px-3 py-2 border">Moy./20</th>
                    <th rowspan="2" class="px-3 py-2 border">Moy.Coef</th>
                    <th rowspan="2" class="px-3 py-2 border">Rang</th>
                </tr>
                <tr class="bg-gray-100 text-gray-600">
                    <th class="px-2 py-1 border">I1</th>
                    <th class="px-2 py-1 border">I2</th>
                    <th class="px-2 py-1 border">I3</th>
                    <th class="px-2 py-1 border">I4</th>
                    <th class="px-2 py-1 border">I5</th>
                    <th class="px-2 py-1 border">D1</th>
                    <th class="px-2 py-1 border">D2</th>
                </tr>
            </thead>
            <tbody>
                @foreach($classe->students as $student)
                    @php
                        // CORRECTION ICI : Retirer [$subject->id]
                        $grades = $gradesData[$student->id] ?? null;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 border text-center">{{ $loop->iteration }}</td>
                        <td class="px-3 py-2 border">{{ $student->last_name }}</td>
                        <td class="px-3 py-2 border">{{ $student->first_name }}</td>
                        <td class="px-3 py-2 border text-center">{{ $student->gender }}</td>

                        {{-- Interrogations --}}
                        @if($grades && isset($grades['interros']))
                            {{-- I1 --}}
                            <td class="px-2 py-1 border text-center">
                                {{ isset($grades['interros'][1]) ? number_format($grades['interros'][1], 2) : '-' }}
                            </td>
                            {{-- I2 --}}
                            <td class="px-2 py-1 border text-center">
                                {{ isset($grades['interros'][2]) ? number_format($grades['interros'][2], 2) : '-' }}
                            </td>
                            {{-- I3 --}}
                            <td class="px-2 py-1 border text-center">
                                {{ isset($grades['interros'][3]) ? number_format($grades['interros'][3], 2) : '-' }}
                            </td>
                            {{-- I4 --}}
                            <td class="px-2 py-1 border text-center">
                                {{ isset($grades['interros'][4]) ? number_format($grades['interros'][4], 2) : '-' }}
                            </td>
                            {{-- I5 --}}
                            <td class="px-2 py-1 border text-center">
                                {{ isset($grades['interros'][5]) ? number_format($grades['interros'][5], 2) : '-' }}
                            </td>
                        @else
                            {{-- Si pas de notes, afficher des tirets --}}
                            <td class="px-2 py-1 border text-center">-</td>
                            <td class="px-2 py-1 border text-center">-</td>
                            <td class="px-2 py-1 border text-center">-</td>
                            <td class="px-2 py-1 border text-center">-</td>
                            <td class="px-2 py-1 border text-center">-</td>
                        @endif

                        {{-- Moyenne Interros --}}
                        <td class="px-2 py-1 border text-center font-semibold">
                            @if($grades && isset($grades['moyenneInterro']))
                                {{ number_format($grades['moyenneInterro'], 2) }}
                            @else
                                -
                            @endif
                        </td>

                        {{-- Coefficient --}}
                        <td class="px-2 py-1 border text-center">
                            @if($grades && isset($grades['coef']))
                                {{ $grades['coef'] }}
                            @else
                                {{ $subject->coefficient ?? 1 }}
                            @endif
                        </td>

                        {{-- Devoirs --}}
                        @if($grades && isset($grades['devoirs']))
                            {{-- D1 --}}
                            <td class="px-2 py-1 border text-center">
                                {{ isset($grades['devoirs'][1]) ? number_format($grades['devoirs'][1], 2) : '-' }}
                            </td>
                            {{-- D2 --}}
                            <td class="px-2 py-1 border text-center">
                                {{ isset($grades['devoirs'][2]) ? number_format($grades['devoirs'][2], 2) : '-' }}
                            </td>
                        @else
                            <td class="px-2 py-1 border text-center">-</td>
                            <td class="px-2 py-1 border text-center">-</td>
                        @endif

                        {{-- Moyenne matière --}}
                        <td class="px-2 py-1 border text-center font-bold text-blue-600">
                            @if($grades && isset($grades['moyenne']))
                                {{ number_format($grades['moyenne'], 2) }}
                            @else
                                -
                            @endif
                        </td>
                        
                        {{-- Moyenne avec coefficient --}}
                        <td class="px-2 py-1 border text-center font-bold text-blue-600">
                            @if($grades && isset($grades['moyenneMat']))
                                {{ number_format($grades['moyenneMat'], 2) }}
                            @else
                                -
                            @endif
                        </td>
                        
                        {{-- Rang --}}
                        <td class="px-2 py-1 border text-center font-bold text-blue-600">
                            @if($grades && isset($grades['rang']))
                                {{ $grades['rang'] }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection