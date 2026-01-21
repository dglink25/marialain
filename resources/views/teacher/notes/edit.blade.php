@extends('layouts.app')

@section('content')

@php
    $pageTitle = "Edite Notes";
@endphp

<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-6">
        Modifier Notes  {{ $subject->name }} - {{ ucfirst($type) }} {{ $num }} - Classe {{ $classe->name }} /  Trimestre {{ $trimestre }}
    </h1>

    <!-- Messages d'alerte -->
            <div class="px-8 pt-6">
                @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-red-800 font-semibold">Veuillez corriger les erreurs suivantes :</h3>
                    </div>
                    <ul class="mt-2 text-red-700 list-disc list-inside text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-red-800">{{ session('error') }}</span>
                </div>
                @endif

                @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-green-800">{{ session('success') }}</span>
                </div>
                @endif
            </div>
            
    <form method="POST"
      action="{{ route('teacher.classes.notes.update', [
          'class' => $classe->id,
          'subject' => $subject->id,
          'type' => $num,
          'num' => $type,
          'trimestre' => $trimestre,
      ]) }}">

        @csrf
        <table class="w-full border-collapse border mb-4">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 py-2 border">N°</th>
                    <th class="px-3 py-2 border">Nom</th>
                    <th class="px-3 py-2 border">Prénom</th>
                    <th class="px-3 py-2 border">Sexe</th>
                    <th class="px-3 py-2 border">Note</th>
                </tr>
            </thead>
            <tbody>
                @foreach($classe->students as $student)
                <tr class="border-b">
                    <td class="px-3 py-2 border">{{ $loop->iteration }}</td>
                    <td class="px-3 py-2 border">{{ $student->last_name }}</td>
                    <td class="px-3 py-2 border">{{ $student->first_name }}</td>
                    <td class="px-3 py-2 border">{{ $student->gender }}</td>
                    <td class="px-3 py-2 border">
                        <input type="number" name="notes[{{ $student->id }}]" step="0.01" min="0" max="20"
                               value="{{ $student->grades->first()->value ?? '' }}"
                               class="w-20 px-2 py-1 border rounded">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex space-x-2">
            <!-- Formulaire pour soumettre les modifications -->
            <form action="{{ route('teacher.classes.notes.update', [
                        'class' => $classe->id,
                        'subject' => $subject->id,
                        'type' => $num,
                        'num' => $type,
                        'trimestre' => $trimestre,
                    ]) }}"
                    method="POST">
                @csrf
                @method('PUT') <!-- ou PATCH selon ta route -->
            
                <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Soumettre les modifications
                </button>
            </form>

            <!-- Formulaire pour supprimer toutes les notes -->
            <form action="{{ route('teacher.classes.notes.destroy', [$classe->id, $type, $num, $trimestre]) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer toutes les notes ?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 mt-2">
                    Supprimer toutes les notes
                </button>
            </form>

            <a href="{{ route('teacher.classes.notes.read', [
                'class' => $classe->id,
                'subject' => $subject->id,
                'type' => $type,
                'num' => $num,
                'trimestre' => $trimestre,
                ]) }}">
                Retour
            </a>
        </div>
    </form>
</div>
@endsection
