@extends('layouts.app')
@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- En-tête avec animation -->
    <div class="mb-8 animate-fade-in">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Autorisations - {{ $classe->name }}</h1>
        <div class="w-20 h-1 bg-blue-600 rounded-full"></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
        @foreach($permissions as $p)
        <div class="p-6 bg-white shadow-lg rounded-2xl hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-slide-up" 
             style="animation-delay: {{ $loop->index * 100 }}ms">
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-xl font-bold text-gray-800">Trimestre {{ $p->trimestre }}</h2>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $p->is_open ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    <span class="w-2 h-2 rounded-full mr-2 {{ $p->is_open ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    {{ $p->is_open ? 'Ouvert' : 'Fermé' }}
                </span>
            </div>

            @if($p->open_at)
            <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex items-center text-sm text-gray-600 mb-1">
                    <i class="fas fa-play-circle text-green-500 mr-2 w-4"></i>
                    <span>Ouverture : {{ $p->open_at }}</span>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-stop-circle text-red-500 mr-2 w-4"></i>
                    <span>Fermeture : {{ $p->close_at }}</span>
                </div>
            </div>
            @else
            <div class="mb-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200 text-center">
                <i class="fas fa-clock text-yellow-500 text-lg mb-1"></i>
                <p class="text-yellow-700 text-sm">Aucune période définie</p>
            </div>
            @endif

            <div class="mt-4 flex flex-col sm:flex-row gap-2">
                <!-- Ouvrir modal -->
                <button onclick="openModal({{ $p->trimestre }})"
                    class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all duration-200 hover:scale-105 active:scale-95 flex items-center justify-center">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Définir période
                </button>

                <!-- Toggle manuel -->
                <form method="POST"
                    action="{{ route('censeur.permissions.toggle', [$classe->id, $p->trimestre]) }}" class="flex-1">
                    @csrf
                    <button class="w-full px-4 py-2.5 {{ $p->is_open ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-500 hover:bg-green-600' }} text-white rounded-lg font-medium transition-all duration-200 hover:scale-105 active:scale-95 flex items-center justify-center">
                        <i class="fas {{ $p->is_open ? 'fa-lock' : 'fa-lock-open' }} mr-2"></i>
                        {{ $p->is_open ? 'Fermer' : 'Ouvrir' }}
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Informations supplémentaires -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6 animate-fade-in" style="animation-delay: 400ms">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border-l-4 border-blue-500 shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-start">
                <div class="flex-shrink-0 w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                    <i class="fas fa-info-circle text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">Information</h3>
                    <p class="text-blue-800 text-sm leading-relaxed">
                        Lorsqu'un trimestre est "Ouvert", les enseignants peuvent saisir les notes et appréciations.
                        Lorsqu'il est "Fermé", la saisie est bloquée.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 border-l-4 border-green-500 shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-start">
                <div class="flex-shrink-0 w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                    <i class="fas fa-lightbulb text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-green-900 mb-2">Conseil</h3>
                    <p class="text-green-800 text-sm leading-relaxed">
                        Pensez à ouvrir les trimestres pendant les périodes de saisie 
                        et à les fermer une fois les bulletins édités.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL AVEC ANIMATIONS AMÉLIORÉES --}}
<div id="modal"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm transition-opacity duration-300">

    <div id="modal-content"
         class="bg-white w-full max-w-md rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300">

        <!-- En-tête du modal -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6 rounded-t-2xl text-white">
            <h2 class="text-xl font-bold flex items-center">
                <i class="fas fa-calendar-alt mr-3"></i>
                Définir la période
            </h2>
            <p class="text-blue-100 text-sm mt-1">Trimestre <span id="modal-trimestre"></span></p>
        </div>

        <div class="p-6">
            <form method="POST" id="modalForm">
                @csrf

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                        <i class="fas fa-play-circle text-green-500 mr-2"></i>
                        Date d'ouverture
                    </label>
                    <input type="datetime-local" name="open_at"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                        <i class="fas fa-stop-circle text-red-500 mr-2"></i>
                        Date de fermeture
                    </label>
                    <input type="datetime-local" name="close_at"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                </div>

                <div class="flex flex-col sm:flex-row gap-3 justify-end">
                    <button onclick="closeModal()" type="button"
                            class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition-all duration-200 hover:scale-105 active:scale-95">
                        Annuler
                    </button>
                    <button class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all duration-200 hover:scale-105 active:scale-95 flex items-center">
                        <i class="fas fa-check mr-2"></i>
                        Valider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.6s ease-out forwards;
    }
    
    .animate-slide-up {
        opacity: 0;
        animation: slideUp 0.6s ease-out forwards;
    }
</style>

<script>
    function openModal(trimestre) {
        const modal = document.getElementById('modal');
        const content = document.getElementById('modal-content');
        const form = document.getElementById('modalForm');
        const trimestreSpan = document.getElementById('modal-trimestre');
        
        // Mettre à jour le trimestre dans le modal
        trimestreSpan.textContent = trimestre;
        
        form.action = "/censeur/permissions/{{ $classe->id }}/" + trimestre + "/dates";

        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('opacity-0', 'scale-95');
            content.classList.add('opacity-100', 'scale-100');
        }, 10);
    }

    function closeModal() {
        const modal = document.getElementById('modal');
        const content = document.getElementById('modal-content');

        content.classList.add('opacity-0', 'scale-95');
        content.classList.remove('opacity-100', 'scale-100');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
    
    // Fermer le modal en cliquant à l'extérieur
    document.getElementById('modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
</script>

@endsection