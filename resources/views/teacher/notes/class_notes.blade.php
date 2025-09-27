@extends('layouts.app')

@section('content')
<div class="p-6">
    {{-- Titre --}}
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-800">
            Notes - Classe {{ $classe->nom }}
        </h1>
        <p class="text-gray-600">
            Année académique : <span class="font-semibold">{{ $activeYear->name ?? $activeYear->label ?? 'N/A' }}</span>
        </p>
        {{-- Matières de l’enseignant --}}
        @foreach($subjects as $subject)
            <h2 class="text-lg font-semibold text-blue-700 mt-4">
                Matière : {{ $subject->name }} (Coef {{ $subject->coefficient ?? 1 }})
            </h2>
        @endforeach
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
                    <th rowspan="2" class="px-3 py-2 border">Moy. Matière</th>
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
                        // On suppose qu’il n’y a qu’une matière (enseignant connecté)
                        $subject = $subjects->first();
                        $grades = $gradesData[$student->id][$subject->id] ?? null;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 border text-center">{{ $loop->iteration }}</td>
                        <td class="px-3 py-2 border">{{ $student->last_name }}</td>
                        <td class="px-3 py-2 border">{{ $student->first_name }}</td>
                        <td class="px-3 py-2 border text-center">{{ $student->gender }}</td>

                        {{-- Interrogations --}}
                        @for($i = 0; $i < 5; $i++)
                            <td class="px-2 py-1 border text-center">
                                {{ $grades['interrogation'][$i] ?? '-' }}
                            </td>
                        @endfor

                        {{-- Moyenne Interros --}}
                        <td class="px-2 py-1 border text-center font-semibold">
                            {{ $grades['moyenneInterro'] ?? '-' }}
                        </td>

                        {{-- Coefficient --}}
                        <td class="px-2 py-1 border text-center">
                            {{ $grades['coef'] ?? ($subject->coefficient ?? 1) }}
                        </td>

                        {{-- Devoirs --}}
                        @for($i = 0; $i < 2; $i++)
                            <td class="px-2 py-1 border text-center">
                                {{ $grades['devoirs'][$i] ?? '-' }}
                            </td>
                        @endfor

                        {{-- Moyenne matière --}}
                        <td class="px-2 py-1 border text-center font-bold text-blue-600">
                            {{ $grades['moyenneMat'] ?? '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
