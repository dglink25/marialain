@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Inscrire un élève';
@endphp
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- En-tête compact -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3 mb-4">
        <h1 class="text-xl lg:text-2xl font-bold text-gray-800 whitespace-nowrap">Élèves en attente</h1>
        <div class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
            <a href="{{ route('admin.students.create') }}" 
               class="bg-blue-600 text-white px-3 py-2 rounded text-sm hover:bg-blue-500 transition whitespace-nowrap text-center">
                Inscrire un élève
            </a>
        </div>
    </div>

    @if(!$activeYear)
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4 text-sm">
            {{ $message }}
        </div>
    @else
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Tableau compact -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full bg-white text-xs lg:text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-2 py-2 text-left font-semibold text-gray-600 whitespace-nowrap">N°</th>
                            <th class="px-2 py-2 text-left font-semibold text-gray-600 whitespace-nowrap">N° Éduc</th>
                            <th class="px-2 py-2 text-left font-semibold text-gray-600 whitespace-nowrap">Nom</th>
                            <th class="px-2 py-2 text-left font-semibold text-gray-600 whitespace-nowrap">Prénoms</th>
                            <th class="px-2 py-2 text-left font-semibold text-gray-600 whitespace-nowrap">Sexe</th>
                            <th class="px-2 py-2 text-left font-semibold text-gray-600 whitespace-nowrap">Niveau</th>
                            <th class="px-2 py-2 text-left font-semibold text-gray-600 whitespace-nowrap">Classe</th>
                            <th class="px-2 py-2 text-left font-semibold text-gray-600 whitespace-nowrap">Naissance</th>
                            <th class="px-2 py-2 text-left font-semibold text-gray-600 whitespace-nowrap">Parents</th>
                            <th class="px-2 py-2 text-left font-semibold text-gray-600 whitespace-nowrap">Inscription</th>
                            <th class="px-2 py-2 text-left font-semibold text-gray-600 whitespace-nowrap"><center>Action</center></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($students as $student)
                            <tr class="hover:bg-gray-50">
                                <td class="px-2 py-2 whitespace-nowrap text-center">{{ $loop->iteration }}</td>
                                <td class="px-2 py-2 whitespace-nowrap">{{ $student->num_educ ?? '-' }}</td>
                                <td class="px-2 py-2 whitespace-nowrap font-medium text-blue-600">
                                    <a href="{{ route('admin.students.show', $student->id) }}" class="hover:underline">
                                        {{ $student->last_name }}
                                    </a>
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap font-medium text-blue-600">
                                    <a href="{{ route('admin.students.show', $student->id) }}" class="hover:underline">
                                        {{ $student->first_name }}
                                    </a>
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-center">{{ $student->gender ?? '-' }}</td>
                                <td class="px-2 py-2 whitespace-nowrap">{{ $student->entity->name ?? '-' }}</td>
                                <td class="px-2 py-2 whitespace-nowrap">{{ $student->classe->name ?? '' }}</td>
                                <td class="px-2 py-2 whitespace-nowrap">{{ $student->birth_date }}</td>
                                <td class="px-2 py-2">
                                    <div class="whitespace-nowrap">{{ $student->parent_full_name ?? '-' }}</div>
                                    <div class="text-gray-600 text-xs">{{ $student->parent_phone ?? '-' }}</div>
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap">{{ $student->created_at->format('d/m/Y') }}</td>
                                <td class="px-2 py-2 whitespace-nowrap">
                                    <form method="POST" action="{{ route('admin.students.validate', $student) }}" class="space-y-1">
                                        @csrf
                                        <input type="number" 
                                               name="amount_paid" 
                                               class="border border-gray-300 rounded px-2 py-1 w-20 text-xs"
                                               placeholder="Montant" 
                                               required
                                               min="0">
                                        <button type="submit" 
                                                class="bg-green-500 text-white px-0 py-1 rounded text-xs hover:bg-green-800 w-full">
                                            Valider
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-6 text-center text-gray-500">
                                    Aucun élève en attente
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <br>
        <br>
          <button onclick="window.history.back()" 
                    class="bg-gray-600 text-white px-3 py-2 rounded text-sm hover:bg-gray-700 transition whitespace-nowrap text-center">
                Retour
            </button>
    @endif
</div>
@endsection