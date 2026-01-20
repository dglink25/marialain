@extends('layouts.app')
@section('title', 'Enseignants de ' . $subject->name)

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Header avec animation -->
    <div class="mb-8 transform transition-all duration-300 hover:scale-[1.01]">
        <h1 class="text-3xl font-bold text-gray-900 mb-2 text-center lg:text-left animate-fadeIn">
            Enseignants assignés à : 
            <span class="text-green-600 bg-gradient-to-r from-green-500 to-emerald-600 bg-clip-text text-transparent">
                {{ $subject->name }}
            </span>
        </h1>
        <div class="w-24 h-1 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full mx-auto lg:mx-0 animate-slideIn"></div>
    </div>

    <!-- Message de succès avec animation -->
    @if(session('success'))
        <div class="mb-8 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-lg shadow-sm animate-bounceIn">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-green-800 font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
    <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6">
      <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    @if (session('error'))
    <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6">
      {{ session('error') }}
    </div>
    @endif

    <!-- Statistiques -->
    @php
        $uniqueTeachers = $subject->teachers->unique('id');
        $totalClasses = 0;
        foreach($uniqueTeachers as $teacher) {
            $totalClasses += $teacher->classes ? $teacher->classes->count() : 0;
        }
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 animate-fadeInUp">
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-6">
            <div class="flex items-center">
                <div class="bg-blue-500 rounded-xl p-3 mr-4">
                    <i class="fas fa-chalkboard-teacher text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-blue-600">Enseignants uniques</p>
                    <p class="text-2xl font-bold text-blue-800">{{ $uniqueTeachers->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-xl p-6">
            <div class="flex items-center">
                <div class="bg-green-500 rounded-xl p-3 mr-4">
                    <i class="fas fa-door-open text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-green-600">Total Classes</p>
                    <p class="text-2xl font-bold text-green-800">{{ $totalClasses }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 rounded-xl p-6">
            <div class="flex items-center">
                <div class="bg-purple-500 rounded-xl p-3 mr-4">
                    <i class="fas fa-book text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-purple-600">Matière</p>
                    <p class="text-lg font-bold text-purple-800 truncate">{{ $subject->name }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($uniqueTeachers->count())
        <!-- Bouton PDF avec design amélioré -->
        <div class="flex justify-between items-center mb-6 animate-fadeInUp">
            <div class="text-sm text-gray-600">
                <i class="fas fa-info-circle mr-1"></i>
                {{ $uniqueTeachers->count() }} enseignant(s) unique(s)
            </div>
            <button onclick="openPdfModal()"
                class="bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white px-6 py-3 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 font-semibold flex items-center group">
                <svg class="w-5 h-5 mr-2 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Télécharger PDF
            </button>
        </div>

        <!-- Modal PDF amélioré -->
        <div id="pdfModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-all duration-300">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all duration-500 scale-95 animate-modalIn">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Sélectionner une période
                        </h2>
                        <button onclick="closePdfModal()" 
                                class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Générez un PDF des enseignants pour la période spécifiée</p>
                </div>
                <form method="POST" action="{{ route('subject.teachers.pdf', $subject->id) }}" class="p-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                            <input type="date" name="start_date" required 
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all duration-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                            <input type="date" name="end_date" required 
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all duration-300">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closePdfModal()" 
                                class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-300 font-medium">
                            Annuler
                        </button>
                        <button type="submit" 
                                class="px-6 py-2.5 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 font-semibold flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Générer PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tableau Desktop -->
        <div class="hidden lg:block bg-white/90 backdrop-blur-sm shadow-2xl rounded-2xl border border-gray-100 overflow-hidden animate-fadeInUp">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200/60">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-8 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">N°</th>
                            <th class="px-8 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Enseignants</th>
                            <th class="px-8 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Classes & Montants</th>
                            <th class="px-8 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200/40">
                        @foreach($uniqueTeachers as $teacher)
                        @php
                            $teacherClasses = $teacher->classes ?? collect();
                            $teacherClassesCount = $teacherClasses->count();
                        @endphp
                        <tr class="hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-indigo-50/50 transition-all duration-300 group animate-slideIn" style="animation-delay: {{ $loop->index * 100 }}ms">
                            <td class="px-8 py-6 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-sm transform group-hover:scale-110 transition-transform duration-300">
                                        {{ $loop->iteration }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="font-semibold text-gray-900 group-hover:text-indigo-700 transition-colors duration-300">{{ $teacher->name }}</div>
                                <div class="text-sm text-gray-500 mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $teacher->email ?? '--' }}
                                </div>
                                <div class="text-xs text-gray-400 mt-2">
                                    <i class="fas fa-door-open mr-1"></i>
                                    {{ $teacherClassesCount }} classe{{ $teacherClassesCount > 1 ? 's' : '' }} assignée{{ $teacherClassesCount > 1 ? 's' : '' }}
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                @if($teacherClassesCount > 0)
                                <div class="space-y-3">
                                    @foreach($teacherClasses as $classe)
                                        @php
                                            $pivot = $classe->pivot;
                                            $amountBrut = $pivot->amount_brut ?? 0;
                                            $aib = round($amountBrut * 0.05, 2);
                                        @endphp
                                        <div class="bg-gradient-to-r from-gray-50 to-blue-50/30 border border-gray-200/60 rounded-xl p-4 hover:shadow-md transition-all duration-300 group/item">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center mb-2">
                                                        <span class="font-bold text-gray-800 text-sm bg-blue-100 px-3 py-1 rounded-full">{{ $classe->name }}</span>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                                                        <div class="flex items-center text-gray-600">
                                                            <svg class="w-4 h-4 mr-1 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                                            </svg>
                                                            Brut: <span class="font-semibold text-green-700 ml-1">{{ number_format($amountBrut, 2, '.', ',') }} FCFA</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button onclick="openAmountModal('modal-{{ $teacher->id }}-{{ $classe->id }}')"
                                                        class="ml-4 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                    </svg>
                                                    Modifier
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @else
                                <div class="text-center py-4">
                                    <span class="text-sm text-gray-500 bg-gray-100 px-3 py-2 rounded-lg">
                                        <i class="fas fa-door-closed mr-2"></i>
                                        Aucune classe assignée
                                    </span>
                                </div>
                                @endif
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex flex-col space-y-3">
                                    @if($teacherClassesCount > 0)
                                        @foreach($teacherClasses as $classe)
                                            <a href="{{ route('enseignants.cahier.matiere', ['teacher' => $teacher->id, 'classe' => $classe->id, 'subject' => $subject->id]) }}"
                                               class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl text-sm font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 group/btn">
                                                <svg class="w-4 h-4 mr-2 group-hover/btn:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                Cahier - {{ $classe->name }}
                                            </a>
                                        @endforeach
                                    @endif
                                    <a href="{{ route('enseignants.show', $teacher->id ) }}" 
                                       class="inline-flex items-center px-4 py-2.5 border-2 border-blue-200 text-blue-700 bg-blue-50/50 hover:bg-blue-100 hover:border-blue-300 rounded-xl transition-all duration-300 font-medium group/profile">
                                        <svg class="w-4 h-4 mr-2 group-hover/profile:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Voir le profil
                                    </a>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Modal amélioré pour chaque classe -->
                        @foreach($teacherClasses as $classe)
                        @php
                            $pivot = $classe->pivot;
                            $amountBrut = $pivot->amount_brut ?? 0;
                        @endphp
                        <div id="modal-{{ $teacher->id }}-{{ $classe->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity duration-300">
                            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all duration-500 scale-95 animate-modalIn">
                                <div class="p-6 border-b border-gray-100">
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-xl font-bold text-gray-900 flex items-center">
                                            <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                            </svg>
                                            Montant brut - {{ $classe->name }}
                                        </h2>
                                        <button onclick="closeAmountModal('modal-{{ $teacher->id }}-{{ $classe->id }}')" 
                                                class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-2">Enseignant: <span class="font-semibold">{{ $teacher->name }}</span></p>
                                </div>
                                <form action="{{ route('enseignants.classe.paiement', ['teacher' => $teacher->id, 'class' => $classe->id, 'subject' => $subject->id]) }}" method="POST" class="p-6">
                                    @csrf
                                    <div class="mb-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Montant brut (FCFA)</label>
                                        <input type="number" name="amount" min="0" step="0.01" value="{{ $amountBrut }}" 
                                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                                               placeholder="Saisir le montant brut">
                                    </div>
                                    <div class="flex justify-end space-x-3">
                                        <button type="button" onclick="closeAmountModal('modal-{{ $teacher->id }}-{{ $classe->id }}')" 
                                                class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-300 font-medium">
                                            Annuler
                                        </button>
                                        <button type="submit" 
                                                class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 font-semibold">
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

        <!-- Version Mobile -->
        <div class="lg:hidden space-y-6 animate-fadeIn">
            @foreach($uniqueTeachers as $teacher)
            @php
                $teacherClasses = $teacher->classes ?? collect();
                $teacherClassesCount = $teacherClasses->count();
            @endphp
            <div class="bg-white/90 backdrop-blur-sm shadow-2xl rounded-2xl border border-gray-100 p-6 hover:shadow-2xl transition-all duration-500 transform hover:scale-[1.02]">
                <!-- En-tête de l'enseignant -->
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200/60">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-lg">
                            {{ $loop->iteration }}
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-lg">{{ $teacher->name }}</h3>
                            <p class="text-sm text-gray-500 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ $teacher->email ?? '--' }}
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                <i class="fas fa-door-open mr-1"></i>
                                {{ $teacherClassesCount }} classe{{ $teacherClassesCount > 1 ? 's' : '' }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('enseignants.show', $teacher->id ) }}" 
                       class="bg-blue-100 text-blue-700 p-2 rounded-lg hover:bg-blue-200 transition-colors duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </a>
                </div>

                <!-- Classes et montants -->
                @if($teacherClassesCount > 0)
                <div class="space-y-4 mb-6">
                    <h4 class="font-semibold text-gray-700 text-sm uppercase tracking-wide">Classes assignées</h4>
                    @foreach($teacherClasses as $classe)
                        @php
                            $pivot = $classe->pivot;
                            $amountBrut = $pivot->amount_brut ?? 0;
                            $aib = round($amountBrut * 0.05, 2);
                        @endphp
                        <div class="bg-gradient-to-r from-gray-50 to-blue-50/30 border border-gray-200/60 rounded-xl p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-gray-800 bg-blue-100 px-3 py-1 rounded-full text-sm">{{ $classe->name }}</span>
                                <button onclick="openAmountModal('modal-{{ $teacher->id }}-{{ $classe->id }}')"
                                        class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold shadow hover:shadow-md transform hover:scale-105 transition-all duration-300">
                                    Modifier
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div class="text-center bg-green-50 rounded-lg p-2">
                                    <div class="font-semibold text-green-700">Montant brut</div>
                                    <div class="text-green-800 font-bold">{{ number_format($amountBrut, 2, '.', ',') }} FCFA</div>
                                </div>
                                <div class="text-center bg-orange-50 rounded-lg p-2">
                                    <div class="font-semibold text-orange-700">AIB (5%)</div>
                                    <div class="text-orange-800 font-bold">{{ number_format($aib, 2, '.', ',') }} FCFA</div>
                                </div>
                            </div>

                            <a href="{{ route('enseignants.cahier.matiere', ['teacher' => $teacher->id, 'classe' => $classe->id, 'subject' => $subject->id]) }}"
                               class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white py-2 rounded-lg text-sm font-semibold shadow hover:shadow-md transform hover:scale-[1.02] transition-all duration-300 flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Cahier de texte
                            </a>
                        </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 bg-gray-50 rounded-xl mb-6">
                    <i class="fas fa-door-closed text-gray-400 text-3xl mb-3"></i>
                    <p class="text-gray-500">Aucune classe assignée</p>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    @else
        <!-- État vide avec animation -->
        <div class="text-center py-16 animate-pulseSubtle">
            <div class="max-w-md mx-auto">
                <div class="w-24 h-24 bg-gradient-to-br from-gray-200 to-gray-300 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">Aucun enseignant assigné</h3>
                <p class="text-gray-500">Aucun enseignant n'est actuellement assigné à cette matière.</p>
            </div>
        </div>
    @endif
</div>

<script>
// Fonctions pour la modal PDF
function openPdfModal() {
    const modal = document.getElementById('pdfModal');
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.style.opacity = '1';
        modal.querySelector('.animate-modalIn').classList.remove('scale-95');
        modal.querySelector('.animate-modalIn').classList.add('scale-100');
    }, 10);
}

function closePdfModal() {
    const modal = document.getElementById('pdfModal');
    modal.querySelector('.animate-modalIn').classList.remove('scale-100');
    modal.querySelector('.animate-modalIn').classList.add('scale-95');
    modal.style.opacity = '0';
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

// Fonctions pour les modales de montant
function openAmountModal(id) {
    const modal = document.getElementById(id);
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.style.opacity = '1';
        modal.querySelector('.animate-modalIn').classList.remove('scale-95');
        modal.querySelector('.animate-modalIn').classList.add('scale-100');
    }, 10);
}

function closeAmountModal(id) {
    const modal = document.getElementById(id);
    modal.querySelector('.animate-modalIn').classList.remove('scale-100');
    modal.querySelector('.animate-modalIn').classList.add('scale-95');
    modal.style.opacity = '0';
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

// Fermer les modales en cliquant à l'extérieur
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('bg-black/50')) {
        // Fermer modal PDF
        const pdfModal = document.getElementById('pdfModal');
        if (!pdfModal.classList.contains('hidden')) {
            closePdfModal();
        }
        
        // Fermer modales de montant
        const amountModals = document.querySelectorAll('[id^="modal-"]');
        amountModals.forEach(modal => {
            if (!modal.classList.contains('hidden') && modal.id !== 'pdfModal') {
                closeAmountModal(modal.id);
            }
        });
    }
});

