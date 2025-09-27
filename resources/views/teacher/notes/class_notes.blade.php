@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-6">Notes - Classe {{ $classe->name }}</h1>

    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded mb-4">{{ session('error') }}</div>
    @endif
    <pre>
{{ dd($classe->toArray()) }}
</pre>

    <div class="overflow-x-auto">
        <table class="min-w-full border rounded-lg text-sm">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-3 py-2 border">N°</th>
                    <th class="px-3 py-2 border">Nom</th>
                    <th class="px-3 py-2 border">Prénom</th>
                    <th class="px-3 py-2 border">Sexe</th>
                    @foreach($classe->subjects as $subject)
                        <th class="px-3 py-2 border text-center" colspan="10">{{ $subject->name }} (Coef: {{ $subject->coefficient }})</th>
                    @endforeach
                </tr>
                <tr>
                    <th colspan="2"></th>
                    @foreach($classe->subjects as $subject)
                        @for($i = 1; $i <= 5; $i++)
                            <th class="px-2 py-1 border">I{{ $i }}</th>
                        @endfor
                        <th class="px-2 py-1 border">Moy I</th>
                        @for($i = 1; $i <= 2; $i++)
                            <th class="px-2 py-1 border">D{{ $i }}</th>
                        @endfor
                        <th class="px-2 py-1 border">Moy Mat</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($classe->students as $student)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-3 py-2 border">{{ $loop->iteration }}</td>
                    <td class="px-3 py-2 border">{{ $student->last_name }}</td>
                    <td class="px-3 py-2 border">{{ $student->first_name }}</td>
                    <td class="px-3 py-2 border">{{ $student->gender }}</td

                    @foreach($classe->subjects as $subject)
                        @php $grades = $gradesData[$student->id][$subject->id]; @endphp
                        {{-- Interros --}}
                        @for($i = 0; $i < 5; $i++)
                            <td class="px-2 py-1 border text-center">
                                {{ $grades['interros'][$i] ?? '--' }}
                            </td>
                        @endfor
                        {{-- Moyenne Interro --}}
                        <td class="px-2 py-1 border text-center">{{ $grades['moyenneInterro'] ?? '--' }}</td>
                        {{-- Devoirs --}}
                        @for($i = 0; $i < 2; $i++)
                            <td class="px-2 py-1 border text-center">{{ $grades['devoirs'][$i] ?? '--' }}</td>
                        @endfor
                        {{-- Moyenne Matière --}}
                        <td class="px-2 py-1 border text-center">{{ $grades['moyenneMat'] ?? '--' }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
