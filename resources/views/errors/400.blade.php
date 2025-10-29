@extends('layouts.app')

@section('title', 'Erreur 400 - Mauvaise requête')

@section('content')
<div class="min-h-screen flex flex-col justify-center items-center bg-gray-100 text-center p-6">
    <p class="text-gray-600 mb-6">
        Oups ! Quelque chose s'est mal passée. Merci de revenir en arrière
    </p>

    <a href="{{ url()->previous() }}" 
       class="px-6 py-3 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
        Retour
    </a>
</div>
@endsection
