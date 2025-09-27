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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/flowbite@1.6.5/dist/flowbite.min.css" rel="stylesheet" />
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
         body { font-family: 'Inter', sans-serif; }
        .scrollbar-hide {
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE 10+ */
        }

    </style>
</head>
<body class="antialiased bg-gray-50 text-gray-800">

    <!-- Mobile header -->
<header class="fixed top-0 left-0 w-full z-50 md:hidden bg-white  p-4 flex justify-between items-center h-16">        
    <h1 class="font-semibold pl-2">CPEG MARIE-ALAIN</h1>
    <button id="sidebarToggle" class="p-2 text-gray-600"><i class="fas fa-bars w-4"></i></button>
    
</header>
<div class="flex min-h-screen">
        <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 h-screen w-64 bg-[#263f91] text-white shadow-xl z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
        
        <!-- Logo + Titre -->
        <div class=" p-3 flex flex-col items-center gap-2 border-b border-blue-300 bg-[#263f91] text-white">
            <div class="bg-white rounded-full p-2 shadow">
                <img src="{{ asset('logo.png') }}" class="h-12 w-12 object-contain rounded-full" alt="Logo" />
            </div>
            <span class="font-bold text-lg text-white text-center">CPEG MARIE-ALAIN</span>
        </div>

        <!-- Navigation -->
        <div class="overflow-y-auto h-[calc(100vh-112px)]  space-y-1 scrollbar-hide">
            <nav class="p-4 space-y-1 text-base font-medium">
                <a href="{{ route('home') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('home') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                    <i class="fa fa-home"></i> 
                    <span class="ml-2">Accueil</span>
                </a>
                <a href="{{ route('students.create') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('students.create') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                    <i class="fa fa-user-plus "></i> 
                    <span class="ml-2">Inscription </span>
                </a>
                            
                @auth

                @php
                    $user = auth()->user();
                    $firstClasse = $user->classe()->with('entity')->first();
                    $entityName = $firstClasse->entity->name ?? null;

                    //dd($firstClasse, $entityName);
                @endphp
                
                @switch($entityName)
                    @case('Primaire')

                        <a href="{{ route('schedules.index') }}" 
                        class="block px-4 py-4 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('schedules.index') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                            <i class="fa fa-layer-group w-5 text-gray-500"></i>
                            <span class="ml-3">Emploi du temps</span>
                        </a>


                        <a href="{{ route('teacher.subjects.primaire') }}" class="block px-4 py-4 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('teacher.subjects.primaire') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                            <i class="fa fa-layer-group w-5 text-gray-500"></i>
                            <span class="ml-3">Gestion matières</span>

                        <a href="{{ route('teacher.dashboard') }}" class="block px-4 py-4 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('teacher.dashboard') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                            <i class="fa fa-book-open w-5 text-gray-500"></i> 
                            <span class="ml-3">Dashboard </span>
                        </a>
                        <a href="{{ route('teacher.classes.primaire') }}" class="block px-4 py-4 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('teacher.classes.primaire') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                            <i class="fa fa-layer-group w-5 text-gray-500"></i>
                            <span class="ml-3">Ma classes</span>
                        </a>

                        <a href="{{ route('teacher.subjects.primaire') }}" class="block px-4 py-4 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('teacher.subjects.primaire') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                            <i class="fa fa-layer-group w-5 text-gray-500"></i>
                            <span class="ml-3">Matières</span>
                        </a>
                        @break
            

                    @case('Secondaire')

                        <a href="{{ route('teacher.dashboard') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('teacher.dashboard') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                            <i class="fas fa-tachometer-alt"></i> 
                            <span class="ml-2">Dashboard </span>
                        </a>
                        <a href="{{ route('teacher.classes') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('teacher.classes') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                            <i class="fa fa-layer-group w-5 text-gray-500"></i>
                            <span class="ml-2">Classes</span>
                        </a>
                            
                        @break
                    @default

                @endswitch
                    <a href="{{ route('archives.index') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('archives.index') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                        <i class="fas fa-archive"></i>
                        <span class="ml-2">Mes Archives</span>
                    </a>
                    
                    @if(!isset($entityName))
                    @switch(optional(auth()->user()->role)->name)
                        @case('directeur_primaire')
                            <a href="{{ route('directeur.dashboard') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('directeur.dashboard') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                                <i class="fas fa-tachometer-alt"></i>
                                <span class="ml-3">Dashboard </span>
                            </a>
                            <a href="{{ route('primaire.classe.classes') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('primaire.classe.classes') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                                <i class="fa fa-school w-5 text-gray-500"></i> 
                                <span class="ml-3">Classes</span>
                            </a>
                            <a href="{{ route('primaire.enseignants.enseignants') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('primaire.enseignants.enseignants') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                                <i class="fa fa-chalkboard-teacher w-5 text-gray-500"></i> 
                                <span class="ml-3">Enseignants</span>
                            </a>
                            <a href="{{ route('primaire.ecoliers.liste') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('primaire.ecoliers.liste') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                                <i class="fa fa-users w-5 text-gray-500"></i> 
                                <span class="ml-3">Ecoliers</span>
                            </a>
                            @break

                        @case('teacher')
                            <a href="{{ route('teacher.dashboard') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('teacher.dashboard') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                                <i class="fas fa-tachometer-alt"></i> 
                                <span class="ml-2">Dashboard </span>
                            </a>
                            <a href="{{ route('teacher.classes') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('teacher.classes') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                                <i class="fa fa-layer-group w-5 text-gray-500"></i>
                                <span class="ml-2">Classes</span>
                            </a>
                            @break

                        @case('censeur')
                            <a href="{{ route('censeur.dashboard') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('censeur.dashboard') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                                <i class="fas fa-tachometer-alt"></i> 
                                <span class="ml-2">Dashboard </span>
                            </a>
                            <a href="{{ route('censeur.invitations.index') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('censeur.invitations.index') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <span class="ml-2">Invitations </span>
                            </a>
                            <a href="{{ route('censeur.subjects.index') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('censeur.subjects.index') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                                <i class="fas fa-book-open"></i>
                                <span class="ml-2">Matières</span>
                            </a>
                            <a href="{{ route('censeur.classes.index') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('censeur.classes.index') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                                <i class="fas fa-list"></i>
                                <span class="ml-2"> Classes</span>
                            </a>
                            @break
                        @case('surveillant')
                            <a href="{{ route('surveillant.dashboard') }}"class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('surveillant.dashboard') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                                <i class="fas fa-tachometer-alt"></i> 
                                <span class="ml-2">Dashboard </span>
                            </a>
                            @break

                        @case('secretaire')
                            <a href="{{ route('secretaire.dashboard') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('secretaire.dashboard') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}"> 
                                <i class="fas fa-tachometer-alt"></i> 
                                <span class="ml-3">Dashboard </span>
                            </a>
                            
                            <div x-data="{ open: false }" class="space-y-1">
                                <!-- Lien principal -->
                                <button @click="open = !open" class="w-full flex items-center px-3 py-3 rounded-md hover:bg-[#63c6ff70] transition text-left">
                                    <i class="fas fa-users-cog"></i>
                                    <span class="ml-2 font-semibold">Elèves</span>
                                    <i class="fas fa-chevron-down ml-auto transition-transform duration-300" :class="{ 'rotate-180': open }"></i>
                                </button>

                                <!-- Sous-liens -->
                                <div x-show="open" x-transition class="pl-6 space-y-2">
                                    <a href="{{ route('admin.students.pending') }}" class="block px-3 py-2 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('admin.students.pending') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                                        <i class="far fa-circle"></i>
                                        <span class="ml-2">En attente</span>
                                    </a>
                                    <a href="{{ route('admin.students.index') }}" class="block px-3 py-2 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('admin.students.index') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                                        <i class="far fa-circle"></i>
                                        <span class="ml-2">Inscrits</span>
                                    </a>
                                </div>
                            </div>
                            <a href="{{ route('admin.classes.index') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('admin.classes.index') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                                 <i class="fas fa-school"></i>
                                <span class="ml-2">Classes</span>
                            </a>
                            @break

                        @case('super_admin')
                            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('admin.dashboard') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}"> 
                                <i class="fas fa-tachometer-alt"></i>
                                <span class="ml-2">Tableau de bord</span>
                            </a>
                            <a href="{{ route('admin.academic_years.index') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('admin.academic_years.index') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                                <i class="fas fa-calendar-alt"></i>
                                <span class="ml-2">Années académiques</span>
                            </a>
                            @break

                        @default
                            <span class="block px-3 py-3 text-white/70">Rôle non défini</span>
                            @break
                    @endswitch
                    @endif
                        <a href="{{ route('profile.edit') }}" class="block px-3 py-3 rounded-md hover:bg-[#ffffff36] transition {{ request()->routeIs('profile.edit') ? 'bg-[#ffffff36] font-bold' : 'hover:bg-[#63c6ff70]' }}">
                            <i class="fa fa-user "></i>
                            <span class="ml-3">Mon Profil</span>
                        </a>

                @endauth

                <!-- Connexion / Déconnexion -->
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-3 rounded-md text-red-200 hover:bg-red-600 transition">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="ml-3">Déconnexion</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block px-3 py-3 rounded-md bg-white text-[#0388fc] hover:bg-blue-100 transition">
                        <i class="fas fa-sign-in-alt"></i>
                        <span class="ml-3">Connexion</span>
                    </a>
                @endauth
            </nav>
        </div>
    </aside>

        

        <!-- Main content -->
        <div class="flex-1 ml-0 md:ml-64 flex flex-col ">
        


            <!-- Top bar -->

            <div class="fixed left-0 w-full md:left-64 md:w-[calc(100%-16rem)] z-40 bg-gray-100 px-6 py-3 flex justify-between items-center shadow-sm border-b top-16 md:top-0">               
                <h2 class="text-lg font-semibold text-gray-800">
                 
                    {{ $pageTitle ??  'Accueil' }}
                </h2>
                <!-- Année centrée -->
                <div class="absolute left-1/2 transform -translate-x-1/2 text-gray-600 text-sm font-medium">
                    {{ (isset($activeYear) ? $activeYear->name : 'Na') }}
                </div>
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
                                <a href="{{ route('login') }}">
                                    Connexion
                                </a>
                            @endauth
                        </span>
                        
                    </div>
                    @auth
                    <ul id="userDropdown" class="absolute right-0 mt-2 w-56 bg-white  rounded shadow-lg hidden z-50">
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
            <main class="flex-1 p-6 mt-20 md:mt-10 pt-20 ">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            {{-- Messages d'erreur --}}
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            {{ $error }}
                        @endforeach
                    </ul>
                </div>
            @endif

                @yield('content')
            </main>
            <footer class="bg-white py-6 border-t">
                <div class="text-center text-sm text-gray-600">
                    &copy; 2025 CPEG MARIE-ALAIN — Tous droits réservés.
                </div>
            </footer>
        </div>
</div>

    
<script>
document.addEventListener('DOMContentLoaded', function () {
  const toggleBtn = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('-translate-x-full');
    });
  }
});
document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.getElementById('userMenuToggle');
  const dropdown = document.getElementById('userDropdown');

  if (toggle && dropdown) {
    toggle.addEventListener('click', () => {
      dropdown.classList.toggle('hidden');
    });

    // Fermer le menu si on clique ailleurs
    document.addEventListener('click', (e) => {
      if (!toggle.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.add('hidden');
      }
    });
  }
});
</script>

    <!-- Scripts -->
    <script src="https://unpkg.com/flowbite@1.6.5/dist/flowbite.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</body>
</html>








