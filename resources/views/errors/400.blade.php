@extends('layouts.app')

@section('title', 'Erreur 400 - Mauvaise requête')

@section('content')
<div class="min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-slate-50 to-blue-50/30 px-4 py-8">
    <!-- Icône animée -->
    <div class="mb-8 relative">
        <div class="w-32 h-32 bg-gradient-to-br from-red-500 to-orange-500 rounded-full flex items-center justify-center shadow-2xl animate-pulse">
            <i class="fas fa-exclamation-triangle text-white text-5xl"></i>
        </div>
        <div class="absolute -inset-4 bg-red-200 rounded-full opacity-20 animate-ping"></div>
    </div>

    <!-- Message d'erreur -->
    <div class="text-center max-w-2xl mx-auto">
        <p class="text-lg md:text-xl text-gray-600 mb-8 leading-relaxed">
            Oups ! La requête envoyée au serveur est incorrecte ou mal formée. 
            <br class="hidden md:block">
            Veuillez vérifier les informations et réessayer.
        </p>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <a href="{{ url()->previous() }}" 
               class="px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 flex items-center gap-3 group">
                <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                <span>Retour à la page précédente</span>
            </a>
            
            <a href="{{ url('/') }}" 
               class="px-8 py-4 bg-white text-gray-700 font-semibold rounded-xl border border-gray-300 shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 flex items-center gap-3 group">
                <i class="fas fa-home group-hover:scale-110 transition-transform"></i>
                <span>Page d'accueil</span>
            </a>
        </div>

        <!-- Informations supplémentaires -->
        <div class="mt-12 p-6 bg-white/80 backdrop-blur-sm rounded-2xl shadow-md border border-gray-200/50 max-w-md mx-auto">
            <div class="flex items-center justify-center gap-3 text-sm text-gray-500">
                <i class="fas fa-info-circle text-blue-500"></i>
                <span>Si le problème persiste, contactez le support technique au +229 01 94 11 94 76</span>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    
    .animate-float {
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes glow {
        0%, 100% { box-shadow: 0 0 20px rgba(239, 68, 68, 0.3); }
        50% { box-shadow: 0 0 40px rgba(239, 68, 68, 0.6); }
    }
    
    .animate-glow {
        animation: glow 2s ease-in-out infinite;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation supplémentaire pour l'icône
        const icon = document.querySelector('.fa-exclamation-triangle');
        if (icon) {
            setInterval(() => {
                icon.classList.toggle('rotate-12');
            }, 2000);
        }
    });
</script>
@endsection