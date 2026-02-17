{{-- resources/views/layouts/parent.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Espace Parent - CPEG MARIE-ALAIN')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Custom Parent CSS -->
    <style>
        :root {
            --primary-orange: #ff6b35;
            --secondary-orange: #ff8c5a;
            --dark-bg: #1e3c72;
            --light-bg: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Navbar personnalisée */
        .parent-navbar {
            background: white;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            animation: slideDown 0.5s ease;
        }
        
        @keyframes slideDown {
            from {
                transform: translateY(-100%);
            }
            to {
                transform: translateY(0);
            }
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--dark-bg) !important;
            transition: all 0.3s ease;
        }
        
        .navbar-brand:hover {
            transform: translateY(-2px);
        }
        
        .nav-link {
            color: var(--dark-bg) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            margin: 0 0.2rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-link:hover,
        .nav-link.active {
            color: var(--primary-orange) !important;
            background: rgba(255, 107, 53, 0.1);
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%) scaleX(0);
            width: 30px;
            height: 3px;
            background: linear-gradient(45deg, var(--primary-orange), var(--secondary-orange));
            border-radius: 10px;
            transition: transform 0.3s ease;
        }
        
        .nav-link:hover::after,
        .nav-link.active::after {
            transform: translateX(-50%) scaleX(1);
        }
        
        /* Dropdown menu */
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-radius: 15px;
            padding: 0.5rem;
            animation: fadeInUp 0.3s ease;
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
        
        .dropdown-item {
            border-radius: 10px;
            padding: 0.7rem 1rem;
            transition: all 0.3s ease;
        }
        
        .dropdown-item:hover {
            background: linear-gradient(45deg, var(--primary-orange), var(--secondary-orange));
            color: white !important;
            transform: translateX(5px);
        }
        
        .dropdown-item i {
            width: 20px;
            margin-right: 10px;
        }
        
        /* Profile avatar */
        .profile-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, var(--primary-orange), var(--secondary-orange));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .profile-avatar:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
        }
        
        /* Main content */
        .main-content {
            flex: 1;
            padding: 2rem 0;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        /* Page header */
        .page-header {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideInLeft 0.5s ease;
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .page-title {
            margin: 0;
            font-weight: 700;
            color: var(--dark-bg);
            position: relative;
            padding-left: 15px;
        }
        
        .page-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 5px;
            height: 30px;
            background: linear-gradient(45deg, var(--primary-orange), var(--secondary-orange));
            border-radius: 10px;
        }
        
        /* Breadcrumb */
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
        }
        
        .breadcrumb-item a {
            color: var(--dark-bg);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .breadcrumb-item a:hover {
            color: var(--primary-orange);
        }
        
        .breadcrumb-item.active {
            color: var(--primary-orange);
        }
        
        /* Footer */
        .parent-footer {
            background: linear-gradient(135deg, var(--dark-bg) 0%, #2a5298 100%);
            color: white;
            padding: 3rem 0 1rem;
            margin-top: auto;
            position: relative;
            overflow: hidden;
        }
        
        .parent-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-orange), var(--secondary-orange), var(--primary-orange));
            animation: gradientMove 3s ease infinite;
            background-size: 200% 100%;
        }
        
        @keyframes gradientMove {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        .footer-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background: var(--primary-orange);
        }
        
        .footer-link {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            margin-bottom: 0.5rem;
        }
        
        .footer-link:hover {
            color: var(--primary-orange);
            transform: translateX(5px);
        }
        
        .social-icon {
            width: 35px;
            height: 35px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .social-icon:hover {
            background: var(--primary-orange);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
        }
        
        .copyright {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 1.5rem;
            margin-top: 2rem;
            text-align: center;
            font-size: 0.9rem;
            color: rgba(255,255,255,0.7);
        }
        
        /* Loader */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
        
        .loader {
            width: 50px;
            height: 50px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary-orange);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Notifications */
        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: var(--primary-orange);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: translate(25%, -25%);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 107, 53, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(255, 107, 53, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 107, 53, 0);
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .navbar-nav {
                padding: 1rem 0;
            }
            
            .nav-link {
                padding: 0.8rem 1rem !important;
            }
            
            .page-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .page-title {
                padding-left: 0;
            }
            
            .page-title::before {
                display: none;
            }
            
            .footer-title::after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .footer-title {
                text-align: center;
            }
            
            .social-icons {
                text-align: center;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader" id="pageLoader">
        <div class="loader"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg parent-navbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('parent.dashboard') }}">
                <img src="{{ asset('ursule/img/logo.png') }}" alt="Logo" style="height: 40px;" class="me-2">
                <span>CPEG MARIE-ALAIN</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#parentNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="parentNavbar">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}" 
                           href="{{ route('parent.dashboard') }}">
                            <i class="fas fa-home me-1"></i> Tableau de bord
                        </a>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-child me-1"></i> Mes enfants
                        </a>
                        <ul class="dropdown-menu">
                            @foreach(auth('parent')->user()->students as $student)
                                <li>
                                    <a class="dropdown-item" href="">
                                        <i class="fas fa-user-graduate"></i>
                                        {{ $student->full_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#notificationsModal">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </a>
                    </li>
                    
                    <li class="nav-item dropdown ms-2">
                        <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown">
                            <div class="profile-avatar">
                                {{ substr(auth('parent')->user()->full_name, 0, 1) }}
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="">
                                    <i class="fas fa-user-circle"></i> Mon profil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="">
                                    <i class="fas fa-cog"></i> Paramètres
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Formulaire de déconnexion caché -->
    <form id="logout-form" action="{{ route('parent.logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header animate__animated animate__fadeIn">
                <h1 class="page-title">@yield('page-title', 'Tableau de bord')</h1>
                
                @hasSection('breadcrumb')
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            @yield('breadcrumb')
                        </ol>
                    </nav>
                @endif
            </div>
            
            <!-- Content -->
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="parent-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">CPEG MARIE-ALAIN</h5>
                    <p class="text-white-50">
                        Un établissement dédié à l'excellence académique et à l'épanouissement de chaque enfant.
                    </p>
                    <div class="social-icons">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                
                <div class="col-md-2 mb-4">
                    <h5 class="footer-title">Liens rapides</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('parent.dashboard') }}" class="footer-link">Tableau de bord</a></li>
                        <li><a href="#" class="footer-link">Notes</a></li>
                        <li><a href="#" class="footer-link">Emploi du temps</a></li>
                        <li><a href="#" class="footer-link">Paiements</a></li>
                    </ul>
                </div>
                
                <div class="col-md-3 mb-4">
                    <h5 class="footer-title">Contact</h5>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-orange"></i> Quartier Aitchédji, Abomey-Calavi</li>
                        <li class="mb-2"><i class="fas fa-phone me-2 text-orange"></i> +229 01 97 21 20 45</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2 text-orange"></i> cpegmariealain@gmail.com</li>
                    </ul>
                </div>
                
                <div class="col-md-3 mb-4">
                    <h5 class="footer-title">Horaires</h5>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2">Lundi - Vendredi: 8h - 17h</li>
                        <li class="mb-2">Samedi: 9h - 12h</li>
                        <li class="mb-2">Dimanche: Fermé</li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                &copy; {{ date('Y') }} CPEG MARIE-ALAIN. Tous droits réservés. | Espace Parent
            </div>
        </div>
    </footer>

    <!-- Notifications Modal -->
    <div class="modal fade" id="notificationsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-orange text-white">
                    <h5 class="modal-title"><i class="fas fa-bell me-2"></i>Notifications</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex align-items-center">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="fas fa-star text-info"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Nouvelle note disponible</h6>
                                <p class="mb-0 small text-muted">Il y a 2 heures</p>
                            </div>
                        </div>
                        <div class="list-group-item d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="fas fa-credit-card text-success"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Paiement enregistré</h6>
                                <p class="mb-0 small text-muted">Il y a 1 jour</p>
                            </div>
                        </div>
                        <div class="list-group-item d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="fas fa-calendar text-warning"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Réunion parents-professeurs</h6>
                                <p class="mb-0 small text-muted">Dans 3 jours</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary w-100">Voir toutes les notifications</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Cache le loader quand la page est chargée
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('pageLoader').style.opacity = '0';
                setTimeout(function() {
                    document.getElementById('pageLoader').style.visibility = 'hidden';
                }, 500);
            }, 500);
        });
        
        // Gestion des messages flash
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Succès!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        @endif
        
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Erreur!',
                text: '{{ session('error') }}',
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        @endif
        
        // Animation des liens actifs
        document.querySelectorAll('.nav-link').forEach(link => {
            if (link.href === window.location.href) {
                link.classList.add('active');
            }
        });
        
        // Confirmation avant déconnexion
        document.querySelectorAll('a[href*="logout"]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Déconnexion',
                    text: 'Voulez-vous vraiment vous déconnecter?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#ff6b35',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Oui, déconnecter',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('logout-form').submit();
                    }
                });
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>