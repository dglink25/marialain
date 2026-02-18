@extends('layouts.app')
@section('title', 'Enseignants de ' . $subject->name)

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Header épuré -->
    <div class="mb-10">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <div class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-sm font-medium mb-3">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Détail des enseignants
                </div>
                <h1 class="text-3xl lg:text-4xl font-light text-gray-900">
                    Enseignants de 
                    <span class="font-semibold text-indigo-600">{{ $subject->name }}</span>
                </h1>
            </div>
            
            <!-- Bouton PDF minimaliste -->
            <button onclick="openPdfModal()"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-600 transition-all duration-200 shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Exporter PDF
            </button>
        </div>
        <div class="w-20 h-0.5 bg-gradient-to-r from-indigo-200 to-indigo-400 rounded-full mt-4"></div>
    </div>

    <!-- Messages avec design doux -->
    @if(session('success'))
        <div class="mb-8 p-4 bg-emerald-50 border border-emerald-100 rounded-xl">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-emerald-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
    <div class="mb-8 p-4 bg-rose-50 border border-rose-100 rounded-xl">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-rose-700">Des erreurs sont survenues</h3>
                <ul class="mt-2 text-sm text-rose-600 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    @if (session('error'))
    <div class="mb-8 p-4 bg-rose-50 border border-rose-100 rounded-xl">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-rose-700">{{ session('error') }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistiques douces -->
    @php
        $uniqueTeachers = $subject->teachers->unique('id');
        $totalClasses = 0;
        foreach($uniqueTeachers as $teacher) {
            $totalClasses += $teacher->classes ? $teacher->classes->count() : 0;
        }
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
        <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Enseignants</p>
                    <p class="text-xl font-semibold text-gray-800">{{ $uniqueTeachers->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Classes</p>
                    <p class="text-xl font-semibold text-gray-800">{{ $totalClasses }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Matière</p>
                    <p class="text-base font-semibold text-gray-800 truncate max-w-[150px]">{{ $subject->name }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($uniqueTeachers->count())
        <!-- Modal PDF épuré -->
        <div id="pdfModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/20 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md transform transition-all duration-200 scale-95 opacity-0" id="pdfModalContent">
                <div class="p-5 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-medium text-gray-900">Exporter en PDF</h2>
                        <button onclick="closePdfModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Sélectionnez une période pour générer le rapport</p>
                </div>
                <form method="POST" action="{{ route('subject.teachers.pdf', $subject->id) }}" class="p-5">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Date de début</label>
                            <input type="date" name="start_date" required 
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:border-indigo-300 focus:ring-1 focus:ring-indigo-200 transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Date de fin</label>
                            <input type="date" name="end_date" required 
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:border-indigo-300 focus:ring-1 focus:ring-indigo-200 transition-colors">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 mt-6">
                        <button type="button" onclick="closePdfModal()" 
                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            Annuler
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm text-white bg-indigo-500 hover:bg-indigo-600 rounded-lg transition-colors">
                            Générer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tableau Desktop élégant -->
        <div class="hidden lg:block bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">#</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Enseignant</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Classes & Montants</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($uniqueTeachers as $teacher)
                        @php
                            $teacherClasses = $teacher->classes ?? collect();
                            $teacherClassesCount = $teacherClasses->count();
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-400">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800">{{ $teacher->name }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ $teacher->email ?? 'Email non renseigné' }}</div>
                                <div class="text-xs text-gray-300 mt-1">
                                    <span>{{ $teacherClassesCount }} classe{{ $teacherClassesCount > 1 ? 's' : '' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($teacherClassesCount > 0)
                                <div class="space-y-2">
                                    @foreach($teacherClasses as $classe)
                                        @php
                                            $pivot = $classe->pivot;
                                            $amountBrut = $pivot->amount_brut ?? 0;
                                        @endphp
                                        <div class="flex items-center justify-between bg-gray-50/50 rounded-lg p-2.5 border border-gray-100">
                                            <div class="flex items-center space-x-3">
                                                <span class="text-xs font-medium text-gray-600 bg-white px-2 py-1 rounded border border-gray-200">{{ $classe->name }}</span>
                                                <span class="text-xs text-gray-500">
                                                    <span class="text-gray-400">Brut:</span> 
                                                    <span class="font-medium text-gray-700">{{ number_format($amountBrut, 0, ',', ' ') }} FCFA</span>
                                                </span>
                                            </div>
                                            <button onclick="openAmountModal('modal-{{ $teacher->id }}-{{ $classe->id }}')"
                                                    class="text-xs text-indigo-500 hover:text-indigo-700 bg-white px-2 py-1 rounded border border-indigo-200 hover:border-indigo-300 transition-colors">
                                                Modifier
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                                @else
                                <span class="text-xs text-gray-400 bg-gray-50 px-3 py-1.5 rounded-lg">Aucune classe assignée</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col space-y-2">
                                    @if($teacherClassesCount > 0)
                                        @foreach($teacherClasses as $classe)
                                            <a href="{{ route('enseignants.cahier.matiere', ['teacher' => $teacher->id, 'classe' => $classe->id, 'subject' => $subject->id]) }}"
                                               class="inline-flex items-center text-xs text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-100 hover:bg-emerald-100 transition-colors">
                                                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                </svg>
                                                Cahier {{ $classe->name }}
                                            </a>
                                        @endforeach
                                    @endif
                                    <a href="{{ route('enseignants.show', $teacher->id ) }}" 
                                       class="inline-flex items-center text-xs text-indigo-600 hover:text-indigo-700 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100 hover:bg-indigo-100 transition-colors">
                                        <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Profil
                                    </a>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Modales montant -->
                        @foreach($teacherClasses as $classe)
                        @php
                            $pivot = $classe->pivot;
                            $amountBrut = $pivot->amount_brut ?? 0;
                        @endphp
                        <div id="modal-{{ $teacher->id }}-{{ $classe->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/20 backdrop-blur-sm">
                            <div class="bg-white rounded-xl shadow-xl w-full max-w-md transform transition-all duration-200 scale-95 opacity-0 modal-amount-content">
                                <div class="p-5 border-b border-gray-100">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-medium text-gray-900">{{ $classe->name }}</h3>
                                        <button onclick="closeAmountModal('modal-{{ $teacher->id }}-{{ $classe->id }}')" class="text-gray-400 hover:text-gray-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Enseignant: {{ $teacher->name }}</p>
                                </div>
                                <form action="{{ route('enseignants.classe.paiement', ['teacher' => $teacher->id, 'class' => $classe->id, 'subject' => $subject->id]) }}" method="POST" class="p-5">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Montant brut (FCFA)</label>
                                        <input type="number" name="amount" min="0" step="0.01" value="{{ $amountBrut }}" 
                                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:border-indigo-300 focus:ring-1 focus:ring-indigo-200 transition-colors"
                                               placeholder="Saisir le montant">
                                    </div>
                                    <div class="flex justify-end space-x-2 mt-5">
                                        <button type="button" onclick="closeAmountModal('modal-{{ $teacher->id }}-{{ $classe->id }}')" 
                                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                            Annuler
                                        </button>
                                        <button type="submit" 
                                                class="px-4 py-2 text-sm text-white bg-indigo-500 hover:bg-indigo-600 rounded-lg transition-colors">
                                            Enregistrer
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Version Mobile épurée -->
        <div class="lg:hidden space-y-3">
            @foreach($uniqueTeachers as $teacher)
            @php
                $teacherClasses = $teacher->classes ?? collect();
                $teacherClassesCount = $teacherClasses->count();
            @endphp
            <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                <!-- En-tête enseignant -->
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-600 font-medium text-sm">
                            {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-800">{{ $teacher->name }}</h3>
                            <p class="text-xs text-gray-400">{{ $teacher->email ?? 'Email non renseigné' }}</p>
                        </div>
                    </div>
                    <a href="{{ route('enseignants.show', $teacher->id ) }}" 
                       class="text-indigo-500 hover:text-indigo-700 bg-indigo-50 p-2 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </a>
                </div>

                <!-- Classes -->
                @if($teacherClassesCount > 0)
                <div class="space-y-2 mt-3">
                    @foreach($teacherClasses as $classe)
                        @php
                            $pivot = $classe->pivot;
                            $amountBrut = $pivot->amount_brut ?? 0;
                        @endphp
                        <div class="bg-gray-50/50 rounded-lg p-3 border border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-medium text-gray-700 bg-white px-2 py-1 rounded border border-gray-200">{{ $classe->name }}</span>
                                <button onclick="openAmountModal('modal-{{ $teacher->id }}-{{ $classe->id }}')"
                                        class="text-xs text-indigo-500 hover:text-indigo-700">
                                    Modifier
                                </button>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500">Montant brut:</span>
                                <span class="font-medium text-gray-700">{{ number_format($amountBrut, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <a href="{{ route('enseignants.cahier.matiere', ['teacher' => $teacher->id, 'classe' => $classe->id, 'subject' => $subject->id]) }}"
                               class="mt-2 w-full inline-flex items-center justify-center text-xs text-emerald-600 bg-emerald-50 px-3 py-2 rounded-lg border border-emerald-100">
                                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                Cahier de texte
                            </a>
                        </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4 bg-gray-50/50 rounded-lg mt-3">
                    <p class="text-xs text-gray-400">Aucune classe assignée</p>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    @else
        <!-- État vide élégant -->
        <div class="text-center py-16">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13.5 9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                </svg>
            </div>
            <h3 class="text-sm font-medium text-gray-600 mb-1">Aucun enseignant assigné</h3>
            <p class="text-xs text-gray-400">Cette matière n'a pas encore d'enseignants.</p>
        </div>
    @endif
</div>

<script>
// Modal PDF
function openPdfModal() {
    const modal = document.getElementById('pdfModal');
    const content = document.getElementById('pdfModalContent');
    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closePdfModal() {
    const modal = document.getElementById('pdfModal');
    const content = document.getElementById('pdfModalContent');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
}

// Modales montant
function openAmountModal(id) {
    const modal = document.getElementById(id);
    const content = modal.querySelector('.modal-amount-content');
    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeAmountModal(id) {
    const modal = document.getElementById(id);
    const content = modal.querySelector('.modal-amount-content');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
}

// Fermeture au clic extérieur
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('bg-black/20')) {
        // PDF modal
        const pdfModal = document.getElementById('pdfModal');
        if (!pdfModal.classList.contains('hidden')) {
            closePdfModal();
        }
        
        // Amount modals
        document.querySelectorAll('[id^="modal-"]').forEach(modal => {
            if (!modal.classList.contains('hidden') && modal.id !== 'pdfModal') {
                closeAmountModal(modal.id);
            }
        });
    }
});

// Touche Echap
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const pdfModal = document.getElementById('pdfModal');
        if (!pdfModal.classList.contains('hidden')) {
            closePdfModal();
        }
        
        document.querySelectorAll('[id^="modal-"]').forEach(modal => {
            if (!modal.classList.contains('hidden') && modal.id !== 'pdfModal') {
                closeAmountModal(modal.id);
            }
        });
    }
});
</script>

<style>
/* Transitions douces */
[id^="modal-"] {
    transition: opacity 0.2s ease;
}

.modal-amount-content, #pdfModalContent {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Scrollbar subtile */
.overflow-x-auto::-webkit-scrollbar {
    height: 4px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #ddd;
    border-radius: 2px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #ccc;
}

/* Focus élégant */
input:focus, button:focus, a:focus {
    outline: none;
    ring: 2px solid #e0e7ff;
}
</style>
@endsection