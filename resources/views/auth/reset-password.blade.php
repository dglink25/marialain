<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe - CPEG MARIE-ALAIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('https://source.unsplash.com/random/1920x1080/?security,protection');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(13, 45, 62, 0.7);
            z-index: 0;
        }

        .reset-container {
            width: 100%;
            max-width: 500px;
            padding: 30px;
            z-index: 1;
        }

        .reset-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-section img {
            height: 90px;
            margin-bottom: 15px;
        }

        .logo-section h1 {
            color: #2c3e50;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .logo-section p {
            color: #7f8c8d;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
            font-size: 15px;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon input {
            width: 100%;
            padding: 14px 50px 14px 45px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .input-with-icon input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
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

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 18px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 6px;
        }

        .session-status {
            padding: 14px;
            margin-bottom: 25px;
            border-radius: 8px;
            text-align: center;
            font-size: 15px;
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

        .password-strength {
            margin-top: 8px;
            height: 5px;
            border-radius: 3px;
            background: #eee;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s, background 0.3s;
        }

        .password-requirements {
            margin-top: 10px;
            font-size: 13px;
            color: #7f8c8d;
        }

        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .requirement i {
            margin-right: 8px;
            font-size: 12px;
        }

        .requirement.met {
            color: #27ae60;
        }

        .submit-button {
            width: 100%;
            padding: 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }

        .submit-button:hover {
            background: #2980b9;
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            color: #3498db;
            text-decoration: none;
            font-size: 15px;
            transition: color 0.2s;
        }

        .back-link a:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .reset-container {
                padding: 20px;
            }
            
            .reset-card {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="logo-section">
                <img src="{{ asset('logo.png') }}" alt="Logo CPEG MARIE-ALAIN">
                <h1>CPEG MARIE-ALAIN</h1>
                <p>Réinitialisation du mot de passe</p>
            </div>

            <!-- Session Status -->
            @if(session('status'))
                <div class="session-status success">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="session-status error">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div class="form-group">
                    <label for="email">Adresse Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope input-icon"></i>
                        <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="email" placeholder="Votre adresse email">
                    </div>
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Nouveau mot de passe</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock input-icon"></i>
                        <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Votre nouveau mot de passe">
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                    <div class="password-requirements">
                        <div class="requirement" id="lengthReq">
                            <i class="fas fa-circle"></i>
                            <span>Au moins 8 caractères</span>
                        </div>
                        <div class="requirement" id="numberReq">
                            <i class="fas fa-circle"></i>
                            <span>Contient un chiffre</span>
                        </div>
                        <div class="requirement" id="specialReq">
                            <i class="fas fa-circle"></i>
                            <span>Contient un caractère spécial</span>
                        </div>
                    </div>
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="password_confirmation">Confirmer le mot de passe</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock input-icon"></i>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Confirmez votre mot de passe">
                        <button type="button" class="toggle-password" id="toggleConfirmPassword">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="submit-button">
                    Réinitialiser le mot de passe
                </button>
            </form>

            <div class="back-link">
                <a href="{{ route('login') }}">← Retour à la connexion</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('#togglePassword');
            const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
            const passwordInput = document.querySelector('#password');
            const confirmPasswordInput = document.querySelector('#password_confirmation');
            const passwordStrengthBar = document.querySelector('#passwordStrengthBar');
            
            // Fonction pour basculer l'affichage du mot de passe
            function setupTogglePassword(button, input) {
                const eyeIcon = button.querySelector('i');
                
                button.addEventListener('click', function() {
                    // Basculer le type de champ
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    
                    // Changer l'icône
                    if (type === 'text') {
                        eyeIcon.classList.remove('fa-eye');
                        eyeIcon.classList.add('fa-eye-slash');
                        button.setAttribute('aria-label', 'Masquer le mot de passe');
                    } else {
                        eyeIcon.classList.remove('fa-eye-slash');
                        eyeIcon.classList.add('fa-eye');
                        button.setAttribute('aria-label', 'Afficher le mot de passe');
                    }
                });
            }
            
            // Configurer les boutons d'affichage/masquage
            setupTogglePassword(togglePassword, passwordInput);
            setupTogglePassword(toggleConfirmPassword, confirmPasswordInput);
            
            // Vérification de la force du mot de passe
            passwordInput.addEventListener('input', function() {
                checkPasswordStrength(this.value);
            });
            
            function checkPasswordStrength(password) {
                let strength = 0;
                const requirements = {
                    length: password.length >= 8,
                    number: /[0-9]/.test(password),
                    special: /[^A-Za-z0-9]/.test(password)
                };
                
                // Mettre à jour les indicateurs de requis
                document.getElementById('lengthReq').classList.toggle('met', requirements.length);
                document.getElementById('numberReq').classList.toggle('met', requirements.number);
                document.getElementById('specialReq').classList.toggle('met', requirements.special);
                
                // Calculer la force
                if (requirements.length) strength += 33;
                if (requirements.number) strength += 33;
                if (requirements.special) strength += 34;
                
                // Mettre à jour la barre de progression
                passwordStrengthBar.style.width = strength + '%';
                
                // Changer la couleur en fonction de la force
                if (strength < 33) {
                    passwordStrengthBar.style.background = '#e74c3c';
                } else if (strength < 66) {
                    passwordStrengthBar.style.background = '#f39c12';
                } else {
                    passwordStrengthBar.style.background = '#27ae60';
                }
            }
        });
    </script>
</body>
</html>