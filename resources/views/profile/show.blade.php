@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6">{{ $user->name }}</h1>

    <p><strong>Nom :</strong> {{ $user->name }}</p>
    <p><strong>Email :</strong> {{ $user->email }}</p>
    <p><strong>Téléphone :</strong> {{ $user->phone }}</p>
    <p><strong>Sexe :</strong> {{ $user->gender }}</p>
    <p><strong>Situation matrimoniale :</strong> {{ $user->marital_status }}</p>
    <p><strong>Lieu de naissance :</strong> {{ $user->birth_place }}</p>
    <p><strong>Date de naissance :</strong> {{ $user->birth_date }}</p>
    <p><strong>Nationalité :</strong> {{ $user->nationality }}</p>
    <hr class="my-4">

    <h2 class="text-lg font-semibold">Documents</h2>
    <ul>
        @if($user->id_card_file)
            <li>
                Carte d’identité : <a href="{{ asset('storage/'.$user->id_card_file) }}" target="_blank" class="text-blue-600 underline">Ouvrir</a>
                (N° {{ $user->id_card_number }})
            </li>
        @endif

        @if($user->ifu_file)
            <li>
                IFU : <a href="{{ asset('storage/'.$user->ifu_file) }}" target="_blank" class="text-blue-600 underline">Ouvrir</a>
                (N° {{ $user->ifu_number }})
            </li>
        @endif

        @if($user->rib_file)
            <li>RIB : <a href="{{ asset('storage/'.$user->rib_file) }}" target="_blank">Ouvrir</a></li>
        @endif
    </ul>
</div>
@endsection
