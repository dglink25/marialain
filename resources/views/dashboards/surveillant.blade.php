@extends('layouts.app')

@section('content')


<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- En-tête du Dashboard -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard Surveillant</h1>
                <p class="mt-1 text-sm text-gray-600">Bienvenue Surveillance et discipline des élèves.</p>
            </div>
           <div class="mt-4 md:mt-0 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                <p class="text-sm text-gray-600">Aujourd'hui: <span class="font-medium">{{ now()->format('d/m/Y') }}</span></p>
            </div>
        </div>
    </div>

    <!-- Cartes de statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Élèves surveillés -->
        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-300">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-user-friends text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Élèves surveillés</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $studentsCount }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Animation pour les cartes */
    .bg-white {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    
    .bg-white:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    /* Animation pour les incidents */
    .flex.items-start {
        animation: fadeInUp 0.5s ease-out;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Délais d'animation */
    .flex.items-start:nth-child(1) { animation-delay: 0.1s; }
    .flex.items-start:nth-child(2) { animation-delay: 0.2s; }
    .flex.items-start:nth-child(3) { animation-delay: 0.3s; }
    
    /* Badges de sévérité */
    .bg-red-100 { background-color: #fed7d7; }
    .bg-yellow-100 { background-color: #feebc8; }
    .bg-blue-100 { background-color: #bee3f8; }
</style>

<script>
    // Animation des statistiques au chargement
    document.addEventListener('DOMContentLoaded', function() {
        // Effet de comptage pour les statistiques
        const stats = document.querySelectorAll('.text-lg.font-medium');
        stats.forEach(stat => {
            const target = parseInt(stat.textContent.replace(/,/g, ''));
            const duration = 1500;
            const steps = 30;
            const increment = target / steps;
            let current = 0;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    stat.textContent = target.toLocaleString();
                    clearInterval(timer);
                } else {
                    stat.textContent = Math.floor(current).toLocaleString();
                }
            }, duration / steps);
        });
        
        // Mise à jour de l'heure actuelle
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            document.getElementById('current-time').textContent = timeString;
        }
        
        setInterval(updateCurrentTime, 1000);
        updateCurrentTime();
    });
</script>
@endsection