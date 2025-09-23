<!DOCTYPE html>
<html lang="fr">
<head>
<<<<<<< HEAD
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>CPEG MARIE-ALAIN</title>

    <!-- Tailwind + Flowbite -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/flowbite@1.6.5/dist/flowbite.min.css" rel="stylesheet" />
</head>
<body class="antialiased bg-gray-100 text-gray-800">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="w-64 bg-white shadow-md hidden md:block">
            <div class="p-3 flex items-center gap-3 border-b">
                <img src="{{ asset('logo.png') }}" class="h-12" alt="Logo" />
                <span class="font-bold">CPEG MARIE-ALAIN</span>
            </div>

            <nav class="p-4 space-y-2">
                <a href="{{ route('home') }}" class="block px-3 py-2 rounded hover:bg-blue-50">Accueil</a>
                <a href="{{ route('students.create') }}" class="block px-3 py-2 rounded hover:bg-blue-50">Inscription en ligne</a>
=======
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPEG MARIE-ALAIN</title>
    <!-- Favicon -->
    <link rel="icon" href="http://static.photos/education/32x32/1" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Flowbite CSS -->
    <link href="https://unpkg.com/flowbite@1.6.5/dist/flowbite.min.css" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Loading Animation -->
    <style>
        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3b82f6;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .active-nav {
            background-color: #e0f2fe;
            color: #1d4ed8;
            font-weight: 600;
        }
        .active-nav i {
            color: #1d4ed8 !important;
        }
        @media (max-width: 768px) {
            .mobile-nav {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                z-index: 50;
                background: white;
                box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            }
        }
    </style>
</head>
<body class="antialiased bg-gray-50 text-gray-800">
    <!-- Loading Screen -->
    <div id="loading-screen" class="fixed inset-0 bg-white z-50 flex flex-col items-center justify-center">
        <div class="loader"></div>
        <p class="mt-4 text-gray-600">Veuillez patienter...</p>
    </div>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="w-64 bg-white shadow-md hidden md:block fixed h-full">
            <div class="p-2 flex items-center gap-3 border-b">
                <img src="{{ asset('logo.png') }}" class="h-12 rounded-lg" alt="Logo" />
                <span class="font-bold text-lg">CPEG MARIE-ALAIN</span>
            </div>
            <nav class="p-4 space-y-1">
                <a href="{{ route('home') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition">
                    <i class="fa fa-home w-5 text-gray-500"></i> <span class="ml-3">Accueil</span>
                </a>
                <a href="{{ route('students.create') }}" class="flex items-center px-4 py-2 rounded hover:bg-blue-50">
                    <i class="fa fa-user-plus w-5 text-gray-500"></i> <span class="ml-3">Inscription en ligne</span>
                </a>

>>>>>>> 00716f68044d81f210a9471f8b687e0882439ee8

                @if(auth()->check())
                    <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition">
                        <i class="fa fa-user w-5 text-gray-500"></i> <span class="ml-3">Mon Profil</span>
                    </a>

<<<<<<< HEAD
=======
                    <a href="{{ route('archives.index') }}" class="flex items-center px-4 py-2 hover:bg-gray-100">
                        <i class="fa fa-archive w-5 text-gray-500"></i> <span class="ml-3">Consulter les archives</span>
                    </a>

>>>>>>> 00716f68044d81f210a9471f8b687e0882439ee8
                    @switch(optional(auth()->user()->role)->name)
                        @case('directeur_primaire')
                            <a href="{{ route('directeur.dashboard') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition">
                                <i class="fa fa-chart-line w-5 text-gray-500"></i> <span class="ml-3">Dashboard Directeur</span>
                            </a>
                            <a href="{{ route('primaire.classe.classes') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition">
                                <i class="fa fa-school w-5 text-gray-500"></i> <span class="ml-3">Gestion des classes</span>
                            </a>
                            <a href="{{ route('primaire.enseignants.enseignants') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition">
                                <i class="fa fa-chalkboard-teacher w-5 text-gray-500"></i> <span class="ml-3">Gestion des enseignants</span>
                            </a>
                            <a href="{{ route('primaire.ecoliers.liste') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition">
                                <i class="fa fa-users w-5 text-gray-500"></i> <span class="ml-3">Gestion des écoliers</span>
                            </a>
                            @break

                        @case('teacher')
                            <a href="{{ route('teacher.dashboard') }}" class="flex items-center px-4 py-2 rounded hover:bg-gray-200 transition">
                                <i class="fa fa-chalkboard w-5 text-gray-500"></i> <span class="ml-3">Dashboard Enseignant</span>
                            </a>
                            <a href="{{ route('teacher.classes') }}" class="flex items-center px-4 py-2 rounded hover:bg-gray-200 transition">
                                <i class="fa fa-school w-5 text-gray-500"></i> <span class="ml-3">Mes classes</span>
                            </a>
                            @break

                        @case('censeur')
