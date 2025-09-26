@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Attribution des enseignants';
@endphp
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Attribution des enseignants</h1>

    @if(session('success'))
        <div class="p-2 bg-green-200 text-green-800 mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-2 bg-red-200 text-red-800 mb-4">{{ session('error') }}</div>
    @endif

    <!-- Formulaire d'attribution -->
    <form method="POST" action="{{ route('censeur.assignments.store') }}" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        @csrf
        <select name="class_id" class="border rounded p-2" required>
            <option value="">-- Classe --</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}">{{ $class->name }}</option>
            @endforeach
        </select>

        <select name="teacher_id" class="border rounded p-2" required>
            <option value="">-- Enseignant --</option>
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
            @endforeach
        </select>

        <select name="subject_id" class="border rounded p-2" required>
            <option value="">-- Matière --</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
            @endforeach
        </select>

        <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Attribuer</button>
    </form>

    <!-- Filtres -->
    <form method="GET" action="{{ route('censeur.assignments.index') }}" class="mb-6 flex gap-4">
        <select name="class_id" class="border rounded p-2">
            <option value="">-- Filtrer par Classe --</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}" @if(request('class_id')==$class->id) selected @endif>{{ $class->name }}</option>
            @endforeach
        </select>

        <select name="subject_id" class="border rounded p-2">
            <option value="">-- Filtrer par Matière --</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" @if(request('subject_id')==$subject->id) selected @endif>{{ $subject->name }}</option>
            @endforeach
        </select>

        <input type="text" name="name" value="{{ request('name') }}" placeholder="Nom enseignant" class="border rounded p-2">

        <button class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Filtrer</button>
    </form>

    <!-- Liste des enseignants -->
    <table class="w-full border">
        <thead class="bg-gray-200">
            <tr>
                <th class="p-2">Nom</th>
                <th class="p-2">Email</th>
                <th class="p-2">Classes & Matières</th>
            </tr>
        </thead>
        <tbody>
            @foreach($teachers as $teacher)
                <tr class="border-b">
                    <td class="p-2">{{ $teacher->name }}</td>
                    <td class="p-2">{{ $teacher->email }}</td>
                    <td class="p-2">
                        @foreach($teacher->classes as $class)
                            <div>{{ $class->name }} - {{ \App\Models\Subject::find($class->pivot->subject_id)->name }}</div>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
