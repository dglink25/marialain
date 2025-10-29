@extends('layouts.app')

@section('title', 'Erreur 500 - Problème interne')

@section('content')
<div class="min-h-screen flex flex-col justify-center items-center bg-gray-100 text-center p-6">
    <p class="text-gray-600 mb-6">
        Une erreur est survenue sur le serveur. L’équipe technique a été notifiée. Merci de revenir en arrière pour continuer 
    </p>

    <a href="{{ url()->previous() }}" 
       class="px-6 py-3 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
        Retour
    </a>
</div>
@endsection