<<<<<<< HEAD
                            <a href="{{ route('censeur.dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-200">Dashboard Censeur</a>
                            <a href="{{ route('censeur.invitations.index') }}" class="block px-4 py-2 hover:bg-gray-200">Invitations enseignants</a>
                            <a href="{{ route('censeur.subjects.index') }}" class="block px-4 py-2 hover:bg-gray-200">Matières</a>
                            <a href="{{ route('censeur.classes.index') }}" class="block px-4 py-2 hover:bg-gray-200">Liste Classes</a>
=======
                            <a href="{{ route('censeur.dashboard') }}" class="flex items-center px-4 py-2 rounded hover:bg-gray-200 transition">
                                <i class="fa fa-user-shield w-5 text-gray-500"></i> <span class="ml-3">Dashboard Censeur</span>
                            </a>
                            <a href="{{ route('censeur.invitations.index') }}" class="flex items-center px-4 py-2 rounded hover:bg-gray-200 transition">
                                <i class="fa fa-envelope-open-text w-5 text-gray-500"></i> <span class="ml-3">Invitations enseignants</span>
                            </a>
                            <a href="{{ route('censeur.subjects.index') }}" class="flex items-center px-4 py-2 rounded hover:bg-gray-200 transition">
                                <i class="fa fa-book-open w-5 text-gray-500"></i> <span class="ml-3">Matières</span>
                            </a>
                            <a href="{{ route('censeur.classes.index') }}" class="flex items-center px-4 py-2 rounded hover:bg-gray-200 transition">
                                <i class="fa fa-school w-5 text-gray-500"></i> <span class="ml-3">Liste Classes</span>
                            </a>
>>>>>>> 00716f68044d81f210a9471f8b687e0882439ee8
                            @break

                        @case('surveillant')
                            <a href="{{ route('surveillant.dashboard') }}" class="flex items-center px-4 py-2 rounded hover:bg-gray-200 transition">
                                <i class="fa fa-user-secret w-5 text-gray-500"></i> <span class="ml-3">Dashboard Surveillant</span>
                            </a>
                            @break

                        @case('secretaire')
<<<<<<< HEAD
                            <a href="{{ route('secretaire.dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-200">Dashboard Secrétaire</a>
                            <a href="{{ route('admin.students.pending') }}" class="block px-4 py-2 rounded hover:bg-gray-200">Inscription en attente</a>
                            <a href="{{ route('admin.students.index') }}" class="block px-4 py-2 rounded hover:bg-gray-200">Liste Elèves</a>
                            <a href="{{ route('admin.classes.index') }}" class="block py-2 px-4 hover:bg-gray-200">Gestion de classes</a>
                            @break

                        @case('super_admin')
                            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-blue-50">Tableau de bord</a>
                            <a href="{{ route('admin.academic_years.index') }}" class="block py-2 px-4 hover:bg-gray-200">Années académiques</a>
=======
                            <a href="{{ route('secretaire.dashboard') }}" class="flex items-center px-4 py-2 rounded hover:bg-gray-200 transition">
                                <i class="fa fa-tachometer-alt w-5 text-gray-500"></i> <span class="ml-3">Dashboard Secrétaire</span>
                            </a>
                            
                            <a href="{{ route('admin.students.pending') }}" class="flex items-center px-4 py-2 rounded hover:bg-gray-200 transition">
                                <i class="fa fa-user-clock w-5 text-gray-500"></i> <span class="ml-3">Inscription en attente</span>
                            </a>
                            <a href="{{ route('admin.students.index') }}" class="flex items-center px-4 py-2 rounded hover:bg-gray-200 transition">
                                <i class="fas fa-users w-5 text-gray-500"></i> <span class="ml-3">Liste Elèves</span>
                            </a>
                            <a href="{{ route('admin.classes.index') }}" class="flex items-center px-4 py-2 rounded hover:bg-gray-200 transition">
                                <i class="fa fa-school w-5 text-gray-500"></i> <span class="ml-3">Gestion de classes</span>
                            </a>
                            @break

                        @case('super_admin')
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2 rounded hover:bg-blue-50 transition">
                                <i class="fa fa-cogs w-5 text-gray-500"></i> <span class="ml-3">Tableau de bord</span>
                            </a>
                            <a href="{{ route('admin.academic_years.index') }}" class="flex items-center px-4 py-2 rounded hover:bg-gray-200 transition">
                                <i class="fa fa-calendar-alt w-5 text-gray-500"></i> <span class="ml-3">Années académiques</span>
                            </a>
>>>>>>> 00716f68044d81f210a9471f8b687e0882439ee8
                            @break

                        @default
                            <span class="px-4 py-2 text-gray-500">Rôle non défini</span>
