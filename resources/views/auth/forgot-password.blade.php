<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe - CPEG MARIE-ALAIN</title>
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
            background-image: url('https://source.unsplash.com/random/1920x1080/?school,education');
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

        .info-text {
            color: #5a6c7d;
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 25px;
            text-align: center;
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
            padding: 14px 15px 14px 45px;
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

        .back-to-login {
            text-align: center;
            margin-top: 25px;
        }

        .back-to-login a {
            color: #3498db;
            text-decoration: none;
            font-size: 15px;
            transition: color 0.2s;
        }

        .back-to-login a:hover {
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
                <p>Réinitialisation de mot de passe</p>
            </div>

            <div class="info-text">
                Mot de passe oublié ? Aucun problème. Indiquez-nous simplement votre adresse e-mail et nous vous enverrons un lien de réinitialisation de mot de passe qui vous permettra d'en choisir un nouveau.
            </div>

            <!-- Session Status -->
            @if(session('status'))
                <div class="session-status success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email Address -->
                <div class="form-group">
                    <label for="email">Adresse Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope input-icon"></i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Votre adresse email">
                    </div>
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="submit-button">
                    Envoyer le lien de réinitialisation
                </button>
            </form>

            <div class="back-to-login">
                <a href="{{ route('login') }}">← Retour à la connexion</a>
            </div>
        </div>
    </div>
</body>
</html>