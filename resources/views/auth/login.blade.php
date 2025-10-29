@php use Illuminate\Support\Facades\Route; @endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - CPEG MARIE-ALAIN</title>
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
            border-radius: 50%;
            justify-content: center;
            align-items: center;
            margin: 0 auto 30px auto;
            padding-top:10px;
            padding-right:20px  /* <-- centre horizontalement */
        }

        
        .logo-container img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 50%;
            border-radius: 50%;
            justify-content: center;
            align-items: center;
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
        }
        
        .login-button:hover {
            background: #1c64e0;
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

            <form method="POST" action="{{ route('login') }}">
                @csrf

               <!-- Email Address -->
                    <div class="form-group">
                        <label for="email">Adresse Email</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope input-icon"></i>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="yourroom@hotmail.com">
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
                            <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
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
                                @if(session('error'))
                    <div class="session-status error">
                        {{ session('error') }}
                    </div>
                @endif

                <button type="submit" class="login-button">
                    Se connecter
                </button>
                 </div>
            </form>
        </div>

    <script>
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
    </script>
</body>
</html>