<<<<<<< HEAD
                    @endswitch
                @endif

                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2 rounded hover:bg-red-50">Déconnexion</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block px-3 py-2 rounded bg-blue-600 text-white">Se connecter</a>
                @endauth
            </nav>
        </aside>

        <!-- Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header mobile -->
            <header class="md:hidden bg-white shadow p-4 flex justify-between items-center">
                <button data-drawer-target="sidebar" data-drawer-toggle="sidebar" aria-controls="sidebar" class="p-2 text-gray-600">☰</button>
                <h1 class="font-semibold">MARI ALAIN</h1>
            </header>



            </div>

            <!-- Contenu principal -->
            <main class="flex-1 p-6">
=======
                    @endswitch

                @endif

                
                
                <div class="pt-2 mt-2 border-t">
                    @auth
                        {{-- Si l'utilisateur est connecté --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center px-4 py-2 text-red-600 hover:underline">
                                <i class="fas fa-sign-out-alt w-5 text-gray-500"></i> 
                                <span class="ml-3">Déconnexion</span>
                            </button>
                        </form>
                    @endauth

                    @guest
                        {{-- Si l'utilisateur n'est pas connecté --}}
                        <a href="{{ route('login') }}" class="flex items-center px-4 py-2 text-green-600 hover:underline">
                            <i class="fas fa-sign-in-alt w-5 text-gray-500"></i>
                            <span class="ml-3">Connexion</span>
                        </a>
                    @endguest
                </div>
                >
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col md:ml-64">

            <!-- Mobile Bottom Navigation -->
            <nav class="mobile-nav md:hidden grid grid-cols-4">
                <!-- Accueil -->
                <a href="{{ route('home') }}" class="flex flex-col items-center justify-center py-2 text-gray-600">
                    <i class="fa fa-home"></i>
                    <span class="text-xs mt-1">Accueil</span>
                </a>

                <!-- Inscription en ligne -->
                <a href="{{ route('students.create') }}" class="flex flex-col items-center justify-center py-2 text-gray-600">
                    <i class="fa fa-user-plus"></i>
                    <span class="text-xs mt-1">Inscription</span>
                </a>

                <!-- Profil -->
                @if(auth()->check())
                <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center py-2 text-gray-600">
                    <i class="fa fa-user"></i>
                    <span class="text-xs mt-1">Profil</span>
                </a>
                @endif

                <!-- Dashboard (adapté selon rôle) -->
                @if(auth()->check())
                    @switch(optional(auth()->user()->role)->name)
                        @case('directeur_primaire')
                            <a href="{{ route('directeur.dashboard') }}" class="flex flex-col items-center justify-center py-2 text-gray-600">
                                <i class="fa fa-chart-line"></i>
                                <span class="text-xs mt-1">Dashboard</span>
                            </a>
                            @break

                        @case('teacher')
                            <a href="{{ route('teacher.dashboard') }}" class="flex flex-col items-center justify-center py-2 text-gray-600">
                                <i class="fa fa-chalkboard"></i>
                                <span class="text-xs mt-1">Dashboard</span>
                            </a>
                            @break

                        @case('censeur')
                            <a href="{{ route('censeur.dashboard') }}" class="flex flex-col items-center justify-center py-2 text-gray-600">
                                <i class="fa fa-user-shield"></i>
                                <span class="text-xs mt-1">Dashboard</span>
                            </a>
                            @break

                        @case('surveillant')
                            <a href="{{ route('surveillant.dashboard') }}" class="flex flex-col items-center justify-center py-2 text-gray-600">
                                <i class="fa fa-user-secret"></i>
                                <span class="text-xs mt-1">Dashboard</span>
                            </a>
                            @break

                        @case('secretaire')
                            <a href="{{ route('secretaire.dashboard') }}" class="flex flex-col items-center justify-center py-2 text-gray-600">
                                <i class="fa fa-tachometer-alt"></i>
                                <span class="text-xs mt-1">Dashboard</span>
                            </a>
                            <a href="{{ route('admin.students.pending') }}" class="flex flex-col items-center justify-center py-2 text-gray-600">
                                <i class="fa fa-user-clock"></i>
                                <span class="text-xs mt-1">En attente</span>
                            </a>
                            <a href="{{ route('admin.students.index') }}" class="flex flex-col items-center justify-center py-2 text-gray-600">
                                <i class="fas fa-users"></i>
                                <span class="text-xs mt-1">Élèves</span>
                            </a>
                            <a href="{{ route('admin.classes.index') }}" class="flex flex-col items-center justify-center py-2 text-gray-600">
                                <i class="fa fa-school"></i>
                                <span class="text-xs mt-1">Classes</span>
                            </a>
                            @break

                        @case('super_admin')
                            <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center justify-center py-2 text-gray-600">
                                <i class="fa fa-cogs"></i>
                                <span class="text-xs mt-1">Dashboard</span>
                            </a>
                            <a href="{{ route('admin.academic_years.index') }}" class="flex flex-col items-center justify-center py-2 text-gray-600">
                                <i class="fa fa-calendar-alt"></i>
                                <span class="text-xs mt-1">Années</span>
                            </a>
                            @break
                    @endswitch
                @endif
            </nav>


            <!-- Main Content Area -->
            <main class="flex-1 p-4 md:p-6">
>>>>>>> 00716f68044d81f210a9471f8b687e0882439ee8
                @yield('content')
            
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/flowbite@1.6.5/dist/flowbite.js"></script>
<<<<<<< HEAD
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const userMenuButton = document.getElementById('userMenuButton');
            const userDropdown = document.getElementById('userDropdown');

            if (userMenuButton && userDropdown) {
                userMenuButton.addEventListener('click', () => {
                    userDropdown.classList.toggle('hidden');
                });

                document.addEventListener('click', (e) => {
                    if (!userMenuButton.contains(e.target) && !userDropdown.contains(e.target)) {
                        userDropdown.classList.add('hidden');
                    }
                });
            }
        });
    </script>
