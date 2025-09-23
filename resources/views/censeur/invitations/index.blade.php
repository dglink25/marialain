@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Invitations des enseignants</h1>

    @if(session('success'))
        <div class="p-4 mb-4 text-green-800 bg-green-200 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('censeur.invitations.send') }}" class="mb-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" name="name" placeholder="Nom enseignant" class="border rounded p-2" required>
            <input type="email" name="email" placeholder="Email enseignant" class="border rounded p-2" required>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Inviter</button>
        </div>
    </form>

    <table class="w-full border">
        <thead class="bg-gray-200">
            <tr>
                <th>N°</th>
                <th class="p-2">Nom</th>
                <th class="p-2">Email</th>
                <th class="p-2">Statut</th>
                <th>Date d'invitation</th>
                <th>Date de reponse</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invitations as $inv)
                <tr class="border-b">
                    <td class="border px-4 py-2 text-gray-600">{{ $loop->iteration }}</td> 
                    <td class="border px-4 py-2 text-gray-600">{{ $inv->user->name }}</td>
                    <td class="border px-4 py-2 text-gray-600">{{ $inv->user->email }}</td>
                    <td class="border px-4 py-2 text-gray-600">
                        @if($inv->accepted)
                            Acceptée
                        @else
                            En attente
                        @endif
                    </td>
                    <td class="border px-4 py-2 text-gray-600">{{ $inv->user->created_at }}</td>
                    <td>
                        {{ $inv->accepted_at 
                            ? \Carbon\Carbon::parse($inv->accepted_at)->format('d/m/Y H:i') 
                            : '--' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
