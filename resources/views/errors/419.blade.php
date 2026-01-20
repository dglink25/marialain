@extends('layouts.app')

@section('title', 'Session expirée')

@section('content')
<div class="min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-slate-50 to-amber-50/30 px-4 py-8">
    <!-- Icône animée -->
    <div class="mb-8 relative">
        <div class="w-32 h-32 bg-gradient-to-br from-amber-500 to-yellow-500 rounded-full flex items-center justify-center shadow-2xl animate-server-glow">
            <i class="fas fa-hourglass-half text-white text-5xl"></i>
        </div>
        <div class="absolute -inset-4 bg-amber-200 rounded-full opacity-20 animate-ping"></div>
        <!-- Effets décoratifs -->
        <div class="absolute -top-6 -right-6 w-24 h-24 bg-gradient-to-br from-amber-400/10 to-yellow-400/10 rounded-full blur-xl"></div>
        <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-gradient-to-br from-blue-400/10 to-indigo-400/10 rounded-full blur-xl"></div>
    </div>

    <!-- Titre -->
    <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-amber-600 to-yellow-600 bg-clip-text text-transparent mb-4">
        Session expirée
    </h1>

    <!-- Message d'erreur -->
    <div class="text-center max-w-2xl mx-auto">
        <p class="text-lg md:text-xl text-gray-600 mb-8 leading-relaxed">
            Votre session de sécurité a expiré en raison d'une inactivité prolongée.
            <br class="hidden md:block">
            Pour des raisons de sécurité, veuillez vous reconnecter.
        </p>

        <!-- Détails techniques -->
        <div class="mb-10 p-6 bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200/50 max-w-xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-red-100 to-red-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-clock text-red-500 text-2xl"></i>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-1">15 minutes</h4>
                    <p class="text-gray-600 text-sm">Temps d'inactivité</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-shield-alt text-blue-500 text-2xl"></i>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-1">Sécurité</h4>
                    <p class="text-gray-600 text-sm">Protection automatique</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-100 to-green-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-user-clock text-green-500 text-2xl"></i>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-1">Reconnexion</h4>
                    <p class="text-gray-600 text-sm">Simple et rapide</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
            <a href="{{ route('login') }}" 
               class="px-8 py-4 bg-gradient-to-r from-amber-500 to-yellow-500 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 flex items-center gap-3 group">
                <i class="fas fa-sign-in-alt group-hover:scale-110 transition-transform duration-300"></i>
                <span>Se reconnecter</span>
            </a>
            
            <a href="{{ url('/') }}" 
               class="px-8 py-4 bg-white text-gray-700 font-semibold rounded-xl border border-gray-300 shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 flex items-center gap-3 group">
                <i class="fas fa-home group-hover:scale-110 transition-transform duration-300"></i>
                <span>Page d'accueil</span>
            </a>
        </div>

        <!-- Conseils -->
        <div class="mb-8 p-6 bg-gradient-to-r from-amber-50 to-yellow-50 rounded-2xl shadow-md border border-amber-200/50 max-w-md mx-auto">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-yellow-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-lightbulb text-white"></i>
                </div>
                <h3 class="font-semibold text-gray-800">Conseils de prévention</h3>
            </div>
            <div class="text-left space-y-2">
                <div class="flex items-start gap-2">
                    <i class="fas fa-check text-amber-500 mt-1"></i>
                    <span class="text-gray-600 text-sm">Gardez votre navigateur ouvert pendant la session</span>
                </div>
                <div class="flex items-start gap-2">
                    <i class="fas fa-check text-amber-500 mt-1"></i>
                    <span class="text-gray-600 text-sm">Évitez de partager vos identifiants</span>
                </div>
                <div class="flex items-start gap-2">
                    <i class="fas fa-check text-amber-500 mt-1"></i>
                    <span class="text-gray-600 text-sm">Déconnectez-vous proprement après utilisation</span>
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
                    En cas de difficultés de connexion répétées
                </p>
            </div>
        </div>

        <!-- Horodatage -->
        <div class="mt-8 text-sm text-gray-400">
            <i class="fas fa-calendar-alt mr-2"></i>
            Session expirée le {{ now()->format('d/m/Y à H:i') }}
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
    
    @keyframes hourglassFlow {
        0% { transform: rotate(0deg); }
        25% { transform: rotate(90deg); }
        50% { transform: rotate(180deg); }
        75% { transform: rotate(270deg); }
        100% { transform: rotate(360deg); }
    }
    
    .animate-server-glow {
        animation: serverGlow 2s ease-in-out infinite;
    }
    
    .fa-hourglass-half {
        animation: hourglassFlow 4s linear infinite;
    }
    
    @keyframes sandFall {
        0% { 
            transform: translateY(0);
            opacity: 0;
        }
        50% { 
            transform: translateY(10px);
            opacity: 1;
        }
        100% { 
            transform: translateY(20px);
            opacity: 0;
        }
    }
    
    /* Effet de sable qui tombe */
    .relative::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 4px;
        height: 4px;
        background: #f59e0b;
        border-radius: 50%;
        animation: sandFall 1.5s linear infinite;
        opacity: 0;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation de l'horloge
        const hourglass = document.querySelector('.fa-hourglass-half');
        let flip = false;
        
        setInterval(() => {
            flip = !flip;
            hourglass.classList.toggle('fa-hourglass-half');
            hourglass.classList.toggle('fa-hourglass-end');
        }, 2000);
        
        // Effet de particules de sable
        const container = document.querySelector('.relative');
        for (let i = 0; i < 5; i++) {
            setTimeout(() => {
                createSandParticle(container);
            }, i * 300);
        }
        
        function createSandParticle(container) {
            const sand = document.createElement('div');
            sand.className = 'absolute w-1 h-1 bg-amber-400 rounded-full';
            sand.style.left = '50%';
            sand.style.top = 'calc(50% - 40px)';
            sand.style.transform = 'translate(-50%, 0)';
            
            container.appendChild(sand);
            
            // Animation
            let position = 0;
            const interval = setInterval(() => {
                position += 2;
                sand.style.top = `calc(50% - 40px + ${position}px)`;
                sand.style.opacity = 1 - (position / 80);
                
                if (position > 80) {
                    clearInterval(interval);
                    sand.remove();
                }
            }, 20);
            
            // Recréer la particule après disparition
            setTimeout(() => {
                createSandParticle(container);
            }, 1500);
        }
    });
</script>
@endsection