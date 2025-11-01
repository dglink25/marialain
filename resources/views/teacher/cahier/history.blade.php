@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">

    <h1 class="text-2xl font-bold mb-6 text-indigo-700">
        Historique du Cahier de texte
    </h1>

    {{-- BOUCLE SUR LES CLASSES --}}
    @foreach($classes as $class)
        <div class="bg-gray-100 p-4 mb-6 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-2">
                <h2 class="text-lg font-semibold text-gray-800">
                    Classe : {{ $class->name }}
                </h2>

                @if($class->currentLesson)
                    <button 
                        onclick="document.getElementById('modal-{{ $class->id }}').classList.remove('hidden')"
                        class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 text-sm">
                        Ajouter Cahier de texte
                    </button>
                @endif
            </div>

            {{-- MODAL D'AJOUT --}}
            @if($class->currentLesson)
                <div id="modal-{{ $class->id }}" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-6 w-full max-w-lg relative">
                        <button onclick="document.getElementById('modal-{{ $class->id }}').classList.add('hidden')" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">✖</button>
                        <h3 class="text-lg font-bold mb-4">
                            Cahier de texte - {{ $class->currentLesson->subject->name }}
                        </h3>

                        <form action="{{ route('teacher.cahier.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="class_id" value="{{ $class->id }}">
                            <input type="hidden" name="subject_id" value="{{ $class->currentLesson->subject_id }}">
                            <input type="hidden" name="teacher_id" value="{{ auth()->id() }}">
                            <input type="hidden" name="timetable_id" value="{{ $class->currentLesson->id }}">
                            <input type="hidden" name="day" value="{{ $class->currentLesson->day }}">

                            <textarea name="content" rows="4" class="w-full border rounded p-2 mb-2" placeholder="Écrivez le contenu du cours ici..."></textarea>
                            <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                                Enregistrer
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    @endforeach

    {{-- TABLEAU DES ENTRÉES --}}
    @if ($entries->isEmpty())
        <div class="bg-yellow-100 text-yellow-800 p-4 rounded">
            Aucun enregistrement trouvé pour cette classe.
        </div>
    @else
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-indigo-600 text-white">
                        <th class="px-4 py-2 text-left">Jour</th>
                        <th class="px-4 py-2 text-left">Heure</th>
                        <th class="px-4 py-2 text-left">Durée</th>
                        <th class="px-4 py-2 text-left">Matière</th>
                        <th class="px-4 py-2 text-left">Contenu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entries as $entry)
                        <tr class="border-b hover:bg-gray-100">
                            <td class="px-4 py-2">{{ ucfirst($entry->day) }}</td>
                            <td class="px-4 py-2">
                                {{ substr($entry->timetable->start_time,0,5) }} - {{ substr($entry->timetable->end_time,0,5) }}
                            </td>
                            <td class="px-4 py-2">
                                @php
                                    $start = \Carbon\Carbon::parse($entry->timetable->start_time);
                                    $end = \Carbon\Carbon::parse($entry->timetable->end_time);
                                    echo $start->diffInHours($end) . ' h';
                                @endphp
                            </td>
                            <td class="px-4 py-2">{{ $entry->subject->name ?? '---' }}</td>
                            <td class="px-4 py-2">{{ $entry->content }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