=======
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS animation
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Hide loading screen when page is loaded
        window.addEventListener('load', function() {
            document.getElementById('loading-screen').style.display = 'none';
        });

        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('hidden');
        });

        // User dropdown toggle
        document.getElementById('user-menu-button').addEventListener('click', function() {
            document.getElementById('user-dropdown').classList.toggle('hidden');
        });

        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            document.getElementById('current-time').textContent = timeString;
        }
        setInterval(updateTime, 1000);
        updateTime();

        // Highlight active nav link
        document.querySelectorAll('nav a').forEach(link => {
            if(link.href === window.location.href) {
                link.classList.add('active-nav');
            }
        });
    </script>

            </div>

        </div>

        <nav class="p-4 space-y-1">
            <a href="{{ route('home') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition">
                <i class="fa fa-home w-5 text-gray-500"></i> <span class="ml-3">Accueil</span>
            </a>

            @if(auth()->check())
                @switch(auth()->user()->role->name)
                    @case('directeur_primaire')
                        <a href="{{ route('directeur.dashboard') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition">
                            <i class="fa fa-chart-line w-5 text-gray-500"></i> <span class="ml-3">Dashboard Directeur</span>
                        </a>
                        <a href="{{ route('primaire.classe.classes') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition">
                            <i class="fa fa-school w-5 text-gray-500"></i> <span class="ml-3">Gestion des classes</span>
                        </a>
                        <a href="{{ route('primaire.enseignants.enseignants') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition">
                            <i class="fa fa-chalkboard-teacher w-5 text-gray-500"></i> <span class="ml-3">Gestion des enseignants</span>
                        </a>
                        <a href="{{ route('primaire.ecoliers.liste') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition">
                            <i class="fa fa-users w-5 text-gray-500"></i> <span class="ml-3">Gestion des écoliers</span>
                        </a>
                        @break

                    @case('teacher')
                        <a href="{{ route('teacher.dashboard') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition">
                            <i class="fa fa-book-open w-5 text-gray-500"></i> <span class="ml-3">Dashboard Enseignant</span>
                        </a>
                        <a href="{{ route('teacher.classes') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition">
                            <i class="fa fa-layer-group w-5 text-gray-500"></i> <span class="ml-3">Mes classes</span>
                        </a>
                        @break
                @endswitch

                <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition">
                    <i class="fa fa-user w-5 text-gray-500"></i> <span class="ml-3">Mon Profil</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-4 py-2 rounded-lg hover:bg-red-50 hover:text-red-700 transition">
                        <i class="fa fa-sign-out-alt w-5 text-gray-500"></i> <span class="ml-3">Déconnexion</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block px-4 py-2 rounded-lg bg-blue-600 text-white text-center hover:bg-blue-700 transition">
                    Se connecter
                </a>
            @endif
        </nav>
    </aside>

    <!-- Content -->
    <div class="flex-1 flex flex-col">
        <!-- Header mobile -->
        <header class="md:hidden bg-white border-b shadow-sm p-4 flex justify-between items-center">
            <button data-drawer-target="sidebar" data-drawer-toggle="sidebar" aria-controls="sidebar" class="p-2 text-gray-600">
                <i class="fa fa-bars text-xl"></i>
            </button>
            <h1 class="font-semibold text-lg text-gray-800">CPEG MARIE-ALAIN</h1>
        </header>

        
    </div>
</div>

<script src="https://unpkg.com/flowbite@1.6.5/dist/flowbite.js"></script>

>>>>>>> 00716f68044d81f210a9471f8b687e0882439ee8
</body>
</html>
