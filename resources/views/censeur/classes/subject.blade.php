@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'LIste des Matières';
@endphp

{{-- Messages flash --}}
    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- En-tête -->
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-500">Liste matières </h1>
        </div>
        <div class="mt-4 md:mt-0 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
            <p class="text-sm text-gray-600">
                Total: <span class="font-medium">{{ $subjects->count() }} matière(s)</span>
            </p>
        </div>
    </div>

    <!-- Liste des matières -->
    <div class="bg-white shadow rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Liste des matières ({{ $subjects->count() }})
            </h3>
        </div>
        <div class="p-6">
            @if($subjects->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($subjects as $subject)
                        @php
                            $coef = $subject->pivot->coefficient ?? null;
                        @endphp

                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300 p-6 text-center">
                            <!-- Avatar -->
                            <div class="w-16 h-16 bg-gradient-to-br from-green-100 to-green-200 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-green-700 font-bold text-xl">
                                    {{ preg_match('/\d+/', $subject->name, $m) ? $m[0] : strtoupper(substr($subject->name, 0, 2)) }}
                                </span>
                            </div>

                            <!-- Nom matière -->
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ $subject->name }} / Coefficient {{ $subject->coefficient}} </h4>

                            <!-- Affichage coefficient actuel -->
                            @if($coef)
                                <p class="text-sm text-gray-600 mb-3">
                                    <span class="font-medium">Coefficient actuel :</span>
                                    <span class="text-green-700 font-bold">{{ $coef }}</span>
                                </p>
                            @endif

                            <!-- Bouton définir/redéfinir -->
                            <button 
                                onclick="toggleForm({{ $subject->id }})"
                                class="inline-flex items-center px-3 py-1.5 text-sm 
                                    {{ $coef ? 'bg-blue-600 hover:bg-blue-700' : 'bg-orange-600 hover:bg-orange-700' }}
                                    text-white rounded-lg transition duration-200 font-medium">
                                {{ $coef ? 'Redéfinir coefficient' : 'Définir coefficient' }}
                            </button>

                            <!-- Formulaire dynamique caché -->
                            <div id="form-{{ $subject->id }}" class="hidden mt-3">
                                <form method="POST" action="{{ route('censeur.subjects.coefficient', [$classe->id, $subject->id]) }}" 
                                      class="bg-gray-50 p-4 rounded-lg border space-y-3">
                                    @csrf
                                    <div class="flex items-center justify-center space-x-2">
                                        <input type="number" name="coefficient" 
                                            class="w-24 border-gray-300 rounded-lg px-2 py-1 focus:ring focus:ring-green-200"
                                            value="{{ $coef ?? '' }}" 
                                            placeholder="Coef" required min="1" max="20">
                                        <button type="submit" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg">Valider</button>
                                        <button type="button" onclick="toggleForm({{ $subject->id }})" class="px-3 py-1.5 bg-gray-300 hover:bg-gray-400 text-sm rounded-lg">Annuler</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Lien consulter notes -->
                            <a href="{{ route('censeur.classes.notes', [$classe->id, $trimestre, $subject->id]) }}"      
                                class="mt-4 inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Consulter notes
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Aucune matière disponible</h4>
                    <p class="text-gray-600">Ajoutez une matière pour commencer.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
@section('scripts')
<script>
    function toggleForm(id) {
        const form = document.getElementById('form-' + id);
        if(form){
            form.classList.toggle('hidden');
        }
    }
</script>
@endsection
