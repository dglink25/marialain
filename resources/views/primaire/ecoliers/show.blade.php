@extends('layouts.app')

@section('content')

<div class="container">
    <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-200">
        <h1 class="text-2xl font-bold mb-6">
            Informations sur l'élève : {{ $student->last_name }} {{ $student->first_name }}
        </h1>

        <!-- Table des informations -->
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 text-sm">
                <tbody class="divide-y divide-gray-200">

                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100 w-1/3">N° Educ Master</th>
                        <td class="px-4 py-3">{{ $student->num_educ }}</td>
                    </tr>

                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Nom</th>
                        <td class="px-4 py-3">{{ $student->last_name }}</td>
                    </tr>

                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Prénom</th>
                        <td class="px-4 py-3">{{ $student->first_name }}</td>
                    </tr>

                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Classe</th>
                        <td class="px-4 py-3">{{ $student->classe?->name ?? 'Non assignée' }}</td>
                    </tr>

                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Sexe</th>
                        <td class="px-4 py-3">{{ $student->gender }}</td>
                    </tr>

                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Date de naissance</th>
                        <td class="px-4 py-3">{{ $student->birth_date ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Lieu de naissance</th>
                        <td class="px-4 py-3">{{ $student->birth_place ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Tuteur</th>
                        <td class="px-4 py-3">
                            {{ $student->parent_full_name ?? '-' }} 
                            ({{ $student->parent_phone ?? '-' }})
                        </td>
                    </tr>

                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Email du parent</th>
                        <td class="px-4 py-3">{{ $student->parent_email ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Contact</th>
                        <td class="px-4 py-3">{{ $student->parent_phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Année d'inscription</th>
                        <td class="px-4 py-3">{{ $student->created_at ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Année d'inscription</th>
                        <td class="px-4 py-3">{{ $student->created_at ?? '-' }}</td>
                    </tr>
                     <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Certificat de naissance</th>
                        <td class="px-4 py-3">
                            {{ $student->birth_certificate ?? '-' }}</td>
                    </tr>
                     <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Carte de vacination</th>
                        <td class="px-4 py-3">
                        @if ($student->vaccination_card)
                            <img src="{{ asset('storage/' . $student->vaccination_card) }}" 
                                 alt="Carte de vaccination de {{ $student->last_name }}" 
                                 class="h-32 border rounded">
                        @else
                            <span class="text-gray-500 italic">Aucun fichier disponible</span>
                        @endif
                    </tr>
                    <tr>
    <th class="px-4 py-3 text-left bg-gray-100">Diplôme</th>
    <td class="px-4 py-3">
        @if($student->diploma_certificate)
            <img src="{{ asset('storage/' . $student->diploma_certificate) }}" 
                 alt="Diplôme de {{ $student->last_name }}" 
                 class="h-32 border rounded">
        @else
            <span class="text-gray-500 italic">Aucun fichier disponible</span>
        @endif
    </td>
</tr>



                </tbody>
    
            </table>
        </div>


        

    </div>
</div>
@endsection
