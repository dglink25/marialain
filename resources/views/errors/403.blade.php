@extends('layouts.app')

@section('title', 'Accès refusé')

@section('content')
<div class="min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-slate-50 to-red-50/30 px-4 py-8">
    <!-- Icône animée -->
    <div class="mb-8 relative">
        <div class="w-32 h-32 bg-gradient-to-br from-red-600 to-rose-600 rounded-full flex items-center justify-center shadow-2xl animate-server-glow">
            <i class="fas fa-lock text-white text-5xl"></i>
        </div>
        <div class="absolute -inset-4 bg-red-200 rounded-full opacity-20 animate-ping"></div>
        <!-- Effets décoratifs -->
        <div class="absolute -top-6 -right-6 w-24 h-24 bg-gradient-to-br from-red-400/10 to-rose-400/10 rounded-full blur-xl"></div>
        <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-gradient-to-br from-blue-400/10 to-indigo-400/10 rounded-full blur-xl"></div>
    </div>

    <!-- Titre -->
    <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-red-700 to-rose-700 bg-clip-text text-transparent mb-4">
        Accès refusé
    </h1>

    <!-- Message d'erreur -->
    <div class="text-center max-w-2xl mx-auto">
        <p class="text-lg md:text-xl text-gray-600 mb-8 leading-relaxed">
            Vous n'avez pas les autorisations nécessaires pour accéder à cette ressource.
            <br class="hidden md:block">
            Cette page est réservée à un usage spécifique ou nécessite des privilèges particuliers.
        </p>

    <!-- Détails techniques -->
    <div class="mb-10 p-6 bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200/50 max-w-xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-red-100 to-red-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-user-shield text-red-500 text-2xl"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-1">Permissions</h4>
                <p class="text-gray-600 text-sm">Droits d'accès insuffisants</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-amber-100 to-amber-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-user-tag text-amber-500 text-2xl"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-1">Rôle requis</h4>
                <p class="text-gray-600 text-sm">Privilèges spécifiques</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-key text-blue-500 text-2xl"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-1">Authentification</h4>
                <p class="text-gray-600 text-sm">Identification nécessaire</p>
            </div>
        </div>
    </div>

        <!-- Avertissement -->
        <div class="mb-8 p-6 bg-gradient-to-r from-red-50/80 to-rose-50/80 rounded-2xl shadow-md border border-red-200/50 max-w-md mx-auto">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-rose-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation text-white"></i>
                    </div>
                </div>
                <div class="text-left">
                    <h3 class="font-semibold text-gray-800 mb-2">Avertissement de sécurité</h3>
                    <p class="text-gray-600 text-sm">
                        Les tentatives répétées d'accès à des ressources non autorisées peuvent être enregistrées
                        et signalées à l'administration du système.
                    </p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
            <a href="{{ url()->previous() }}" 
               class="px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 flex items-center gap-3 group">
                <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform duration-300"></i>
                <span>Retour en arrière</span>
            </a>
            
            <a href="{{ url('/') }}" 
               class="px-8 py-4 bg-white text-gray-700 font-semibold rounded-xl border border-gray-300 shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 flex items-center gap-3 group">
                <i class="fas fa-home group-hover:scale-110 transition-transform duration-300"></i>
                <span>Page d'accueil</span>
            </a>
            
            @auth
            <a href="{{ route('dashboard') }}" 
               class="px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 flex items-center gap-3 group">
                <i class="fas fa-tachometer-alt group-hover:scale-110 transition-transform duration-300"></i>
                <span>Tableau de bord</span>
            </a>
            @else
            <a href="{{ route('login') }}" 
               class="px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 flex items-center gap-3 group">
                <i class="fas fa-sign-in-alt group-hover:scale-110 transition-transform duration-300"></i>
                <span>Se connecter</span>
            </a>
            @endauth
        </div>

        <!-- Conseils -->
        <div class="mb-8 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl shadow-md border border-blue-200/50 max-w-md mx-auto">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-question-circle text-white"></i>
                </div>
                <h3 class="font-semibold text-gray-800">Besoin d'accès ?</h3>
            </div>
            <div class="text-left space-y-3">
                <div class="flex items-start gap-2">
                    <i class="fas fa-user-check text-blue-500 mt-1"></i>
                    <span class="text-gray-600 text-sm">Assurez-vous d'être connecté avec le bon compte</span>
                </div>
                <div class="flex items-start gap-2">
                    <i class="fas fa-user-tie text-blue-500 mt-1"></i>
                    <span class="text-gray-600 text-sm">Contactez votre administrateur pour obtenir les droits nécessaires</span>
                </div>
                <div class="flex items-start gap-2">
                    <i class="fas fa-file-alt text-blue-500 mt-1"></i>
                    <span class="text-gray-600 text-sm">Vérifiez que vous avez les autorisations requises pour cette fonctionnalité</span>
                </div>
            </div>
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
                <p class="text-sm text-gray-500 text-center">
                    Contactez-nous pour demander les autorisations nécessaires
                </p>
            </div>
        </div>

        <!-- Log de sécurité -->
        <div class="mt-8 p-4 bg-gray-900/80 backdrop-blur-sm rounded-xl border border-gray-700/50 max-w-md mx-auto">
            <div class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-2 text-gray-300">
                    <i class="fas fa-shield-alt"></i>
                    <span>Log de sécurité</span>
                </div>
                <div class="text-gray-400">
                    Ref-ID: SEC-{{ now()->format('YmdHis') }}
                </div>
            </div>
            <div class="mt-2 text-xs text-gray-400 text-center">
                Cette tentative d'accès a été enregistrée pour des raisons de sécurité
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
    
    @keyframes lockShake {
        0%, 100% { 
            transform: translateX(0) rotate(0deg);
        }
        25% { 
            transform: translateX(-2px) rotate(-2deg);
        }
        50% { 
            transform: translateX(2px) rotate(2deg);
        }
        75% { 
            transform: translateX(-2px) rotate(-2deg);
        }
    }
    
    .animate-server-glow {
        animation: serverGlow 2s ease-in-out infinite;
    }
    
    .fa-lock {
        animation: lockShake 0.5s ease-in-out infinite;
    }
    
    @keyframes securityPulse {
        0%, 100% { 
            box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7);
        }
        70% { 
            box-shadow: 0 0 0 10px rgba(220, 38, 38, 0);
        }
        100% { 
            box-shadow: 0 0 0 0 rgba(220, 38, 38, 0);
        }
    }
    
    .security-pulse {
        animation: securityPulse 2s infinite;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation du cadenas
        const lockIcon = document.querySelector('.fa-lock');
        let isLocked = true;
        
        setInterval(() => {
            if (isLocked) {
                lockIcon.classList.remove('fa-lock');
                lockIcon.classList.add('fa-unlock');
                lockIcon.style.animation = 'none';
                
                setTimeout(() => {
                    lockIcon.classList.remove('fa-unlock');
                    lockIcon.classList.add('fa-lock');
                    lockIcon.style.animation = 'lockShake 0.5s ease-in-out infinite';
                    isLocked = false;
                }, 2000);
            }
        }, 6000);
        
        // Effet de pulsation de sécurité
        const container = document.querySelector('.relative');
        const pulse = document.createElement('div');
        pulse.className = 'absolute inset-0 rounded-full security-pulse';
        container.appendChild(pulse);
        
        // Effet de scan de sécurité
        const scanLine = document.createElement('div');
        scanLine.className = 'absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-red-500 to-transparent opacity-50';
        container.appendChild(scanLine);
        
        let scanPosition = 0;
        const scanInterval = setInterval(() => {
            scanPosition = (scanPosition + 1) % 100;
            scanLine.style.top = `${scanPosition}%`;
        }, 50);
        
        // Effet de particules de sécurité
        createSecurityParticles(container);
        
        function createSecurityParticles(container) {
            for (let i = 0; i < 5; i++) {
                setTimeout(() => {
                    const particle = document.createElement('div');
                    particle.className = 'absolute w-1 h-1 bg-gradient-to-r from-red-400 to-rose-400 rounded-full';
                    
                    // Position aléatoire
                    const x = Math.random() * 100;
                    const y = Math.random() * 100;
                    
                    particle.style.left = `${x}%`;
                    particle.style.top = `${y}%`;
                    
                    container.appendChild(particle);
                    
                    // Animation
                    let opacity = 0.7;
                    const fadeInterval = setInterval(() => {
                        opacity -= 0.02;
                        particle.style.opacity = opacity;
                        
                        if (opacity <= 0) {
                            clearInterval(fadeInterval);
                            particle.remove();
                        }
                    }, 100);
                }, i * 500);
            }
            
            // Recréer des particules
            setTimeout(() => createSecurityParticles(container), 3000);
        }
    });
</script>
@endsection