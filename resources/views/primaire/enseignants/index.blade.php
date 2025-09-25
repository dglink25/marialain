@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <h1 class="text-2xl font-bold mb-4">Invitations enseignants - {{ $activeYear->name }}</h1>

    <!-- Formulaire -->
    <div class="bg-white p-6 rounded shadow mb-6">
        <form action="{{ route('primaire.enseignants.send') }}" method="POST" class="grid md:grid-cols-3 gap-4">
            @csrf
            <input type="text" name="name" placeholder="Nom enseignant" class="border p-2 rounded" required>
            <input type="email" name="email" placeholder="Email enseignant" class="border p-2 rounded" required>
            <select name="classe" class="border p-2 rounded" required>
                <option value="">-- Sélectionner classe --</option>
                @foreach($classes as $classe)
                    <option value="{{ $classe->id }}">
                        {{ $classe->name }} ({{ $classe->students_count }} élèves)
                    </option>
                @endforeach
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded col-span-full">Envoyer invitation</button>
        </form>
    </div>

    <!-- Invitations envoyées -->
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-3">Invitations envoyées</h2>
        <table class="w-full text-sm border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2">Nom</th>
                    <th class="px-3 py-2">Email</th>
                    <th class="px-3 py-2">Classe</th>
                    <th class="px-3 py-2">État</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invitations as $inv)
                    <tr class="border-b">
                        <td class="px-3 py-2">{{ $inv->user->name }}</td>
                        <td class="px-3 py-2">{{ $inv->user->email }}</td>
                        <td class="px-3 py-2">{{ $inv->classe->name ?? '---' }}</td>
                        <td class="px-3 py-2">
                            @if($inv->accepted)
                                <span class="text-green-600 font-semibold">Acceptée</span>
                            @else
                                <span class="text-yellow-600">En attente</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-4">Aucune invitation envoyée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
