@extends('layouts.app')

@section('title', 'Erreur 500 - Problème interne')

@section('content')
<div class="min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-slate-50 to-red-50/30 px-4 py-8">
    <!-- Icône animée -->
    <div class="mb-8 relative">
        <div class="w-32 h-32 bg-gradient-to-br from-red-600 to-pink-600 rounded-full flex items-center justify-center shadow-2xl animate-server-glow">
            <i class="fas fa-server text-white text-5xl"></i>
        </div>
        <div class="absolute -inset-4 bg-red-200 rounded-full opacity-20 animate-ping"></div>
        <!-- Effets décoratifs -->
        <div class="absolute -top-6 -right-6 w-24 h-24 bg-gradient-to-br from-red-400/10 to-pink-400/10 rounded-full blur-xl"></div>
        <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-gradient-to-br from-blue-400/10 to-indigo-400/10 rounded-full blur-xl"></div>
    </div>

    <!-- Titre -->
    <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-red-600 to-red-800 bg-clip-text text-transparent mb-4">
        Problème interne du serveur
    </h1>

    <!-- Message d'erreur -->
    <div class="text-center max-w-2xl mx-auto">
        <p class="text-lg md:text-xl text-gray-600 mb-8 leading-relaxed">
            Une erreur est survenue sur le serveur. L'équipe technique a été notifiée.
            <br class="hidden md:block">
            Merci de revenir en arrière pour continuer votre navigation.
        </p>

        <!-- Détails techniques (optionnel) -->
        <div class="mb-10 p-6 bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200/50 max-w-xl mx-auto">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-orange-100 to-orange-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-tools text-orange-500 text-xl"></i>
                    </div>
                </div>
                <div class="text-left">
                    <h3 class="font-semibold text-gray-800 mb-2">Que s'est-il passé ?</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Le serveur a rencontré une erreur inattendue qui l'empêche de traiter votre requête.
                        Notre équipe technique a été automatiquement alertée et travaille à la résolution du problème.
                    </p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
            <a href="{{ url()->previous() }}" 
               class="px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 flex items-center gap-3 group">
                <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform duration-300"></i>
                <span>Retour à la page précédente</span>
            </a>
            
            <a href="{{ url('/') }}" 
               class="px-8 py-4 bg-white text-gray-700 font-semibold rounded-xl border border-gray-300 shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 flex items-center gap-3 group">
                <i class="fas fa-home group-hover:scale-110 transition-transform duration-300"></i>
                <span>Page d'accueil</span>
            </a>
        </div>

        <!-- Support -->
        <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl shadow-md border border-blue-200/50 max-w-md mx-auto">
            <div class="flex flex-col items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-headset text-white"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">Support technique</h3>
                </div>
                <a href="mailto:cpegmariealain@gmail.com" 
                   class="text-blue-600 hover:text-blue-700 font-medium text-lg flex items-center gap-2 group">
                    <i class="fas fa-envelope group-hover:scale-110 transition-transform"></i>
                    <span>cpegmariealain@gmail.com</span>
                </a>
                <p class="text-sm text-gray-500 text-center mt-2">
                    Contactez-nous si le problème persiste
                </p>
            </div>
        </div>

        <!-- Statut -->
        <div class="mt-8 flex items-center justify-center gap-3">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-sm text-gray-500">Support notifié</span>
            </div>
            <div class="h-4 w-px bg-gray-300"></div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse"></div>
                <span class="text-sm text-gray-500">En cours d'analyse</span>
            </div>
            <div class="h-4 w-px bg-gray-300"></div>
            <div class="text-sm text-gray-500">
                ID: ERR-{{ now()->format('His') }}
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes serverGlow {
        0%, 100% { 
            box-shadow: 0 0 30px rgba(220, 38, 38, 0.3),
                       0 10px 30px rgba(220, 38, 38, 0.2),
                       inset 0 0 20px rgba(255, 255, 255, 0.1);
            transform: scale(1);
        }
        50% { 
            box-shadow: 0 0 50px rgba(220, 38, 38, 0.6),
                       0 15px 40px rgba(220, 38, 38, 0.3),
                       inset 0 0 30px rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    
    .animate-server-glow {
        animation: serverGlow 2s ease-in-out infinite;
    }
    
    .animate-float {
        animation: float 3s ease-in-out infinite;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation aléatoire de l'icône serveur
        const serverIcon = document.querySelector('.fa-server');
        if (serverIcon) {
            let rotation = 0;
            setInterval(() => {
                rotation = (rotation + 5) % 360;
                serverIcon.style.transform = `rotate(${rotation}deg)`;
                serverIcon.style.transition = 'transform 0.5s ease';
            }, 3000);
        }
        
        // Effet de scintillement pour les points de statut
        const statusDots = document.querySelectorAll('.animate-pulse');
        statusDots.forEach((dot, index) => {
            setInterval(() => {
                dot.classList.toggle('opacity-50');
            }, 1000 + index * 200);
        });
    });
</script>
@endsection