@extends('layouts.app')

@section('title', 'Liste des étudiants')

@section('content')

@if(!$activeYear)
    <h1 class="text-xl font-bold">Liste de tous les  élèves de CPEG MARIE-ALAIN</h1>
    <div class="bg-yellow-100 text-yellow-700 p-3 rounded mb-4">
        {{ $message }}
    </div>

@else


    <div class="mb-4">
    <form method="GET" action="{{ route('admin.students.index') }}" class="flex flex-wrap gap-2">
        <!-- Recherche -->
        <input type="text" name="search" placeholder="Rechercher un élève"
               value="{{ request('search') }}"
               class="border rounded p-2">

        <!-- Filtre entité -->
        <select name="entity_id" class="border rounded p-2">
            <option value="">-- Entité --</option>
            @foreach($entities as $entity)
                <option value="{{ $entity->id }}" {{ request('entity_id') == $entity->id ? 'selected' : '' }}>
                    {{ $entity->name }}
                </option>
            @endforeach
        </select>

        <!-- Filtre classe -->
        <select name="class_id" class="border rounded p-2">
            <option value="">-- Classe --</option>
            @foreach($classes as $classe)
                <option value="{{ $classe->id }}" {{ request('class_id') == $classe->id ? 'selected' : '' }}>
                    {{ $classe->name }}
                </option>
            @endforeach
        </select>

        <!-- Filtre date inscription -->
        <input type="date" name="date" value="{{ request('date') }}" class="border rounded p-2">

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filtrer</button>
        <a href="{{ route('admin.students.index') }}" class="bg-gray-400 text-white px-4 py-2 rounded">Réinitialiser</a>
    </form>
</div>

<div class="mb-4">
    <form method="GET" action="{{ route('admin.students.export.pdf') }}" class="flex gap-2">
        <select name="class_id" class="border rounded p-2">
            <option value="">-- Toutes les classes --</option>
            @foreach($classes as $classe)
                <option value="{{ $classe->id }}">{{ $classe->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded">
            Télécharger PDF
        </button>
    </form>
</div>

<div class="mb-4">
    <a href="{{ route('admin.students.export.all.pdf') }}" 
       class="bg-red-600 text-white px-4 py-2 rounded">
       Télécharger toutes les classes (PDF Zip)
    </a>
</div>


@if(auth()->check())
    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-xl font-bold">Liste de tous les  élèves de CPEG MARIE-ALAIN</h1>
            <a href="{{ route('admin.students.create') }}" 
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                + Nouvelle inscription
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border rounded-lg">
                <thead class="bg-gray-100 text-sm font-semibold text-left">
                    <!-- Ligne 1 : titres principaux -->
                    <tr>
                        <th class="border px-4 py-2" rowspan="2">N°</th>
                        <th class="border px-4 py-2" rowspan="2">N° Éduc Master</th>
                        <th class="border px-4 py-2" rowspan="2">Nom</th>
                        <th class="border px-4 py-2" rowspan="2">Prénoms</th>
                        <th class="border px-4 py-2" rowspan="2">Sexe</th>
                        <th class="border px-4 py-2" rowspan="2">Niveau</th>
                        <th class="border px-4 py-2" rowspan="2">Classe</th>

                        <!-- Colonne parent Frais de Scolarité -->
                        <th class="border px-4 py-2 text-center" colspan="2">Frais de Scolarité</th>

                        <th class="border px-4 py-2" rowspan="2">Date de naissance</th>
                        <th class="border px-4 py-2" rowspan="2">Parents/Tuteurs</th>
                        <th class="border px-4 py-2" rowspan="2">Date d'inscription</th>
                        <th class="border px-4 py-2" rowspan="2">Actions</th>
                    </tr>

                    <!-- Ligne 2 : sous-colonnes Frais de Scolarité -->
                    <tr>
                        <th class="border px-4 py-2 text-left">Total payé</th>
                        <th class="border px-4 py-2 text-left">Reste à payer</th>
                    </tr>
                </thead>

                <tbody class="text-sm">
                    @forelse($students as $student)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="border px-4 py-2">{{ $loop->iteration }}</td>
                        <td class="border px-4 py-2">{{ $student->num_educ ?? '- -' }}</td>
                        <td class="border px-4 py-2">
                            <a href="{{ route('admin.students.show', $student->id) }}" class="text-blue-600 hover:underline">
                                {{ $student->last_name }}
                            </a>
                        </td>
                        <td class="border px-4 py-2">
                            <a href="{{ route('admin.students.show', $student->id) }}" class="text-blue-600 hover:underline">
                                {{ $student->first_name }}
                            </a>
                        </td>
                        <td class="border px-4 py-2">{{ $student->gender ?? '- -' }}</td>
                        <td class="border px-4 py-2">{{ $student->entity->name ?? '-' }}</td>
                        <td class="border px-4 py-2">{{ $student->classe->name ?? '-' }}</td>

                       
                        <td class="border px-4 py-2">{{ $student->school_fees_paid ?? '- -' }} FCFA <br>
                            <a href="{{ route('students.payments.index', $student->id) }}" 
                                class="bg-blue-600 text-white px-4 py-2 rounded mb-4 inline-block">
                                Détails
                            </a>
                        </td>
                        <td class="border px-4 py-2">{{ number_format($student->remaining_fees,2) }} FCFA</td>

                        <td class="border px-4 py-2">{{ $student->birth_date }}</td>
                        <td class="border px-4 py-2">{{ $student->parent_full_name ?? ' - - ' }} <br> {{ $student->parent_phone ?? ' - - ' }}</td>
                        <td class="border px-4 py-2">{{ $student->created_at }}</td>
                        <td class="border px-4 py-2 space-x-2">
                            <a href="{{ route('admin.students.edit', $student->id) }}" class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">Modifier</a>
                            <form method="POST" action="{{ route('admin.students.destroy', $student->id) }}" onsubmit="return confirm('Voulez-vous vraiment supprimer cet étudiant ?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-4">Aucun étudiant inscrit.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $students->links() }}
        </div>
    </div>
@else
<p style="color:red"> Une erreur s'est produite lors de l'affichage de cette section <br> Veuillez vous connectez à nouveau pour continuer <a href="{{ route('login') }}" class="block px-3 py-2 rounded bg-blue-600 text-white">Se connecter</a></p>
@endif 


@endif


@endsection
