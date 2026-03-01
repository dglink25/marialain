{{-- resources/views/layouts/parent.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Espace Parent - CPEG MARIE-ALAIN')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        :root {
            --primary: #2E7D32;
            --primary-light: #4CAF50;
            --primary-dark: #1B5E20;
            --secondary: #FF8F00;
            --secondary-light: #FFB300;
            --danger: #C62828;
            --warning: #FFB300;
            --success: #2E7D32;
            --text-dark: #263238;
            --text-light: #546E7A;
            --bg-light: #F5F7FA;
            --white: #FFFFFF;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.15);
            --shadow-lg: 0 8px 30px rgba(0,0,0,0.2);
            --radius-sm: 12px;
            --radius-md: 20px;
            --radius-lg: 30px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Roboto, system-ui, -apple-system, sans-serif;
            background-color: var(--bg-light);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.6;
            font-size: 16px;
        }

        /* Pour les malvoyants - texte plus grand */
        @media (prefers-contrast: high) {
            body {
                font-size: 18px;
            }
        }

        /* Skip link pour accessibilité */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: var(--primary);
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 0 0 var(--radius-sm) 0;
            z-index: 10000;
            transition: top 0.3s;
        }

        .skip-link:focus {
            top: 0;
            outline: 3px solid var(--secondary);
        }

        /* Navigation */
        .parent-navbar {
            background: var(--white);
            box-shadow: var(--shadow-sm);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: var(--text-dark) !important;
            font-size: 1.25rem;
        }

        .navbar-brand img {
            height: 45px;
            width: auto;
            border-radius: 8px;
        }

        .nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            padding: 0.75rem 1.25rem !important;
            border-radius: 50px;
            transition: var(--transition);
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
            position: relative;
        }

        .nav-link i {
            font-size: 1.2rem;
            color: var(--primary);
        }

        .nav-link:hover,
        .nav-link:focus {
            background: rgba(46, 125, 50, 0.1);
            color: var(--primary) !important;
            outline: 2px solid transparent;
        }

        .nav-link:focus-visible {
            outline: 3px solid var(--secondary);
            outline-offset: 2px;
        }

        .nav-link.active {
            background: var(--primary);
            color: white !important;
        }

        .nav-link.active i {
            color: white;
        }

        /* Menu déroulant accessible */
        .dropdown-menu {
            border: none;
            box-shadow: var(--shadow-md);
            border-radius: var(--radius-md);
            padding: 0.75rem;
            min-width: 240px;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .dropdown-item {
            border-radius: var(--radius-sm);
            padding: 0.875rem 1.25rem;
            transition: var(--transition);
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .dropdown-item i {
            width: 20px;
            color: var(--primary);
            font-size: 1.1rem;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background: rgba(46, 125, 50, 0.1);
            color: var(--primary);
            transform: translateX(5px);
        }

        .dropdown-item:focus-visible {
            outline: 3px solid var(--secondary);
            outline-offset: -2px;
        }

        /* Avatar profil */
        .profile-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.3rem;
            text-transform: uppercase;
            border: 3px solid var(--white);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .profile-avatar:hover,
        .profile-avatar:focus {
            transform: scale(1.1);
            box-shadow: 0 5px 20px rgba(46, 125, 50, 0.3);
        }

        /* Badge notifications */
        .notification-wrapper {
            position: relative;
            display: inline-block;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -8px;
            background: var(--danger);
            color: white;
            border-radius: 50px;
            min-width: 22px;
            height: 22px;
            font-size: 0.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 6px;
            border: 2px solid var(--white);
            box-shadow: var(--shadow-sm);
        }

        /* Contenu principal */
        .main-content {
            flex: 1;
            padding: 2rem 0;
        }

        /* En-tête de page amélioré */
        .page-header {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
            border-left: 6px solid var(--primary);
        }

        .page-title {
            margin: 0;
            font-weight: 700;
            color: var(--text-dark);
            font-size: 2rem;
            line-height: 1.2;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 1.5rem;
            }
        }

        /* Boutons accessibles */
        .btn {
            padding: 0.875rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-width: 44px;
            min-height: 44px;
        }

        .btn:focus-visible {
            outline: 3px solid var(--secondary);
            outline-offset: 2px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-outline-primary {
            background: transparent;
            color: var(--primary);
            border-color: var(--primary);
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:focus {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        .btn-lg {
            padding: 1rem 2.5rem;
            font-size: 1.125rem;
        }

        /* Cartes améliorées */
        .card {
            border: none;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            background: var(--white);
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .card-body {
            padding: 2rem;
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem;
            }
        }

        /* Badges colorés */
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .badge-success {
            background: rgba(46, 125, 50, 0.15);
            color: var(--primary-dark);
        }

        .badge-warning {
            background: rgba(255, 143, 0, 0.15);
            color: var(--secondary);
        }

        .badge-danger {
            background: rgba(198, 40, 40, 0.15);
            color: var(--danger);
        }

        /* Footer */
        .parent-footer {
            background: var(--text-dark);
            color: white;
            padding: 4rem 0 2rem;
            margin-top: 3rem;
            position: relative;
        }

        .footer-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: white;
            position: relative;
            padding-bottom: 0.75rem;
        }

        .footer-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--secondary);
            border-radius: 3px;
        }

        .footer-link {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.5rem 0;
            font-size: 1rem;
        }

        .footer-link:hover,
        .footer-link:focus {
            color: var(--secondary);
            transform: translateX(5px);
            outline: none;
        }

        .footer-link:focus-visible {
            outline: 2px solid var(--secondary);
            outline-offset: 4px;
            border-radius: 4px;
        }

        .social-icon {
            width: 44px;
            height: 44px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 0.5rem;
            transition: var(--transition);
            font-size: 1.2rem;
        }

        .social-icon:hover,
        .social-icon:focus {
            background: var(--secondary);
            transform: translateY(-3px);
            color: var(--text-dark);
        }

        .copyright {
            text-align: center;
            padding-top: 2rem;
            margin-top: 3rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.7);
            font-size: 0.95rem;
        }

        /* Loader accessible */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--white);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.3s;
        }

        .loader {
            width: 60px;
            height: 60px;
            border: 4px solid var(--bg-light);
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loader-text {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Messages flash */
        .flash-message {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            animation: slideInRight 0.3s ease;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Accessibilité */
        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
        }

        /* Focus visible */
        *:focus-visible {
            outline: 3px solid var(--secondary);
            outline-offset: 2px;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .navbar-nav {
                padding: 1rem 0;
            }
            
            .nav-link {
                padding: 1rem !important;
                border-radius: var(--radius-sm);
            }
            
            .nav-link i {
                width: 24px;
            }
            
            .profile-avatar {
                margin: 0.5rem 0;
            }
        }

        @media (max-width: 576px) {
            .page-header {
                flex-direction: column;
                text-align: center;
                padding: 1.5rem;
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
            
            .btn {
                width: 100%;
            }
        }

        /* Impression */
        @media print {
            .parent-navbar,
            .parent-footer,
            .page-header {
                display: none;
            }
        }

        .bg-purple {
            background-color: #6f42c1 !important;
        }

        .bg-indigo {
            background-color: #6610f2 !important;
        }

        .progress {
            background-color: #e9ecef;
            border-radius: 0.25rem;
            overflow: hidden;
        }

        .progress-bar {
            transition: width 0.3s ease;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Lien d'évitement pour accessibilité -->
    <a href="#main-content" class="skip-link">Aller au contenu principal</a>

    <!-- Page Loader -->
    <div class="page-loader" id="pageLoader" role="status" aria-label="Chargement">
        <div class="loader"></div>
        <span class="loader-text">Chargement de la page en cours...</span>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg parent-navbar" aria-label="Navigation principale">
        <div class="container">
            <a class="navbar-brand" href="{{ route('parent.dashboard') }}" aria-label="Accueil CPEG MARIE-ALAIN">
                <img src="{{ asset('ursule/img/logo.png') }}" alt="" loading="lazy">
                <span>CPEG MARIE-ALAIN</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#parentNavbar" 
                    aria-controls="parentNavbar" aria-expanded="false" aria-label="Menu navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="parentNavbar">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}" 
                           href="{{ route('parent.dashboard') }}"
                           aria-current="{{ request()->routeIs('parent.dashboard') ? 'page' : false }}">
                            <i class="fas fa-home" aria-hidden="true"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false"
                           aria-haspopup="true">
                            <i class="fas fa-child" aria-hidden="true"></i>
                            <span>Mes enfants</span>
                        </a>
                        <ul class="dropdown-menu" aria-label="Sélectionner un enfant">
                            @foreach(auth('parent')->user()->students as $student)
                                <li>
                                    <a class="dropdown-item" href="">
                                        <i class="fas fa-user-graduate" aria-hidden="true"></i>
                                        {{ $student->full_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="#" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false"
                           aria-label="Menu utilisateur">
                            <div class="profile-avatar" aria-hidden="true">
                                {{ substr(auth('parent')->user()->full_name, 0, 1) }}
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button class="dropdown-item text-danger" 
                                        onclick="confirmLogout(event)"
                                        aria-label="Se déconnecter">
                                    <i class="fas fa-sign-out-alt"></i>
                                    Déconnexion
                                </button>
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

    <!-- Messages flash -->
    @if(session('success'))
        <div class="flash-message" role="alert" aria-live="polite">
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="flash-message" role="alert" aria-live="assertive">
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="main-content" id="main-content">
        <div class="container">
            <!-- Page Header -->
            <header class="page-header">
                <h1 class="page-title">@yield('page-title', 'Tableau de bord')</h1>
                
                @hasSection('breadcrumb')
                    <nav aria-label="Fil d'Ariane">
                        <ol class="breadcrumb">
                            @yield('breadcrumb')
                        </ol>
                    </nav>
                @endif
            </header>
            
            <!-- Content -->
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="parent-footer" role="contentinfo">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h2 class="footer-title">CPEG MARIE-ALAIN</h2>
                    <p class="text-white-50">
                        Un établissement dédié à l'excellence académique et à l'épanouissement de chaque enfant.
                    </p>
                    <div class="social-icons">
                        <a href="#" class="social-icon" aria-label="Facebook">
                            <i class="fab fa-facebook-f" aria-hidden="true"></i>
                        </a>
                        <a href="#" class="social-icon" aria-label="Twitter">
                            <i class="fab fa-twitter" aria-hidden="true"></i>
                        </a>
                        <a href="#" class="social-icon" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in" aria-hidden="true"></i>
                        </a>
                        <a href="#" class="social-icon" aria-label="WhatsApp">
                            <i class="fab fa-whatsapp" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <h2 class="footer-title">Liens rapides</h2>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('parent.dashboard') }}" class="footer-link">Tableau de bord</a></li>
                        <li><a href="" class="footer-link">Notes</a></li>
                        <li><a href="" class="footer-link">Emploi du temps</a></li>
                    </ul>
                </div>
                
                <div class="col-md-3">
                    <h2 class="footer-title">Contact</h2>
                    <address class="text-white-50" style="font-style: normal;">
                        <p class="mb-2">
                            <i class="fas fa-map-marker-alt me-2 text-warning" aria-hidden="true"></i>
                            Quartier Aitchédji, Abomey-Calavi
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-phone me-2 text-warning" aria-hidden="true"></i>
                            <a href="tel:+22997212045" class="text-white-50 text-decoration-none">+229 01 97 21 20 45</a>
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-envelope me-2 text-warning" aria-hidden="true"></i>
                            <a href="mailto:cpegmariealain@gmail.com" class="text-white-50 text-decoration-none">cpegmariealain@gmail.com</a>
                        </p>
                    </address>
                </div>
                
                <div class="col-md-3">
                    <h2 class="footer-title">Horaires</h2>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2">Lundi - Vendredi: 8h - 17h</li>
                        <li class="mb-2">Samedi: 9h - 12h</li>
                        <li class="mb-2">Dimanche: Fermé</li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; {{ date('Y') }} CPEG MARIE-ALAIN. Tous droits réservés.</p>
                <p class="small">Espace Parent - Version accessible</p>
            </div>
        </div>
    </footer>

    <!-- Notifications Modal -->
    <div class="modal fade" id="notificationsModal" tabindex="-1" 
         aria-labelledby="notificationsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="notificationsModalLabel">
                        <i class="fas fa-bell me-2" aria-hidden="true"></i>
                        Notifications
                    </h5>
                    <button type="button" class="btn-close btn-close-white" 
                            data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-star text-info fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Nouvelle note disponible</h6>
                                    <p class="mb-0 small text-muted">Il y a 2 heures</p>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-credit-card text-success fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Paiement enregistré</h6>
                                    <p class="mb-0 small text-muted">Il y a 1 jour</p>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-calendar text-warning fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Réunion parents-professeurs</h6>
                                    <p class="mb-0 small text-muted">Dans 3 jours</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-success w-100">
                        Voir toutes les notifications
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Configuration de SweetAlert2 pour les parents
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Cache le loader
        window.addEventListener('load', function() {
            setTimeout(function() {
                const loader = document.getElementById('pageLoader');
                if (loader) {
                    loader.style.opacity = '0';
                    setTimeout(function() {
                        loader.style.display = 'none';
                    }, 300);
                }
            }, 500);
        });

        // Fonction de confirmation de déconnexion améliorée
        window.confirmLogout = function(event) {
            event.preventDefault();
            
            Swal.fire({
                title: 'Confirmation de déconnexion',
                text: 'Êtes-vous sûr de vouloir vous déconnecter ?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2E7D32',
                cancelButtonColor: '#C62828',
                confirmButtonText: 'Oui, me déconnecter',
                cancelButtonText: 'Non, rester connecté',
                reverseButtons: true,
                focusCancel: true,
                allowOutsideClick: false,
                allowEscapeKey: true,
                customClass: {
                    confirmButton: 'btn btn-success btn-lg',
                    cancelButton: 'btn btn-danger btn-lg',
                    popup: 'rounded-4'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Afficher un message de chargement
                    Swal.fire({
                        title: 'Déconnexion en cours...',
                        html: 'Veuillez patienter',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Soumettre le formulaire
                    document.getElementById('logout-form').submit();
                }
            });
        };

        // Gestion des messages flash avec SweetAlert2
        @if(session('success'))
            Toast.fire({
                icon: 'success',
                title: '{{ session('success') }}',
                background: '#2E7D32',
                color: 'white'
            });
        @endif
        
        @if(session('error'))
            Toast.fire({
                icon: 'error',
                title: '{{ session('error') }}',
                background: '#C62828',
                color: 'white'
            });
        @endif

        @if(session('warning'))
            Toast.fire({
                icon: 'warning',
                title: '{{ session('warning') }}',
                background: '#FF8F00',
                color: 'white'
            });
        @endif

        @if(session('info'))
            Toast.fire({
                icon: 'info',
                title: '{{ session('info') }}',
                background: '#0288D1',
                color: 'white'
            });
        @endif

        // Détection des erreurs de formulaire
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    // Vérifier les champs requis
                    const requiredFields = form.querySelectorAll('[required]');
                    let hasError = false;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            hasError = true;
                            field.classList.add('is-invalid');
                            
                            // Message d'erreur pour le champ
                            Toast.fire({
                                icon: 'error',
                                title: 'Veuillez remplir tous les champs obligatoires',
                                timer: 3000
                            });
                        }
                    });
                    
                    if (hasError) {
                        e.preventDefault();
                    }
                });
            });
        });

        // Support pour la navigation au clavier
        document.addEventListener('keydown', function(e) {
            // Touche Echap pour fermer les modales
            if (e.key === 'Escape') {
                const modals = document.querySelectorAll('.modal.show');
                modals.forEach(modal => {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                });
            }
        });

        // Animation douce du scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#') {
                    e.preventDefault();
                    document.querySelector(href).scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Mise à jour du lien actif dans la navigation
        const currentLocation = window.location.href;
        document.querySelectorAll('.nav-link').forEach(link => {
            if (link.href === currentLocation) {
                link.classList.add('active');
                link.setAttribute('aria-current', 'page');
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>