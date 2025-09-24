<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>CPEG MARIE-ALAIN</title>

    <!-- Tailwind + Flowbite -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/flowbite@1.6.5/dist/flowbite.min.css" rel="stylesheet" />
    <style>
        .scrollbar-hide {
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE 10+ */
        }
    </style>
</head>
<body class="antialiased bg-gray-50 text-gray-800">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 h-screen w-64  bg-[#195af0] text-white shadow-xl z-50 hidden md:block font-sans">
    <!-- Logo + Titre -->
    <div class="p-3 flex flex-col items-center gap-2 border-b border-blue-300 bg-[#195af0] text-white">
        <div class="bg-white rounded-full p-2 shadow">
            <img src="{{ asset('logo.png') }}" class="h-12 w-12 object-contain rounded-full" alt="Logo" />
        </div>
        <span class="font-bold text-lg text-white text-center">CPEG MARIE-ALAIN</span>
    </div>

    <!-- Navigation -->
     <div class="overflow-y-auto h-[calc(100vh-112px)]  space-y-1 scrollbar-hide">
        <nav class="p-4 space-y-1 text-base font-medium">
            <a href="{{ route('home') }}" class="block px-4 py-4 rounded-md hover:bg-[#63c6ff70] transition">
                🏠 Accueil
            </a>
            <a href="{{ route('students.create') }}" class="block px-4 py-4 rounded-md hover:bg-[#63c6ff70] transition">
                📝 Inscription en ligne
            </a>

            @auth
                <a href="{{ route('profile.edit') }}" class="block px-4 py-4 rounded-md hover:bg-[#63c6ff70] transition">
                    👤 Mon Profil
                </a>

                <a href="{{ route('archives.index') }}" class="block px-4 py-4 rounded-md hover:bg-[#63c6ff70] transition">
                    Mes Archives
                </a>

                @switch(optional(auth()->user()->role)->name)
                    @case('directeur_primaire')
                        <a href="{{ route('directeur.dashboard') }}" class="block px-4 py-4 rounded-md hover:bg-[#63c6ff70] transition">📊 Dashboard Directeur</a>
                        <a href="{{ route('primaire.classes') }}" class="block px-4 py-4 rounded-md hover:bg-[#63c6ff70] transition">🏫 Gestion des classes</a>
                        <a href="{{ route('primaire.enseignants.enseignants') }}" class="block px-4 py-4 rounded-md hover:bg-[#63c6ff70] transition">👨‍🏫 Gestion des enseignants</a>
                        @break

                    @case('teacher')
                        <a href="{{ route('teacher.dashboard') }}" class="block px-4 py-4 rounded-md hover:bg-[#63c6ff70] transition">📚 Dashboard Enseignant</a>
                        <a href="{{ route('teacher.classes') }}" class="block px-4 py-4 rounded-md hover:bg-[#63c6ff70] transition">📘 Mes classes</a>
                        @break

                    @case('censeur')
                        <a href="{{ route('censeur.dashboard') }}" class="block px-4 py-4 rounded-md hover:bg-[#63c6ff70] transition">📋 Dashboard Censeur</a>
                        <a href="{{ route('censeur.invitations.index') }}" class="block px-4 py-4 rounded-md hover:bg-[#63c6ff70] transition">📨 Invitations enseignants</a>
                        <a href="{{ route('censeur.subjects.index') }}" class="block px-4 py-4 rounded-md hover:bg-[#63c6ff70] transition">📖 Matières</a>
                        <a href="{{ route('censeur.classes.index') }}" class="block px-4 py-4 rounded-md hover:bg-[#63c6ff70] transition">🗂️ Liste Classes</a>
                        @break

                    @case('secretaire')
                        <a href="{{ route('secretaire.dashboard') }}" class="block px-4 py-4 rounded-md hover:bg-[#63c6ff70] transition">🗃️ Dashboard Secrétaire</a>
                        <a href="{{ route('admin.students.pending') }}" class="block px-4 py-4 rounded-md hover:bg-[#63c6ff70] transition">⏳ Inscriptions en attente</a>
                        <a href="{{ route('admin.students.index') }}" class="block px-4 py-4 rounded-md hover:bg-[#63c6ff70] transition">👥 Liste Élèves</a>
                        <a href="{{ route('admin.classes.index') }}" class="block px-4 py-4 rounded-md hover:bg-[#63c6ff70] transition">🏷️ Gestion des classes</a>
                        @break

                    @case('super_admin')
                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-4 rounded-md hover:bg-[#63c6ff70] transition">🛠️ Tableau de bord</a>
                        <a href="{{ route('admin.academic_years.index') }}" class="block px-4 py-4 rounded-md hover:bg-[#63c6ff70] transition">📆 Années académiques</a>
                        @break

                    @default
                        <span class="block px-4 py-4 text-white/70">Rôle non défini</span>
                @endswitch
            @endauth

            <!-- Connexion / Déconnexion -->
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-4 rounded-md text-red-200 hover:bg-red-600 transition">
                        🔓 Déconnexion
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block px-4 py-4 rounded-md bg-white text-[#0388fc] hover:bg-blue-100 transition">
                    🔐 Connexion
                </a>
            @endauth
        </nav>
    </div>
</aside>

        

        <!-- Main content -->
        <div class="flex-1 ml-0 md:ml-64 flex flex-col">
            <!-- Mobile header -->
            <header class="md:hidden bg-white shadow p-4 flex justify-between items-center">
                <button data-drawer-target="sidebar" data-drawer-toggle="sidebar" aria-controls="sidebar" class="p-2 text-gray-600">☰</button>
                <h1 class="font-semibold">CPEG MARIE-ALAIN</h1>
            </header>

            <!-- Top bar -->
            <div class="bg-gray-100 px-6 py-4 flex justify-between items-center shadow-sm border-b">
                <h2 class="text-lg font-semibold text-gray-800">
                    {{ $pageTitle ?? 'Accu' }}
                </h2>

                <div class="relative">
                    <div id="userMenuToggle" class="flex items-center gap-2 cursor-pointer  rounded-md hover:bg-gray-50 transition">
                        <span class="inline-flex items-center justify-center w-8 h-8 bg-white rounded-full shadow">
                            <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 10a4 4 0 100-8 4 4 0 000 8zm-6 8a6 6 0 1112 0H4z"/>
                            </svg>
                        </span>
                        <span class="text-gray-800 font-medium">
                            @auth
                                {{ auth()->user()->name }}
                            @else
                                Connexion
                            @endauth
                        </span>
                    </div>

                    @auth
                    <ul id="userDropdown" class="absolute right-0 mt-2 w-56  rounded shadow-lg hidden z-50">
                        <li class="px-4 py-2 text-sm text-gray-600 border-b">
                            Connecté en tant que <strong>{{ auth()->user()->name }}</strong>
                        </li>
                        <li>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Modifier le mot de passe
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                    @endauth
                </div>
            </div>

            <!-- Page content -->
            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/flowbite@1.6.5/dist/flowbite.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('userMenuToggle');
            const dropdown = document.getElementById('userDropdown');

            if (toggle && dropdown) {
                toggle.addEventListener('click', () => {
                    dropdown.classList.toggle('hidden');
                });

                document.addEventListener('click', (e) => {
                    if (!toggle.contains(e.target) && !dropdown.contains(e.target)) {
                        dropdown.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</body>
</html>