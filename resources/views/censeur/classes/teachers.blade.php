@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- En-tête -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Enseignants de la classe : {{ $class->name }}</h1>
                <p class="mt-1 text-sm text-gray-600">Liste complète des enseignants assignés à cette classe</p>
            </div>
            <div class="mt-4 md:mt-0 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                <p class="text-sm text-gray-600">Total: <span class="font-medium">{{ $teachers->count() }} enseignant(s)</span></p>
            </div>
        </div>
    </div>

    <!-- Tableau -->
    <div class="bg-white shadow-lg rounded-xl border border-gray-200">
        @if($teachers->count() > 0)
        <div class="overflow-x-auto">
            <div class="max-h-[600px] overflow-y-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">N°</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Enseignant</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Sexe</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Téléphone</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Matières enseignées</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($teachers as $data)
                        <tr class="hover:bg-blue-50 transition duration-150 group">
                            <!-- Numéro -->
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-b">
                                {{ $loop->iteration }}
                            </td>
                            
                            <!-- Nom et Prénom -->
                            <td class="px-4 py-3 border-b">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-white text-xs font-bold">
                                            {{ substr($data['teacher']->name, 0, 2) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $data['teacher']->name }}</div>
                                        <div class="text-sm text-gray-600">Enseignant</div>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Sexe -->
                            <td class="px-4 py-3 whitespace-nowrap border-b">
                                @if($data['teacher']->gendre)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $data['teacher']->gendre == 'M' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800' }}">
                                        {{ $data['teacher']->gendre }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-sm">--</span>
                                @endif
                            </td>
                            
                            <!-- Email -->
                            <td class="px-4 py-3 border-b">
                                <div class="text-sm text-gray-600 break-all max-w-[200px]">
                                    @if($data['teacher']->email)
                                        <a href="mailto:{{ $data['teacher']->email }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                            {{ $data['teacher']->email }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">--</span>
                                    @endif
                                </div>
                            </td>
                            
                            <!-- Téléphone -->
                            <td class="px-4 py-3 whitespace-nowrap border-b">
                                <div class="text-sm text-gray-900">
                                    @if($data['teacher']->phone)
                                        <a href="tel:{{ $data['teacher']->phone }}" class="text-gray-600 hover:text-gray-800">
                                            {{ $data['teacher']->phone }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">--</span>
                                    @endif
                                </div>
                            </td>
                            
                            <!-- Matières enseignées -->
                            <td class="px-4 py-3 border-b">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($data['subjects'] as $subject)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $subject }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            
                            <!-- Actions -->
                            <td class="px-4 py-3 whitespace-nowrap border-b">
                                <a href="{{ route('enseignants.show', $data['teacher']->id) }}" 
                                   class="inline-flex items-center px-3 py-1.5 border border-blue-300 rounded-full text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 hover:border-blue-400 transition duration-150">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Voir profil
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
            </svg>
            <h4 class="text-lg font-semibold text-gray-900 mb-2">Aucun enseignant assigné</h4>
            <p class="text-gray-600">Cette classe n'a pas d'enseignants pour le moment.</p>
        </div>
        @endif
    </div>

    <!-- Actions -->
    <div class="mt-8 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
        <button onclick="window.history.back()" 
                class="inline-flex items-center px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-200 font-medium">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </button>
        
        <div class="flex space-x-3">
            <a href="{{ route('enseignants.export', $class->id) }}" 
               class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Télécharger PDF
            </a>
        </div>
    </div>
</div>

<style>
    .min-w-full {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .min-w-full th {
        background-color: #f9fafb;
        position: sticky;
        top: 0;
        z-index: 10;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .min-w-full td {
        border-bottom: 1px solid #f3f4f6;
    }
    
    .min-w-full tr:last-child td {
        border-bottom: none;
    }
    
    .hover\:bg-blue-50:hover {
        background-color: #eff6ff;
    }
    
    @media (max-width: 768px) {
        .max-w-7xl {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .overflow-x-auto {
            margin-left: -1rem;
            margin-right: -1rem;
        }
    }
</style>
@endsection