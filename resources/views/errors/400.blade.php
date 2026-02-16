@extends('layouts.app')

@section('title', 'Erreur 400 - Requête incorrecte')

@section('content')
<div class="min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-slate-50 to-blue-50/30 px-4 py-8">
    <!-- Icône animée -->
    <div class="mb-8 relative">
        <div class="w-32 h-32 bg-gradient-to-br from-amber-500 to-orange-500 rounded-full flex items-center justify-center shadow-2xl animate-server-glow">
            <i class="fas fa-exclamation-triangle text-white text-5xl"></i>
        </div>
        <div class="absolute -inset-4 bg-amber-200 rounded-full opacity-20 animate-ping"></div>
        <!-- Effets décoratifs -->
        <div class="absolute -top-6 -right-6 w-24 h-24 bg-gradient-to-br from-amber-400/10 to-orange-400/10 rounded-full blur-xl"></div>
        <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-gradient-to-br from-blue-400/10 to-indigo-400/10 rounded-full blur-xl"></div>
    </div>

    <!-- Titre -->
    <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-amber-600 to-orange-600 bg-clip-text text-transparent mb-4">
        Requête incorrecte
    </h1>

    <!-- Message d'erreur -->
    <div class="text-center max-w-2xl mx-auto">
        <p class="text-lg md:text-xl text-gray-600 mb-8 leading-relaxed">
            La requête envoyée au serveur est malformée ou contient des données invalides.
            <br class="hidden md:block">
            Veuillez vérifier les informations saisies et réessayer.
        </p>

        <!-- Détails techniques -->
        <div class="mb-10 p-6 bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200/50 max-w-xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-red-100 to-red-50 rounded-xl flex items-center justify-center">
                            <i class="fas fa-times text-red-500"></i>
                        </div>
                    </div>
                    <div class="text-left">
                        <h4 class="font-semibold text-gray-800 mb-1">Données invalides</h4>
                        <p class="text-gray-600 text-sm">Vérifiez les informations saisies</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-50 rounded-xl flex items-center justify-center">
                            <i class="fas fa-link text-blue-500"></i>
                        </div>
                    </div>
                    <div class="text-left">
                        <h4 class="font-semibold text-gray-800 mb-1">URL incorrecte</h4>
                        <p class="text-gray-600 text-sm">Vérifiez l'adresse saisie</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-100 to-green-50 rounded-xl flex items-center justify-center">
                            <i class="fas fa-cookie text-green-500"></i>
                        </div>
                    </div>
                    <div class="text-left">
                        <h4 class="font-semibold text-gray-800 mb-1">Session expirée</h4>
                        <p class="text-gray-600 text-sm">Vos cookies peuvent être invalides</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-100 to-purple-50 rounded-xl flex items-center justify-center">
                            <i class="fas fa-shield-alt text-purple-500"></i>
                        </div>
                    </div>
                    <div class="text-left">
                        <h4 class="font-semibold text-gray-800 mb-1">Sécurité</h4>
                        <p class="text-gray-600 text-sm">Validation de sécurité échouée</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
            <button onclick="window.location.reload()" 
               class="px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 flex items-center gap-3 group">
                <i class="fas fa-redo group-hover:rotate-180 transition-transform duration-500"></i>
                <span>Rafraîchir la page</span>
            </button>
            
            <a href="{{ url()->previous() }}" 
               class="px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 flex items-center gap-3 group">
                <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform duration-300"></i>
                <span>Page précédente</span>
            </a>
            
            <a href="{{ url('/home') }}" 
               class="px-8 py-4 bg-white text-gray-700 font-semibold rounded-xl border border-gray-300 shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 flex items-center gap-3 group">
                <i class="fas fa-home group-hover:scale-110 transition-transform duration-300"></i>
                <span>Accueil</span>
            </a>
        </div>

        <!-- Support -->
        <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl shadow-md border border-blue-200/50 max-w-md mx-auto">
            <div class="flex flex-col items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-headset text-white"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">Besoin d'aide ?</h3>
                </div>
                <a href="mailto:cpegmariealain@gmail.com" 
                   class="text-blue-600 hover:text-blue-700 font-medium text-lg flex items-center gap-2 group">
                    <i class="fas fa-envelope group-hover:scale-110 transition-transform"></i>
                    <span>cpegmariealain@gmail.com</span>
                </a>
                <p class="text-sm text-gray-500 text-center">
                    Notre équipe technique est là pour vous aider
                </p>
            </div>
        </div>

        <!-- Statut -->
        <div class="mt-8 text-sm text-gray-400">
            <i class="fas fa-clock mr-2"></i>
            {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</div>

<style>
    @keyframes serverGlow {
        0%, 100% { 
            box-shadow: 0 0 30px rgba(245, 158, 11, 0.3),
                       0 10px 30px rgba(245, 158, 11, 0.2),
                       inset 0 0 20px rgba(255, 255, 255, 0.1);
            transform: scale(1);
        }
        50% { 
            box-shadow: 0 0 50px rgba(245, 158, 11, 0.6),
                       0 15px 40px rgba(245, 158, 11, 0.3),
                       inset 0 0 30px rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }
    }
    
    .animate-server-glow {
        animation: serverGlow 2s ease-in-out infinite;
    }
    
    @keyframes warningPulse {
        0%, 100% { 
            opacity: 1;
            transform: scale(1);
        }
        50% { 
            opacity: 0.8;
            transform: scale(1.1);
        }
    }
    
    .fa-exclamation-triangle {
        animation: warningPulse 1.5s ease-in-out infinite;
    }
</style>
@endsection