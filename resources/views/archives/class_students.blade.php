@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Élèves de la classe';
@endphp
<div class="bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-6">
        Élèves de la classe {{ $class->name }} ({{ $year->name }})
    </h1>

    @if($students->isEmpty())
        <p class="text-gray-600">Aucun élève trouvé dans cette classe.</p>
    @else
        <div class="overflow-x-auto">
    <table class="min-w-max w-full bg-white border rounded-lg shadow-sm">
        <thead class="bg-gray-100 text-xs md:text-sm font-semibold text-left">
            <tr>
                <th class="border px-4 py-2" rowspan="2">N°</th>
                <th class="border px-4 py-2" rowspan="2">N° Éduc Master</th>
                <th class="border px-4 py-2" rowspan="2">Nom</th>
                <th class="border px-4 py-2" rowspan="2">Prénoms</th>
                <th class="border px-4 py-2" rowspan="2">Sexe</th>
                <th class="border px-4 py-2" rowspan="2">Niveau</th>
                <th class="border px-4 py-2" rowspan="2">Classe</th>
                <th class="border px-4 py-2 text-center" colspan="2">Frais de Scolarité</th>
                <th class="border px-4 py-2" rowspan="2">Date de naissance</th>
                <th class="border px-4 py-2" rowspan="2">Parents/Tuteurs</th>
                <th class="border px-4 py-2" rowspan="2">Date d'inscription</th>
                <th class="border px-4 py-2" rowspan="2">Actions</th>
            </tr>
            <tr>
                <th class="border px-4 py-2 text-left">Total payé</th>
                <th class="border px-4 py-2 text-left">Reste à payer</th>
            </tr>
        </thead>

        <tbody class="text-xs md:text-sm">
            @forelse($students as $student)
                <tr class="border-b hover:bg-gray-50">
                    <td class="border px-4 py-2">{{ $loop->iteration }}</td>
                    <td class="border px-4 py-2">{{ $student->num_educ ?? '- -' }}</td>
                    <td class="border px-4 py-2">
                        <a href="{{ route('admin.students.show', $student->id) }}" 
                           class="text-blue-600 hover:underline">
                           {{ $student->last_name }}
                        </a>
                    </td>
                    <td class="border px-4 py-2">
                        <a href="{{ route('admin.students.show', $student->id) }}" 
                           class="text-blue-600 hover:underline">
                           {{ $student->first_name }}
                        </a>
                    </td>
                    <td class="border px-4 py-2">{{ $student->gender ?? '- -' }}</td>
                    <td class="border px-4 py-2">{{ $student->entity->name ?? '-' }}</td>
                    <td class="border px-4 py-2">{{ $student->classe->name ?? '-' }}</td>
                    
                    <td class="border px-4 py-2">
                        <span class="font-semibold">{{ $student->school_fees_paid ?? 0 }} FCFA</span>
                        <br>
                        @if(auth()->id() == 8)
                            <a href="{{ route('students.payments.index', $student->id) }}" 
                            class="mt-1 inline-block bg-blue-600 text-white px-2 py-1 rounded text-xs hover:bg-blue-700">
                            Détails
                            </a>
                        @endif
                    </td>
                    <td class="border px-4 py-2">
                        {{ number_format($student->remaining_fees, 0, ',', ' ') }} FCFA
                    </td>
                    
                    <td class="border px-4 py-2">{{ $student->birth_date }}</td>
                    <td class="border px-4 py-2">
                        {{ $student->parent_full_name ?? ' - - ' }} 
                        <br> 
                        <span class="text-gray-500">{{ $student->parent_phone ?? ' - - ' }}</span>
                    </td>
                    <td class="border px-4 py-2">{{ $student->created_at->format('d/m/Y') }}</td>
                    <td class="border px-4 py-2">
                        <a href="{{ route('admin.students.show', $student->id) }}" 
                           class="text-blue-600 hover:underline">Voir profil</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center py-4 text-gray-500">Aucun étudiant inscrit.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $students->links() }}
</div>

    @endif

    <div class="mt-6">
        <a href="{{ route('archives.show', $year->id) }}" 
           class="inline-block px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
           Retour aux classes
        </a>
    </div>
</div>
@endsection
