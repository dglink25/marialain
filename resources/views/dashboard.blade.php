<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Super-Admin') }}
            </h2>
            <div class="text-sm text-gray-500">
                {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Cartes de statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Utilisateurs totaux -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Utilisateurs totaux</p>
                            <p class="text-3xl font-bold mt-2">{{ \App\Models\User::count() }}</p>
                            <p class="text-blue-200 text-xs mt-1">
                                +{{ \App\Models\User::whereDate('created_at', '>=', now()->subDays(7))->count() }} cette semaine
                            </p>
                        </div>
                        <div class="bg-blue-400 bg-opacity-20 p-3 rounded-full">
                            <i class="fas fa-users text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Élèves inscrits -->
                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Élèves inscrits</p>
                            <p class="text-3xl font-bold mt-2">{{ \App\Models\Student::count() }}</p>
                            <p class="text-green-200 text-xs mt-1">
                                {{ \App\Models\Student::whereDate('created_at', '>=', now()->subDays(7))->count() }} nouveaux
                            </p>
                        </div>
                        <div class="bg-green-400 bg-opacity-20 p-3 rounded-full">
                            <i class="fas fa-user-graduate text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Classes actives -->
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Classes actives</p>
                            <p class="text-3xl font-bold mt-2">{{ \App\Models\Classe::count() }}</p>
                            <p class="text-purple-200 text-xs mt-1">
                                Tous niveaux confondus
                            </p>
                        </div>
                        <div class="bg-purple-400 bg-opacity-20 p-3 rounded-full">
                            <i class="fas fa-school text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Années académiques -->
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-orange-100 text-sm font-medium">Années académiques</p>
                            <p class="text-3xl font-bold mt-2">{{ \App\Models\AcademicYear::count() }}</p>
                            <p class="text-orange-200 text-xs mt-1">
                                {{ \App\Models\AcademicYear::where('is_active', true)->first()->name ?? 'Aucune active' }}
                            </p>
                        </div>
                        <div class="bg-orange-400 bg-opacity-20 p-3 rounded-full">
                            <i class="fas fa-calendar-alt text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grille principale -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Colonne de gauche -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Activités récentes -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Activités Récentes</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                @php
                                    $activities = [
                                        ['icon' => 'user-plus', 'color' => 'green', 'text' => 'Nouvel utilisateur créé', 'time' => 'Il y a 5 min'],
                                        ['icon' => 'chalkboard-teacher', 'color' => 'blue', 'text' => 'Enseignant assigné à une classe', 'time' => 'Il y a 15 min'],
                                        ['icon' => 'user-graduate', 'color' => 'purple', 'text' => 'Inscription élève validée', 'time' => 'Il y a 30 min'],
                                        ['icon' => 'cog', 'color' => 'orange', 'text' => 'Paramètres système mis à jour', 'time' => 'Il y a 1 heure'],
                                    ];
                                @endphp
                                
                                @foreach($activities as $activity)
                                <div class="flex items-center space-x-4 p-3 hover:bg-gray-50 rounded-lg transition">
                                    <div class="bg-{{ $activity['color'] }}-100 p-2 rounded-full">
                                        <i class="fas fa-{{ $activity['icon'] }} text-{{ $activity['color'] }}-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-800">{{ $activity['text'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $activity['time'] }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Statistiques d'utilisation -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Statistiques d'Utilisation</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-600 mb-3">Connexions par rôle</h4>
                                    <div class="space-y-3">
                                        @php
                                            $roles = [
                                                ['name' => 'Enseignants', 'count' => 45, 'color' => 'blue'],
                                                ['name' => 'Élèves', 'count' => 320, 'color' => 'green'],
                                                ['name' => 'Administrateurs', 'count' => 8, 'color' => 'purple'],
                                                ['name' => 'Parents', 'count' => 280, 'color' => 'orange'],
                                            ];
                                        @endphp
                                        
                                        @foreach($roles as $role)
                                        <div>
                                            <div class="flex justify-between text-sm mb-1">
                                                <span class="text-gray-700">{{ $role['name'] }}</span>
                                                <span class="font-medium">{{ $role['count'] }}</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-{{ $role['color'] }}-500 h-2 rounded-full" 
                                                     style="width: {{ ($role['count'] / 650) * 100 }}%"></div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-600 mb-3">Performance système</h4>
                                    <div class="space-y-4">
                                        <div>
                                            <div class="flex justify-between text-sm mb-1">
                                                <span class="text-gray-700">Utilisation CPU</span>
                                                <span class="font-medium">42%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-green-500 h-2 rounded-full" style="width: 42%"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex justify-between text-sm mb-1">
                                                <span class="text-gray-700">Utilisation mémoire</span>
                                                <span class="font-medium">68%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-yellow-500 h-2 rounded-full" style="width: 68%"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex justify-between text-sm mb-1">
                                                <span class="text-gray-700">Espace disque</span>
                                                <span class="font-medium">35%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-500 h-2 rounded-full" style="width: 35%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Colonne de droite -->
                <div class="space-y-8">
                    
                    <!-- Actions rapides -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Actions Rapides</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <a href="{{ route('admin.users.index') }}" 
                                   class="flex items-center space-x-3 p-3 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                    <i class="fas fa-users text-lg"></i>
                                    <span>Gérer les utilisateurs</span>
                                </a>
                                <a href="{{ route('admin.academic_years.index') }}" 
                                   class="flex items-center space-x-3 p-3 text-green-600 hover:bg-green-50 rounded-lg transition">
                                    <i class="fas fa-calendar-alt text-lg"></i>
                                    <span>Années académiques</span>
                                </a>
                                <a href="{{ route('admin.settings') }}" 
                                   class="flex items-center space-x-3 p-3 text-purple-600 hover:bg-purple-50 rounded-lg transition">
                                    <i class="fas fa-cog text-lg"></i>
                                    <span>Paramètres système</span>
                                </a>
                                <a href="{{ route('admin.backup') }}" 
                                   class="flex items-center space-x-3 p-3 text-orange-600 hover:bg-orange-50 rounded-lg transition">
                                    <i class="fas fa-database text-lg"></i>
                                    <span>Sauvegarde données</span>
                                </a>
                                <a href="{{ route('admin.logs') }}" 
                                   class="flex items-center space-x-3 p-3 text-red-600 hover:bg-red-50 rounded-lg transition">
                                    <i class="fas fa-file-alt text-lg"></i>
                                    <span>Journaux système</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Informations système -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Informations Système</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Version plateforme</span>
                                    <span class="text-sm font-medium">v2.1.0</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Dernière sauvegarde</span>
                                    <span class="text-sm font-medium">24/10/2023 02:00</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Statut serveur</span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-circle mr-1" style="font-size: 6px;"></i>
                                        En ligne
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Temps fonctionnement</span>
                                    <span class="text-sm font-medium">15j 8h 42m</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tâches planifiées -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Tâches Planifiées</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                @php
                                    $tasks = [
                                        ['name' => 'Sauvegarde automatique', 'status' => 'success', 'time' => '02:00'],
                                        ['name' => 'Nettoyage cache', 'status' => 'pending', 'time' => '03:00'],
                                        ['name' => 'Rapports quotidiens', 'status' => 'success', 'time' => '06:00'],
                                        ['name' => 'Sync. données', 'status' => 'warning', 'time' => '12:00'],
                                    ];
                                @endphp
                                
                                @foreach($tasks as $task)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        @if($task['status'] === 'success')
                                            <i class="fas fa-check-circle text-green-500"></i>
                                        @elseif($task['status'] === 'warning')
                                            <i class="fas fa-exclamation-circle text-yellow-500"></i>
                                        @else
                                            <i class="fas fa-clock text-gray-400"></i>
                                        @endif
                                        <span class="text-sm text-gray-700">{{ $task['name'] }}</span>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $task['time'] }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section basse pour les rapports -->
            <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Rapports récents -->
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Rapports Récents</h3>
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-800">Voir tout</a>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach([
                                ['name' => 'Rapport d\'activité mensuel', 'date' => 'Oct 2023', 'type' => 'pdf'],
                                ['name' => 'Statistiques inscriptions', 'date' => '23/10/2023', 'type' => 'excel'],
                                ['name' => 'Audit de sécurité', 'date' => '20/10/2023', 'type' => 'pdf'],
                            ] as $report)
                            <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-file-{{ $report['type'] }} text-blue-500"></i>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $report['name'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $report['date'] }}</p>
                                    </div>
                                </div>
                                <button class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Alertes système -->
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Alertes Système</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3 p-3 bg-yellow-50 rounded-lg">
                                <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Espace disque à 85%</p>
                                    <p class="text-xs text-gray-600">Nettoyage recommandé</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                                <i class="fas fa-check-circle text-green-500"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Système à jour</p>
                                    <p class="text-xs text-gray-600">Dernière vérification: aujourd'hui</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                                <i class="fas fa-info-circle text-blue-500"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Mise à jour disponible</p>
                                    <p class="text-xs text-gray-600">Version 2.2.0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        .hover-lift {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation des barres de progression
            const progressBars = document.querySelectorAll('.bg-blue-500, .bg-green-500, .bg-yellow-500, .bg-purple-500');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 500);
            });

            // Actualisation automatique du temps
            function updateTime() {
                document.querySelector('[x-text="now().format(\'d/m/Y H:i\')"]').textContent = 
                    new Date().toLocaleString('fr-FR', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
            }
            setInterval(updateTime, 60000);
        });
    </script>
    @endpush
</x-app-layout>