<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>CPEG MARIE-ALAIN</title>

    <!-- Tailwind + Flowbite -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/flowbite@1.6.5/dist/flowbite.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="antialiased bg-gray-50 text-gray-800">
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside id="sidebar" class="w-64 bg-white border-r border-gray-200 hidden md:block shadow-sm">
        <div class="p-6 flex items-center gap-3 border-b">
            <img src="{{ asset('logo.png') }}" class="h-12" alt="Logo" />
            <span class="font-bold text-lg text-gray-800">CPEG MARIE-ALAIN</span>
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

        <main class="flex-1 p-6 container mx-auto space-y-6">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded shadow-sm flex items-center">
                    <i class="fa fa-check-circle mr-2 text-green-600"></i> {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="https://unpkg.com/flowbite@1.6.5/dist/flowbite.js"></script>
</body>
</html>
