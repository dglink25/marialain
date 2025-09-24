@extends('layouts.app')

@section('content')
<div class="container ">
    <!-- Carte principale -->
    <div class="bg-white shadow-lg rounded-2xl p-10 border border-gray-200">
        
        <!-- En-tête -->
        <div class="flex items-center justify-between mb-8">
            <div>
              
                <h3 class="text-1xl font-bold text-blue-800">
                   
                    Année académique :{{ $class->academicYear->name ?? 'Non définie' }}
                
                </h3>
              
            </div>
            <a href="{{ route('primaire.classe.classes') }}" 
               class="bg-gray-600 text-white px-6 py-3 rounded-lg shadow hover:bg-gray-700 transition">
                 Retour
            </a>
        </div>

        <!-- Informations générales -->
        <div class="grid md:grid-cols-3 gap-8 mb-10">
           
            <div class="p-6 bg-gray-50 rounded-lg border">
                <p class="text-sm text-gray-500">Nom de la classe</p>
                <p class="text-lg font-semibold text-gray-800">
                    {{ $class->name ?? 'Non définie' }}
                </p>
            </div>
            <div class="p-6 bg-gray-50 rounded-lg border">
                <p class="text-sm text-gray-500">Enseignant titulaire</p>
                <p class="text-lg font-semibold text-gray-800">
                    {{ $class->teacher?->name ?? 'Non assigné' }}
                </p>
            </div>
            <div class="p-6 bg-gray-50 rounded-lg border">
                <p class="text-sm text-gray-500"> Voir l'emploi du temps</p>
                <p class="text-lg font-semibold text-gray-800">{{ $class->name }}</p>
            </div>
        </div>

        <!-- Liste élèves -->
        <div>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-semibold text-blue-700">Liste des élèves</h2>
                <a href="{{ route('primaire.classe.pdf', $class-> id)}}" 
                   class="bg-green-600 text-white px-5 py-2 rounded-lg shadow hover:bg-blue-700 transition">
                   Télécharger
                </a>
            </div>

            @if($class->students->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border border-gray-200 rounded-lg">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700 uppercase text-xs">
                                <th class="px-5 py-4 text-center">N°</th>
                                <th class="px-5 py-4 text-left">Numéro Educ-master</th>
                                <th class="px-5 py-4 text-left">Nom</th>
                                <th class="px-5 py-4 text-left">Prénom</th>
                                <th class="px-5 py-4 text-left">Sexe</th>
                                <th class="px-5 py-4 text-left">Date de naissance</th>
                                <th class="px-5 py-4 text-left">Lieu de naissance</th>
                                <th class="px-5 py-4 text-left">Contact Parent/Tuteur</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($class->students as $student)
                                <tr class="hover:bg-gray-50 transition">
                                     
                                    <td class="px-5 py-4 text-center text-gray-600">{{ $loop->iteration }}</td>
                                    <td class="px-5 py-4 text-center text-gray-600">{{ $student->num_educ ?? '-' }}</td>
                                    <td class="px-5 py-4 font-medium text-gray-600">{{ $student->last_name }}</td>
                                    <td class="px-5 py-4 font-medium text-gray-600">{{ $student->first_name }}</td>
                                    <td class="px-5 py-4 font-medium text-gray-600">{{ $student->gender ?? '-' }}</td>
                                    <td class="px-5 py-4 font-medium text-gray-600">{{ $student->birth_date }}</td>
                                    <td class="px-5 py-4 font-medium text-gray-600">{{ $student->birth_place }}</td>
                                    <td class="px-5 py-4 font-medium text-gray-600">{{ $student->parent_phone }}</td>
                                     </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 italic mt-4">Aucun élève inscrit dans cette classe.</p>
            @endif
        </div>
    </div>
</div>
@endsection
