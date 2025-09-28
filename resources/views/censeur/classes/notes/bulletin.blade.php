@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-6 text-center">
        Bulletin - {{ $student->last_name }} {{ $student->first_name }}  
        (Trimestre {{ $trimestre }})
    </h1>

    <table class="w-full border-collapse border text-sm md:text-base">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-2 py-1">Matière</th>
                <th class="border px-2 py-1">Coef</th>
                @for ($i=1; $i<=5; $i++)
                    <th class="border px-2 py-1">I{{ $i }}</th>
                @endfor
                <th class="border px-2 py-1">Moy. Interros</th>
                <th class="border px-2 py-1">D1</th>
                <th class="border px-2 py-1">D2</th>
                <th class="border px-2 py-1">Moy /20</th>
                <th class="border px-2 py-1">Moy x Coef</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bulletin as $row)
                <tr>
                    <td class="border px-2 py-1">{{ $row['subject'] }}</td>
                    <td class="border px-2 py-1 text-center">{{ $row['coef'] }}</td>
                    @foreach ($row['interros'] as $note)
                        <td class="border px-2 py-1 text-center">{{ $note ?? '-' }}</td>
                    @endforeach
                    <td class="border px-2 py-1 text-center">{{ $row['moyInterro'] ?? '-' }}</td>
                    <td class="border px-2 py-1 text-center">{{ $row['devoirs'][1] ?? '-' }}</td>
                    <td class="border px-2 py-1 text-center">{{ $row['devoirs'][2] ?? '-' }}</td>
                    <td class="border px-2 py-1 text-center">{{ $row['moyenne'] ?? '-' }}</td>
                    <td class="border px-2 py-1 text-center">{{ $row['moyCoeff'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-6 text-lg font-bold">
        Moyenne Générale : {{ $moyenneGenerale ?? '-' }} /20  
        <br>
        Rang : {{ $rang ?? '-' }}
    </div>

    <div class="mt-6">
        <button onclick="window.history.back()" 
                    class="bg-gray-600 text-white px-3 py-2 rounded text-sm hover:bg-gray-700 transition whitespace-nowrap text-center">
                Retour
            </button>
    </div>
</div>
@endsection
