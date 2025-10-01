@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-200">
        <h1 class="text-2xl font-bold mb-6">
            Informations sur l'enseignant : {{ $teacher->name }}
        </h1>

        <!-- Table des informations principales -->
        <div class="overflow-x-auto mb-6">
            <table class="min-w-full border border-gray-200 text-sm">
                <tbody class="divide-y divide-gray-200">

                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100 w-1/3">Nom complet</th>
                        <td class="px-4 py-3">{{ $teacher->name }}</td>
                    </tr>

                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Email</th>
                        <td class="px-4 py-3">{{ $teacher->email ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Téléphone</th>
                        <td class="px-4 py-3">{{ $teacher->phone ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Genre</th>
                        <td class="px-4 py-3">{{ $teacher->gender ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Date de naissance</th>
                        <td class="px-4 py-3">{{ $teacher->birth_date ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Lieu de naissance</th>
                        <td class="px-4 py-3">{{ $teacher->birth_place ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Adresse</th>
                        <td class="px-4 py-3">{{ $teacher->address ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Statut marital</th>
                        <td class="px-4 py-3">{{ $teacher->marital_status ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th class="px-4 py-3 text-left bg-gray-100">Nationalité</th>
                        <td class="px-4 py-3">{{ $teacher->nationality ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Fichiers PDF / Documents -->
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-700 mb-4">Documents</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @php
                    $files = [
                        'Carte d\'identité' => 'id_card_file',
                        'Certificat de naissance' => 'birth_certificate_file',
                        'Diplôme' => 'diploma_file',
                        'IFU' => 'ifu_file',
                        'RIB' => 'rib_file'
                    ];
                @endphp

                @foreach($files as $label => $field)
                    <div class="bg-gray-50 border border-gray-200 rounded p-4">
                        <h3 class="font-semibold mb-2">{{ $label }}</h3>
                        @if($teacher->$field)
                            <a href="{{ asset('storage/' . $teacher->$field) }}" 
                               class="text-blue-600 hover:underline" target="_blank">
                                Voir le document
                            </a>
                        @else
                            <span class="text-gray-500 italic">Aucun fichier disponible</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Bouton Retour -->
        <div>
            <a href="{{ url()->previous() }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-5 py-2 rounded shadow transition">
               Retour
            </a>
        </div>
    </div>
</div>
@endsection
