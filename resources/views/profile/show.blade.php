@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6">{{ $user->name }}</h1>

    <p><strong>Nom :</strong> {{ $user->name }}</p>
    <p><strong>Email :</strong> {{ $user->email }}</p>
    <p><strong>Téléphone :</strong> {{ $user->phone ?? '--' }}</p>
    <p><strong>Sexe :</strong> {{ $user->gender ?? '--' }}</p>
    <p><strong>Situation matrimoniale :</strong> {{ $user->marital_status ?? '--' }}</p>
    <p><strong>Lieu de naissance :</strong> {{ $user->birth_place ?? '--' }}</p>
    <p><strong>Date de naissance :</strong> {{ $user->birth_date ?? '--' }}</p>
    <p><strong>Nationalité :</strong> {{ $user->nationality ?? '--' }}</p>

    <hr class="my-4">

    <h2 class="text-lg font-semibold mb-2">Documents</h2>
    <ul class="space-y-2">
        @if($user->id_card_file)
            <li>
                Carte d’identité : 
                <a href="{{ asset('storage/'.$user->id_card_file) }}" target="_blank" 
                   class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                    Ouvrir
                </a>
            </li>
        @endif

        @if($user->ifu_file)
            <li>
                IFU : 
                <a href="{{ asset('storage/'.$user->ifu_file) }}" target="_blank" 
                   class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                    Ouvrir
                </a>
                
            </li>
        @endif

        @if($user->rib_file)
            <li>
                RIB : 
                <a href="{{ asset('storage/'.$user->rib_file) }}" target="_blank" 
                   class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                    Ouvrir
                </a>
            </li>
        @endif
    </ul>

    <div class="mt-6">
        <a href="{{ url()->previous() }}" 
           class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
           Retour
        </a>
    </div>
</div>
@endsection
