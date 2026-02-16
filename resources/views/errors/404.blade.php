@extends('layouts.app')

@section('title', 'Page non trouvée')

@section('content')
<div class="min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-slate-50 to-purple-50/30 px-4 py-8">
    <!-- Icône animée -->
    <div class="mb-8 relative">
        <div class="w-32 h-32 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center shadow-2xl animate-server-glow">
            <i class="fas fa-search text-white text-5xl"></i>
        </div>
        <div class="absolute -inset-4 bg-purple-200 rounded-full opacity-20 animate-ping"></div>
        <!-- Effets décoratifs -->
        <div class="absolute -top-6 -right-6 w-24 h-24 bg-gradient-to-br from-purple-400/10 to-pink-400/10 rounded-full blur-xl"></div>
        <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-gradient-to-br from-blue-400/10 to-indigo-400/10 rounded-full blur-xl"></div>
    </div>

    <!-- Titre -->
    <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-4">
        Page non trouvée
    </h1>

    <!-- Message d'erreur -->
    <div class="text-center max-w-2xl mx-auto">
        <p class="text-lg md:text-xl text-gray-600 mb-8 leading-relaxed">
            Oups ! La page que vous recherchez semble introuvable.
            <br class="hidden md:block">
            Elle a peut-être été déplacée, supprimée ou n'a jamais existé.
        </p>

        <!-- Détails techniques -->
        <div class="mb-10 p-6 bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200/50 max-w-xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-100 to-purple-50 rounded-xl flex items-center justify-center">
                            <i class="fas fa-map-signs text-purple-500"></i>
                        </div>
                    </div>
                    <div class="text-left">
                        <h4 class="font-semibold text-gray-800 mb-1">URL incorrecte</h4>
                        <p class="text-gray-600 text-sm">Vérifiez l'adresse saisie</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-pink-100 to-pink-50 rounded-xl flex items-center justify-center">
                            <i class="fas fa-route text-pink-500"></i>
                        </div>
                    </div>
                    <div class="text-left">
                        <h4 class="font-semibold text-gray-800 mb-1">Lien rompu</h4>
                        <p class="text-gray-600 text-sm">Le lien pourrait être obsolète</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-50 rounded-xl flex items-center justify-center">
                            <i class="fas fa-archive text-blue-500"></i>
                        </div>
                    </div>
                    <div class="text-left">
                        <h4 class="font-semibold text-gray-800 mb-1">Contenu déplacé</h4>
                        <p class="text-gray-600 text-sm">La page a été réorganisée</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-100 to-indigo-50 rounded-xl flex items-center justify-center">
                            <i class="fas fa-ban text-indigo-500"></i>
                        </div>
                    </div>
                    <div class="text-left">
                        <h4 class="font-semibold text-gray-800 mb-1">Accès restreint</h4>
                        <p class="text-gray-600 text-sm">Page supprimée ou protégée</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- URL demandée -->
        <div class="mb-8 p-4 bg-gray-50/80 rounded-xl border border-gray-200 max-w-lg mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas fa-link text-gray-400"></i>
                    <span class="text-sm text-gray-500 truncate">URL recherchée :</span>
                </div>
                <code class="text-xs text-gray-600 truncate max-w-[200px]">{{ url()->current() }}</code>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
            <a href="{{ url()->previous() }}" 
               class="px-8 py-4 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 flex items-center gap-3 group">
                <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform duration-300"></i>
                <span>Page précédente</span>
            </a>
            
            <a href="{{ url('/home') }}" 
               class="px-8 py-4 bg-white text-gray-700 font-semibold rounded-xl border border-gray-300 shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 flex items-center gap-3 group">
                <i class="fas fa-home group-hover:scale-110 transition-transform duration-300"></i>
                <span>Page d'accueil</span>
            </a>
            
            <button onclick="window.location.reload()" 
               class="px-8 py-4 bg-gradient-to-r from-pink-500 to-rose-500 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 flex items-center gap-3 group">
                <i class="fas fa-redo group-hover:rotate-180 transition-transform duration-500"></i>
                <span>Réessayer</span>
            </button>
        </div>

        <!-- Recherche -->
        <div class="mb-8 p-6 bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl shadow-md border border-purple-200/50 max-w-md mx-auto">
            <div class="flex flex-col items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-compass text-white"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">Naviguer autrement</h3>
                </div>
                <p class="text-sm text-gray-600 text-center mb-3">
                    Utilisez le menu principal ou la barre de recherche pour trouver ce que vous cherchez
                </p>
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
                    Signalez cette erreur si vous pensez qu'il s'agit d'un problème technique
                </p>
            </div>
        </div>

        <!-- Horodatage -->
        <div class="mt-8 flex items-center justify-center gap-4 text-sm text-gray-400">
            <div class="flex items-center gap-2">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ now()->format('d/m/Y') }}</span>
            </div>
            <div class="h-4 w-px bg-gray-300"></div>
            <div class="flex items-center gap-2">
                <i class="fas fa-clock"></i>
                <span>{{ now()->format('H:i') }}</span>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes serverGlow {
        0%, 100% { 
            box-shadow: 0 0 30px rgba(168, 85, 247, 0.3),
                       0 10px 30px rgba(168, 85, 247, 0.2),
                       inset 0 0 20px rgba(255, 255, 255, 0.1);
            transform: scale(1);
        }
        50% { 
            box-shadow: 0 0 50px rgba(168, 85, 247, 0.6),
                       0 15px 40px rgba(168, 85, 247, 0.3),
                       inset 0 0 30px rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }
    }
    
    @keyframes searchPulse {
        0%, 100% { 
            transform: scale(1) rotate(0deg);
        }
        25% { 
            transform: scale(1.1) rotate(5deg);
        }
        50% { 
            transform: scale(1.05) rotate(-5deg);
        }
        75% { 
            transform: scale(1.1) rotate(5deg);
        }
    }
    
    .animate-server-glow {
        animation: serverGlow 2s ease-in-out infinite;
    }
    
    .fa-search {
        animation: searchPulse 3s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        25% { transform: translateY(-10px) rotate(5deg); }
        50% { transform: translateY(-5px) rotate(-5deg); }
        75% { transform: translateY(-10px) rotate(5deg); }
    }
    
    .animate-float {
        animation: float 4s ease-in-out infinite;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation de l'icône de recherche
        const searchIcon = document.querySelector('.fa-search');
        let isSearching = false;
        
        setInterval(() => {
            if (!isSearching) {
                isSearching = true;
                searchIcon.classList.remove('fa-search');
                searchIcon.classList.add('fa-search-location');
                
                setTimeout(() => {
                    searchIcon.classList.remove('fa-search-location');
                    searchIcon.classList.add('fa-search');
                    isSearching = false;
                }, 1000);
            }
        }, 4000);
        
        // Effet de particules de recherche
        const container = document.querySelector('.relative');
        createSearchParticles(container);
        
        function createSearchParticles(container) {
            const particles = 8;
            
            for (let i = 0; i < particles; i++) {
                setTimeout(() => {
                    const particle = document.createElement('div');
                    particle.className = 'absolute w-2 h-2 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full opacity-70';
                    
                    // Position aléatoire autour de l'icône
                    const angle = (i * (360 / particles)) * (Math.PI / 180);
                    const radius = 60;
                    const x = radius * Math.cos(angle);
                    const y = radius * Math.sin(angle);
                    
                    particle.style.left = `calc(50% + ${x}px)`;
                    particle.style.top = `calc(50% + ${y}px)`;
                    
                    container.appendChild(particle);
                    
                    // Animation de la particule
                    let anglePos = angle;
                    const interval = setInterval(() => {
                        anglePos += 0.02;
                        const newX = radius * Math.cos(anglePos);
                        const newY = radius * Math.sin(anglePos);
                        
                        particle.style.left = `calc(50% + ${newX}px)`;
                        particle.style.top = `calc(50% + ${newY}px)`;
                        
                        // Changement d'opacité
                        const opacity = 0.3 + 0.4 * Math.sin(anglePos);
                        particle.style.opacity = opacity;
                    }, 50);
                    
                    // Nettoyer après un certain temps
                    setTimeout(() => {
                        clearInterval(interval);
                        particle.remove();
                        // Recréer une nouvelle particule
                        setTimeout(() => createSearchParticles(container), 100);
                    }, 10000);
                }, i * 300);
            }
        }
    });
</script>
@endsection