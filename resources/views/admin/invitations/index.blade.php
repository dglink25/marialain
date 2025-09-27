@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Invitations des enseignants';
@endphp
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold mb-6">Invitations des enseignants</h1>

    {{-- Formulaire d'invitation --}}
    <form method="POST" action="{{ route('admin.invitations.store') }}" class="bg-white shadow-md rounded p-6 mb-6 space-y-4">
        @csrf
        <div>
            <label for="email" class="block text-sm font-medium mb-1">Email de l'enseignant</label>
            <input type="email" name="email" id="email" class="border rounded p-2 w-full" required>
        </div>

        <div>
            <label for="academic_year_id" class="block text-sm font-medium mb-1">Année académique</label>
            <select name="academic_year_id" id="academic_year_id" class="border rounded p-2 w-full" required>
                @foreach($years as $year)
                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="entity" class="block text-sm font-medium mb-1">Entité</label>
            <select name="entity" id="entity" class="border rounded p-2 w-full" required>
                <option value="">-- Sélectionner --</option>
                <option value="maternelle" {{ request('entity') == 'maternelle' ? 'selected' : '' }}>Maternelle</option>
                <option value="primaire" {{ request('entity') == 'primaire' ? 'selected' : '' }}>Primaire</option>
                <option value="secondaire" {{ request('entity') == 'secondaire' ? 'selected' : '' }}>Secondaire</option>
            </select>
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Envoyer l'invitation</button>
    </form>

    {{-- Filtrage des invitations --}}
    <form method="GET" action="{{ route('admin.invitations.index') }}" class="mb-4 flex flex-wrap gap-4 items-center">
        <select name="entity" onchange="this.form.submit()" class="border rounded p-2">
            <option value="">-- Filtrer par entité --</option>
            <option value="maternelle" {{ request('entity') == 'maternelle' ? 'selected' : '' }}>Maternelle</option>
            <option value="primaire" {{ request('entity') == 'primaire' ? 'selected' : '' }}>Primaire</option>
            <option value="secondaire" {{ request('entity') == 'secondaire' ? 'selected' : '' }}>Secondaire</option>
        </select>
        <noscript>
            <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded">Filtrer</button>
        </noscript>
    </form>

    {{-- Liste des invitations --}}
    <div class="bg-white shadow-md rounded p-4 overflow-x-auto">
        <h2 class="font-semibold mb-3">Invitations envoyées</h2>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Entité</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Année académique</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Invité par</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($invitations as $inv)
                    <tr>
                        <td class="px-4 py-2 text-sm">{{ $inv->email }}</td>
                        <td class="px-4 py-2 text-sm">{{ ucfirst($inv->entity) }}</td>
                        <td class="px-4 py-2 text-sm">{{ $inv->academicYear->name ?? '-' }}</td>
                        <td class="px-4 py-2 text-sm">{{ $inv->inviter->name ?? '-' }}</td>
                        <td class="px-4 py-2 text-sm">{{ $inv->created_at->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-2 text-center text-sm text-gray-500">Aucune invitation envoyée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $invitations->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
