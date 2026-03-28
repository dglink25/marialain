@extends('layouts.app')

@section('content')
@if(auth()->check())
<div class="max-w-6xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
    <!-- En-tête avec fond dégradé -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-8 py-6 text-white">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">Fiche Élève</h1>
                <p class="text-blue-100 mt-2">Détails complets de l'élève</p>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-full p-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Informations principales -->
    <div class="px-8 py-6 border-b border-gray-200">
        <div class="flex items-center space-x-4">
            <div class="bg-blue-100 text-blue-800 rounded-full p-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $student->last_name }} {{ $student->first_name }}</h2>
                <p class="text-gray-600">{{ $student->classe->name ?? 'Classe non assignée' }}</p>
            </div>
        </div>
    </div>

    <!-- Grille d'informations organisée par sections -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 p-8">
        <!-- Section Identité -->
        <div class="lg:col-span-1">
            <div class="bg-gray-50 rounded-lg p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Identité
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Nom complet</label>
                        <p class="text-gray-900 font-semibold">{{ $student->last_name }} {{ $student->first_name }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Sexe</label>
                        <p class="text-gray-900 font-semibold">{{ $student->gender ?? 'Non spécifié' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Date de naissance</label>
                        <p class="text-gray-900 font-semibold">{{ $student->birth_date }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Lieu de naissance</label>
                        <p class="text-gray-900 font-semibold">{{ $student->birth_place }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Scolarité -->
        <div class="lg:col-span-1">
            <div class="bg-gray-50 rounded-lg p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" transform="translate(0 6)"/>
                    </svg>
                    Scolarité
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">N° Éduc Master</label>
                        <p class="text-gray-900 font-semibold">{{ $student->num_educ ?? 'Non attribué' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Niveau</label>
                        <p class="text-gray-900 font-semibold">{{ $student->entity->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Classe</label>
                        <p class="text-gray-900 font-semibold">{{ $student->classe->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Date d'inscription</label>
                        <p class="text-gray-900 font-semibold">
                            {{ $student->classe->created_at ? $student->classe->created_at->format('d/m/Y') : 'Non spécifié' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Contacts & Finances -->
        <div class="lg:col-span-1">
            <div class="bg-gray-50 rounded-lg p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    Contacts & Finances
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Parent/Tuteur</label>
                        <p class="text-gray-900 font-semibold">{{ $student->parent_full_name ?? 'Non renseigné' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Téléphone</label>
                        <p class="text-gray-900 font-semibold">{{ $student->parent_phone ?? 'Non renseigné' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Email</label>
                        <p class="text-gray-900 font-semibold">{{ $student->parent_email ?? 'Non renseigné' }}</p>
                    </div>
                    <div class="pt-2 border-t border-gray-200">
                        <label class="text-sm font-medium text-gray-500">Total payé</label>
                        <p class="text-xl font-bold text-green-600">{{ number_format($student->total_paid, 2) }} FCFA</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Documents -->
    <div class="px-8 py-6 border-t border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Documents de l’élève
        </h3>

        <div class="grid md:grid-cols-2 gap-4">
            @php
                $docs = [
                    'Acte de naissance' => $student->birth_certificate ?? null,
                    'Carnet de vaccination' => $student->vaccination_card ?? null,
                    'Relevé de notes' => $student->previous_report_card ?? null,
                    'Diplôme' => $student->diploma_certificate ?? null,
                ];
            @endphp

            @foreach ($docs as $label => $url)
                <div class="bg-white border rounded-lg shadow-sm p-4 mb-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700">{{ ucfirst(str_replace('_', ' ', $label)) }}</h3>

                        @if ($url)
                            <a href="{{ $url }}" target="_blank"
                            class="inline-flex items-center px-3 py-2 text-sm text-white bg-orange-600 rounded-lg hover:bg-orange-700 transition">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Ouvrir
                            </a>
                        @endif
                    </div>

                    @if ($url)
                        
                            <img src="{{ $url }}" alt="{{ $label }}"
                                class="w-full h-64 object-contain rounded-lg border my-2">
                        

                        {{-- ⬇️ Bouton de téléchargement --}}
                        <div class="mt-2">
                            <a href="{{ $url }}" download
                            class="inline-block px-3 py-2 bg-orange-600 text-white text-sm rounded-lg hover:bg-orange-700">
                                Télécharger
                            </a>
                        </div>
                    @else
                        <p class="text-sm text-gray-400 italic mt-2">Aucun document disponible</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>


    <!-- Actions -->
    <div class="px-8 py-6 bg-gray-50 border-t border-gray-200">
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
            <div class="text-sm text-gray-600">
                Dernière modification : {{ $student->classe->updated_at ? $student->classe->updated_at->format('d/m/Y à H:i') : '--' }}
            </div>
            
            <div class="flex space-x-3">
                <button onclick="window.history.back()" 
                        class="px-6 py-2 bg-gray-600 text-white rounded-lg shadow hover:bg-gray-700 transition duration-200 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour
                </button>
                
                @if(auth()->id() == 8)
                <a href="{{ route('admin.students.edit', $student->id) }}" 
                   class="px-6 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition duration-200 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
                
                <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" 
                      onsubmit="return confirm('Voulez-vous vraiment supprimer cet étudiant ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-6 py-2 bg-red-600 text-white rounded-lg shadow hover:bg-red-700 transition duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Supprimer
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@else
<div class="max-w-md mx-auto mt-8 bg-red-50 border border-red-200 rounded-lg p-6 text-center">
    <svg class="w-12 h-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <h3 class="text-lg font-semibold text-red-800 mb-2">Session expirée</h3>
    <p class="text-red-600 mb-4">Veuillez vous reconnecter pour continuer</p>
    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
        Se connecter
    </a>
</div>
@endif
@endsection