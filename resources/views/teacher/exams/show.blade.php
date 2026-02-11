{{-- resources/views/teacher/exams/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header avec navigation --}}
        <div class="mb-6">
            <a href="{{ route('teacher.exams.index') }}" 
               class="inline-flex items-center text-gray-600 hover:text-indigo-600 transition-colors group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour à la liste des épreuves
            </a>
        </div>

        <div class="max-w-4xl mx-auto">
            {{-- Carte principale --}}
            <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                {{-- En-tête avec dégradé --}}
                <div class="relative bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 px-8 py-10">
                    <div class="absolute inset-0 bg-black opacity-10"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-4">
                                <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h1 class="text-3xl font-bold text-white">{{ $exam->titre }}</h1>
                                    <div class="flex items-center gap-3 mt-2">
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-white/20 backdrop-blur-sm text-white border border-white/30">
                                            {{ $exam->class->name }}
                                        </span>
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-white/20 backdrop-blur-sm text-white border border-white/30">
                                            {{ $exam->subject->name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex gap-2">
                                <a href="{{ $exam->file_url }}" target="_blank" 
                                   class="bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-xl px-4 py-3 text-white transition-all flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Télécharger le PDF
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Formes décoratives --}}
                    <div class="absolute top-0 right-0 -mt-10 -mr-10">
                        <div class="w-40 h-40 rounded-full bg-white/10 blur-3xl"></div>
                    </div>
                    <div class="absolute bottom-0 left-0 -mb-10 -ml-10">
                        <div class="w-40 h-40 rounded-full bg-purple-500/20 blur-3xl"></div>
                    </div>
                </div>

                {{-- Corps --}}
                <div class="p-8">
                    {{-- Grille d'informations --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-gradient-to-br from-gray-50 to-white rounded-2xl p-6 border border-gray-100">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="bg-blue-100 rounded-lg p-2.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-500">Trimestre</span>
                            </div>
                            <p class="text-2xl font-bold text-gray-900">
                                @if($exam->trimestre == 1)
                                    🔵 Trimestre 1
                                @elseif($exam->trimestre == 2)
                                    🟢 Trimestre 2
                                @else
                                    🟠 Trimestre 3
                                @endif
                            </p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-gray-50 to-white rounded-2xl p-6 border border-gray-100">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="bg-purple-100 rounded-lg p-2.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-500">Type</span>
                            </div>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $exam->type == 'devoir' ? '📚 Devoir' : '📝 Interrogation' }} n°{{ $exam->numero_evaluation }}
                            </p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-gray-50 to-white rounded-2xl p-6 border border-gray-100">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="bg-green-100 rounded-lg p-2.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-500">Date de publication</span>
                            </div>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $exam->created_at->format('d/m/Y') }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                à {{ $exam->created_at->format('H:i') }}
                            </p>
                        </div>
                    </div>

                    {{-- Description --}}
                    @if($exam->description)
                        <div class="bg-gradient-to-br from-gray-50 to-white rounded-2xl p-6 border border-gray-100 mb-8">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="bg-orange-100 rounded-lg p-2.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-500">Description</span>
                            </div>
                            <p class="text-gray-700 leading-relaxed">
                                {{ $exam->description }}
                            </p>
                        </div>
                    @endif

                    {{-- Aperçu du PDF --}}
                    <div class="bg-gradient-to-br from-gray-50 to-white rounded-2xl p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="bg-red-100 rounded-lg p-2.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Fichier source</span>
                                    <p class="text-sm text-gray-700 mt-1">{{ $exam->file_name }}</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-3">
                                <a href="{{ $exam->file_url }}" target="_blank" 
                                   class="inline-flex items-center px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-colors shadow-lg hover:shadow-xl">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Visualiser
                                </a>
                                <a href="{{ $exam->file_url }}" download 
                                   class="inline-flex items-center px-5 py-2.5 bg-gray-600 hover:bg-gray-700 text-white rounded-xl transition-colors shadow-lg hover:shadow-xl">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Télécharger
                                </a>
                            </div>
                        </div>
                        
                        {{-- Aperçu embed --}}
                        <div class="relative rounded-xl overflow-hidden border border-gray-200 bg-gray-100" style="height: 500px;">
                            <iframe src="{{ $exam->file_url }}#view=FitH" 
                                    class="absolute inset-0 w-full h-full"
                                    frameborder="0">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection