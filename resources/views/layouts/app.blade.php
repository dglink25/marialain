<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <title>CPEG MARIE-ALAIN</title>
        <!-- Tailwind + Flowbite -->
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://unpkg.com/flowbite@1.6.5/dist/flowbite.min.css" rel="stylesheet" />
    </head>
    <body class="antialiased bg-gray-50 text-gray-800">
        <div class="flex min-h-screen">
            <!-- Sidebar -->
            <aside id="sidebar" class="w-64 bg-white shadow-md hidden md:block">
            <div class="p-4 flex items-center gap-3 border-b">
                <img src="{{ asset('logo.png') }}" class="h-12" alt="Logo" />
                <span class="font-bold">CPEG MARIE-ALAIN</span>
            </div>
            <nav class="p-4 space-y-2">
                <a href="{{ route('home') }}" class="block px-3 py-2 rounded hover:bg-blue-50">Accueil</a>
                <a href="{{ route('students.create') }}" class="block px-3 py-2 rounded hover:bg-blue-50">Inscription en ligne</a>

                @if(auth()->check())
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-100">Mon Profil</a>


                    @switch(optional(auth()->user()->role)->name)
                        @case('directeur_primaire')
                            <a href="{{ route('directeur.dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-200">Dashboard Directeur</a>
                            @break

                        @case('teacher')
                            <a href="{{ route('teacher.dashboard') }}" class="block px-4 py-2 hover:bg-gray-200">Dashboard Enseignant</a>
                            <a href="{{ route('teacher.classes') }}" class="block px-4 py-2 hover:bg-gray-200">Mes classes</a>
                            @break

                        @case('censeur')
                            <a href="{{ route('censeur.dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-200">Dashboard Censeur</a>
                            <a href="{{ route('censeur.invitations.index') }}" class="block px-4 py-2 hover:bg-gray-200">Invitations enseignants</a>
                            <a href="{{ route('censeur.subjects.index') }}" class="block px-4 py-2 hover:bg-gray-200">Matières</a>
                            <a href="{{ route('censeur.classes.index') }}" class="block px-4 py-2 hover:bg-gray-200" >Liste Classes</a>
                            @break

                        @case('surveillant')
                            <a href="{{ route('surveillant.dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-200">Dashboard Surveillant</a>
                            @break

                        @case('secretaire')
                            <a href="{{ route('secretaire.dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-200">Dashboard Secrétaire</a>
                            <a href="{{ route('admin.students.pending') }}" class="block px-4 py-2 rounded hover:bg-gray-200">Inscription en attente</a>
                            
                            <a href="{{ route('admin.students.index') }}" class="block px-4 py-2 rounded hover:bg-gray-200">Liste Elèves</a>
                            <a href="{{ route('admin.classes.index') }}" class="block py-2 px-4 hover:bg-gray-200">Gestion de classes</a>
                            @break

                        @case('super_admin')
                            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-blue-50">Tableau de bord</a>
                            <a href="{{ route('admin.academic_years.index') }}" class="block py-2 px-4 hover:bg-gray-200">Années académiques</a>
                            <a href="{{ route('admin.classes.index') }}" class="block py-2 px-4 hover:bg-gray-200">Classes</a>
                            @break
                        @default
                            <span class="px-4 py-2 text-gray-500">Rôle non défini</span>
                        
                    @endswitch
                @endif
                
                @auth
                    
                    <form method="POST" action="{{ route('logout') }}">@csrf <button type="submit" class="w-full text-left px-3 py-2 rounded hover:bg-red-50">Déconnexion</button></form>
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


            <main class="flex-1 p-6">
                @yield('content')
            </main>
            </div>
        </div>
        <script src="https://unpkg.com/flowbite@1.6.5/dist/flowbite.js"></script>
    </body>
</html>