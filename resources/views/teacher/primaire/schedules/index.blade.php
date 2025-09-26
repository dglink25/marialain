@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Emploi du temps - {{ $classe->name }}</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    <a href="{{ route('schedules.create') }}" 
       class="bg-blue-600 text-white px-4 py-2 rounded mb-4 inline-block hover:bg-blue-700 transition">
       + Ajouter un cours
    </a>
    
    <a href="{{ route('schedules.download') }}" 
    class="bg-green-600 text-white px-4 py-2 rounded mb-4 inline-block hover:bg-green-700 transition">
    Télécharger PDF
    </a>


    <div class="overflow-x-auto">
        <table class="w-full border-collapse border">
            <thead>
                <tr>
                    <th class="border px-2 py-2 bg-gray-100">Heures</th>
                    @foreach($days as $day)
                        <th class="border px-2 py-2 bg-gray-100 text-center">{{ $day }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($timeRanges as $range)
                    <tr>
                        <td class="border px-2 py-2 font-medium bg-gray-50">{{ $range }}</td>
                        @foreach($days as $day)
                            @php
                                $cell = $planning[$day][$range];
                            @endphp
                            @if($cell && $cell['is_start'])
                                <td class="border px-2 py-2 align-top" rowspan="{{ $cell['rowspan'] }}">
                                    <div class="p-2 mb-1 rounded bg-blue-100 text-blue-700 shadow">
                                        <strong>{{ $cell['schedule']->subject->name }}</strong><br>
                                        <small>{{ $cell['schedule']->start_time }} - {{ $cell['schedule']->end_time }}</small>
                                        <div class="mt-1 flex gap-2">
                                            <a href="{{ route('schedules.edit',$cell['schedule']->id) }}" class="text-blue-500">✏</a>
                                            <form action="{{ route('schedules.destroy',$cell['schedule']->id) }}" method="POST" onsubmit="return confirm('Supprimer ce cours ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-500">🗑</button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            @elseif($cell && !$cell['is_start'])
                                {{-- cellule déjà couverte par rowspan, ne rien afficher --}}
                            @else
                                <td class="border px-2 py-2"></td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