// Fermer avec la touche Échap
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        // Fermer modal PDF
        const pdfModal = document.getElementById('pdfModal');
        if (!pdfModal.classList.contains('hidden')) {
            closePdfModal();
        }
        
        // Fermer modales de montant
        const amountModals = document.querySelectorAll('[id^="modal-"]');
        amountModals.forEach(modal => {
            if (!modal.classList.contains('hidden') && modal.id !== 'pdfModal') {
                closeAmountModal(modal.id);
            }
        });
    }
});
</script>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideIn {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes bounceIn {
    0% { opacity: 0; transform: scale(0.3); }
    50% { opacity: 1; transform: scale(1.05); }
    70% { transform: scale(0.9); }
    100% { opacity: 1; transform: scale(1); }
}

@keyframes modalIn {
    from { opacity: 0; transform: scale(0.7) translateY(-20px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

@keyframes pulseSubtle {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}

@keyframes bounce {
    0%, 20%, 53%, 80%, 100% { transform: translate3d(0,0,0); }
    40%, 43% { transform: translate3d(0,-8px,0); }
    70% { transform: translate3d(0,-4px,0); }
    90% { transform: translate3d(0,-2px,0); }
}

.animate-fadeIn { animation: fadeIn 0.6s ease-out; }
.animate-fadeInUp { animation: fadeInUp 0.8s ease-out; }
.animate-slideIn { animation: slideIn 0.5s ease-out; }
.animate-bounceIn { animation: bounceIn 0.6s ease-out; }
.animate-modalIn { animation: modalIn 0.3s ease-out; }
.animate-pulseSubtle { animation: pulseSubtle 2s ease-in-out infinite; }
.animate-bounce { animation: bounce 1s ease-in-out; }

/* Smooth transitions pour les modales */
[id^="modal-"] {
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
}

.animate-modalIn {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Scrollbar personnalisée */
.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Amélioration du focus pour l'accessibilité */
button:focus, a:focus, input:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}
</style>
@endsection