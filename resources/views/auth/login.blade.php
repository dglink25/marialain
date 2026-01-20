@php use Illuminate\Support\Facades\Route; @endphp
@php
    use Illuminate\Support\Str;
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - CPEG MARIE-ALAIN</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            display: flex;
            width: 100%;
            max-width: 900px;
            height: 550px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 40px;
            position: relative;
        }
        
        .logo-container {
            width: 155px;
            height: 155px;
            background: #ffffff;
            border-radius: 50%;
            justify-content: center;
            align-items: center;
            margin: 0 auto 30px auto;
            padding: 10px;
        }
        
        .logo-container img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 50%;
        }
        
        .left-panel h2 {
            font-size: 28px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .left-panel p {
            text-align: center;
            font-size: 16px;
            line-height: 1.5;
        }
        
        .right-panel {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .welcome-text {
            margin-bottom: 30px;
        }
        
        .welcome-text h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .welcome-text p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            color: #333;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .input-with-icon input:focus {
            border-color: #2575fc;
            outline: none;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 18px;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 18px;
            cursor: pointer;
            background: none;
            border: none;
            outline: none;
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 13px;
            margin-top: 5px;
            display: flex;
            align-items: center;
        }
        
        .error-message::before {
            content: "!";
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 16px;
            height: 16px;
            background-color: #e74c3c;
            color: white;
            border-radius: 50%;
            margin-right: 6px;
            font-size: 12px;
        }
        
        .session-status {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
            font-size: 14px;
        }
        
        .session-status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .session-status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .session-status.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 14px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
        }
        
        .remember-me input {
            margin-right: 8px;
            width: 16px;
            height: 16px;
        }
        
        .remember-me label {
            color: #333;
            font-size: 14px;
        }
        
        .forgot-password {
            color: #2575fc;
            text-decoration: none;
            transition: color 0.2s;
            font-size: 14px;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
            color: #1c64e0;
        }
        
        .login-button {
            background: #2575fc;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
            margin-bottom: 20px;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        
        .login-button:hover:not(:disabled) {
            background: #1c64e0;
        }
        
        .login-button:disabled {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.8;
        }
        
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .resubmit-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
            font-size: 13px;
            text-align: center;
        }
        
        @media (max-width: 900px) {
            .container {
                flex-direction: column;
                width: 100%;
                height: auto;
                max-width: 500px;
            }
            
            .left-panel {
                padding: 30px;
            }
            
            .right-panel {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="logo-container">
                <img src="{{ asset('logo.png') }}" alt="Logo CPEG MARIE-ALAIN">
            </div>
            <h2>CPEG MARIE-ALAIN</h2>
            <p>Connectez-vous à votre espace personnel</p>
        </div>
        
        <div class="right-panel">
            <div class="welcome-text">
                <h1>Connectez-vous à votre compte</h1>
                <p></p>
            </div>
            
            <!-- Session Status -->
            @if(session('status'))
                <div class="session-status success">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Affichage des erreurs de token CSRF -->
            @if(session('csrf_error'))
                <div class="session-status error">
                    {{ session('csrf_error') }}
                </div>
            @endif

            @if($errors->has('csrf'))
                <div class="session-status error">
                    {{ $errors->first('csrf') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                <!-- Erreurs d'authentification générale -->
                @if($errors->has('auth'))
                    <div class="session-status error">
                        {{ $errors->first('auth') }}
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="session-status error">
                        {{ session('error') }}
                    </div>
                @endif
                
                <!-- Champ caché pour détecter les resoumissions -->
                <input type="hidden" name="form_submitted" value="1">
                <input type="hidden" name="form_token" value="{{ Str::random(40) }}">

                <!-- Email Address -->
                <div class="form-group">
                    <label for="email">Adresse Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope input-icon"></i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="votre@email.com">
                    </div>

                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock input-icon"></i>
                        <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="entrez votre mot de passe">
                        <button type="button" id="password-toggle" class="password-toggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                                    
                <div class="options">
                    <div class="remember-me">
                        <input id="remember_me" type="checkbox" name="remember">
                        <label for="remember_me">Se souvenir de moi</label>
                    </div>
                    
                    @if (Route::has('password.request'))
                        <a class="forgot-password" href="{{ route('password.request') }}">
                            Mot de passe oublié?
                        </a>
                    @endif
                </div>

                <button type="submit" class="login-button" id="loginButton">
                    <span id="buttonText">Se connecter</span>
                    <div class="spinner" id="buttonSpinner" style="display: none;"></div>
                </button>
                
                <div id="resubmitWarning" class="resubmit-warning" style="display: none;">
                    <i class="fas fa-exclamation-triangle"></i>
                    Le formulaire est en cours de traitement, veuillez patienter...
                </div>
            </form>
        </div>
    </div>

    <script>
        // Récupération des références aux éléments DOM
        const loginForm = document.getElementById('loginForm');
        const loginButton = document.getElementById('loginButton');
        const buttonText = document.getElementById('buttonText');
        const buttonSpinner = document.getElementById('buttonSpinner');
        const resubmitWarning = document.getElementById('resubmitWarning');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // État du formulaire
        let isSubmitting = false;
        let submittedForms = new Set();

        // Fonctionnalité pour afficher/masquer le mot de passe
        document.getElementById('password-toggle').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Fonction pour désactiver le bouton et afficher le spinner
        function setSubmittingState(submitting) {
            isSubmitting = submitting;
            loginButton.disabled = submitting;
            
            if (submitting) {
                buttonText.textContent = 'Connexion en cours...';
                buttonSpinner.style.display = 'inline-block';
                resubmitWarning.style.display = 'block';
            } else {
                buttonText.textContent = 'Se connecter';
                buttonSpinner.style.display = 'none';
                resubmitWarning.style.display = 'none';
            }
        }

        // Gérer la soumission du formulaire
        loginForm.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }
            
            // Générer un identifiant unique pour cette soumission
            const formToken = document.querySelector('input[name="form_token"]').value;
            
            // Vérifier si ce formulaire a déjà été soumis
            if (submittedForms.has(formToken)) {
                e.preventDefault();
                alert('Ce formulaire a déjà été soumis. Veuillez patienter.');
                return false;
            }
            
            // Marquer le formulaire comme en cours de soumission
            submittedForms.add(formToken);
            setSubmittingState(true);
            
            // Rafraîchir le token CSRF avant soumission
            refreshCsrfToken();
            
            // Le formulaire sera soumis normalement
            return true;
        });

        // Rafraîchir le token CSRF
        function refreshCsrfToken() {
            // Vous pouvez implémenter une logique pour rafraîchir le token CSRF si nécessaire
            // Par exemple, via une requête AJAX
        }

        // Gérer les événements de navigation
        window.addEventListener('pageshow', function(event) {
            // Si l'utilisateur revient à la page via le cache navigateur
            if (event.persisted) {
                resetFormState();
            }
        });

        // Réinitialiser l'état du formulaire
        function resetFormState() {
            setSubmittingState(false);
            submittedForms.clear();
            
            // Rafraîchir le token CSRF stocké
            const newCsrfToken = '{{ csrf_token() }}';
            document.querySelector('input[name="_token"]').value = newCsrfToken;
            document.querySelector('meta[name="csrf-token"]').setAttribute('content', newCsrfToken);
            
            // Générer un nouveau token de formulaire
            document.querySelector('input[name="form_token"]').value = generateRandomToken();
        }

        // Générer un token aléatoire
        function generateRandomToken() {
            return Math.random().toString(36).substring(2) + Date.now().toString(36);
        }

        // Gérer le bouton d'actualisation de la page
        window.addEventListener('beforeunload', function(e) {
            if (isSubmitting) {
                // Optionnel: Avertir l'utilisateur qu'il quitte pendant une soumission
                // e.preventDefault();
                // e.returnValue = 'Une connexion est en cours. Êtes-vous sûr de vouloir quitter ?';
            }
        });

        // Réinitialiser l'état si l'utilisulaire revient en arrière
        window.addEventListener('pagehide', function() {
            if (isSubmitting) {
                // Réinitialiser l'état pour la prochaine visite
                localStorage.setItem('wasSubmitting', 'true');
            }
        });

        // Vérifier l'état au chargement
        window.addEventListener('load', function() {
            if (localStorage.getItem('wasSubmitting') === 'true') {
                resetFormState();
                localStorage.removeItem('wasSubmitting');
            }
            
            // Vérifier s'il y a des erreurs 419 dans l'URL
            if (window.location.search.includes('error=419')) {
                showCsrfError();
            }
        });

        // Afficher un message d'erreur CSRF
        function showCsrfError() {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'session-status error';
            errorDiv.innerHTML = `
                <strong>Session expirée</strong><br>
                Votre session a expiré en raison d'une inactivité prolongée.
                Veuillez rafraîchir la page et vous reconnecter.
            `;
            
            const welcomeText = document.querySelector('.welcome-text');
            welcomeText.parentNode.insertBefore(errorDiv, welcomeText.nextSibling);
        }
    </script>
</body>
</html>