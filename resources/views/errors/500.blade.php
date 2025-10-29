@extends('layouts.app')

@section('title', 'Erreur 500 - Problème interne')

@section('content')
<div class="min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-slate-50 to-red-50/30 px-4 py-8">
    <!-- Icône animée -->
    <div class="mb-8 relative">
        <div class="w-32 h-32 bg-gradient-to-br from-red-600 to-pink-600 rounded-full flex items-center justify-center shadow-2xl animate-pulse">
            <i class="fas fa-server text-white text-5xl"></i>
        </div>
        <div class="absolute -inset-4 bg-red-200 rounded-full opacity-20 animate-ping"></div>
    </div>

    <!-- Message d'erreur -->
    <div class="text-center max-w-2xl mx-auto">
        <p class="text-lg md:text-xl text-gray-600 mb-8 leading-relaxed">
            Une erreur est survenue sur le serveur. L'équipe technique a été notifiée.
            <br class="hidden md:block">
            Merci de revenir en arrière pour continuer votre navigation.
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

        <!-- Informations techniques -->
        <div class="mt-12 p-6 bg-white/80 backdrop-blur-sm rounded-2xl shadow-md border border-gray-200/50 max-w-md mx-auto">
            <div class="flex items-center justify-center gap-3 text-sm text-gray-500">
                <i class="fas fa-tools text-blue-500"></i>
                <span>Nous travaillons à résoudre le problème</span>
            </div>
        </div>

        <!-- Statut -->
        <div class="mt-6 flex items-center justify-center gap-2 text-sm text-gray-400">
            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
            <span>Support technique notifié</span>
        </div>
    </div>
</div>

<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-8px); }
    }
    
    .animate-float {
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes serverGlow {
        0%, 100% { 
            box-shadow: 0 0 20px rgba(220, 38, 38, 0.3),
                       inset 0 0 20px rgba(255, 255, 255, 0.1);
        }
        50% { 
            box-shadow: 0 0 40px rgba(220, 38, 38, 0.6),
                       inset 0 0 30px rgba(255, 255, 255, 0.2);
        }
    }
    
    .animate-server-glow {
        animation: serverGlow 2s ease-in-out infinite;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation du serveur
        const serverIcon = document.querySelector('.fa-server');
        if (serverIcon) {
            setInterval(() => {
                serverIcon.classList.toggle('text-red-200');
            }, 1000);
        }
    });
</script>
@endsection