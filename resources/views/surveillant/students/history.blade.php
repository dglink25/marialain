@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6 px-4">
    <h1 class="text-3xl font-bold mb-6 text-center">Cahier de punition - {{ $student->full_name }}</h1>

    @if($student->punishments->isEmpty())
        <p class="text-center text-gray-500">Aucune punition enregistrée pour cet élève.</p>
    @else
        <div class="space-y-4">
            @foreach($student->punishments as $punishment)
                <div class="bg-white shadow rounded-lg p-4 md:p-6 border-l-4 border-red-500">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-2">
                        <span class="text-gray-700 font-semibold">
                            Date : {{ \Carbon\Carbon::parse($punishment->date_punishment)->format('d/m/Y H:i') }}
                        </span>
                        <span class="text-gray-500 text-sm md:text-base">
                            Durée : <strong>{{ $punishment->hours }}h</strong>
                        </span>
                    </div>

                    <div class="mt-2">
                        <p class="text-gray-800"><strong>Motif :</strong> {{ $punishment->reason }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Bouton retour -->
    <div class="mt-6 text-center">
        <a href="{{ url()->previous() }}" class="inline-block bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
            Retour à la liste des élèves
        </a>
    </div>
    
</div>
@endsection
