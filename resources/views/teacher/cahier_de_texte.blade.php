@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-4">Cahier de texte {{ \Carbon\Carbon::now()->format('d/m/Y') }}</h1>

    @if(session('error'))
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @foreach($timetable as $slot)
        <div class="mb-4 p-4 border rounded">
            <h2 class="text-lg font-semibold">
                Matière : {{ $slot->subject->name ?? 'Non assignée' }}
                ({{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }})
            </h2>

            <form action="{{ route('teacher.cahier.store') }}" method="POST">
                @csrf
                <input type="hidden" name="class_id" value="{{ $slot->class_id }}">
                <input type="hidden" name="subject_id" value="{{ $slot->subject_id }}">
                <input type="hidden" name="teacher_id" value="{{ $slot->teacher_id }}">
                <input type="hidden" name="timetable_id" value="{{ $slot->id }}">
                <input type="hidden" name="day" value="{{ $slot->day }}">

                <textarea name="content" rows="4" class="w-full border rounded p-2" placeholder="Écrivez le contenu du cours ici..."></textarea>

                <button type="submit" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded">
                    Enregistrer
                </button>
            </form>
        </div>
    @endforeach
</div>
@endsection
