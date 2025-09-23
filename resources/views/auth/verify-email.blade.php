<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification d'email - CPEG MARIE-ALAIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('https://source.unsplash.com/random/1920x1080/?email,verification');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            padding: 20px;
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

        .verification-container {
            width: 100%;
            max-width: 550px;
            z-index: 1;
        }

        .verification-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.4);
            text-align: center;
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
            line-height: 1.6;
            margin-bottom: 25px;
            text-align: center;
        }

        .verification-icon {
            font-size: 60px;
            color: #3498db;
            margin-bottom: 20px;
        }

        .success-message {
            padding: 14px;
            margin-bottom: 25px;
            border-radius: 8px;
            text-align: center;
            font-size: 15px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .actions-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 30px;
        }

        .resend-form {
            margin-bottom: 15px;
        }

        .submit-button {
            padding: 12px 24px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .submit-button:hover {
            background: #2980b9;
        }

        .logout-form {
            margin-top: 10px;
        }

        .logout-button {
            color: #7f8c8d;
            text-decoration: none;
            font-size: 15px;
            transition: color 0.2s;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
        }

        .logout-button:hover {
            color: #e74c3c;
            text-decoration: underline;
        }

        @media (min-width: 640px) {
            .actions-container {
                flex-direction: row;
                justify-content: center;
                align-items: center;
            }
            
            .resend-form {
                margin-bottom: 0;
                margin-right: 15px;
            }
            
            .logout-form {
                margin-top: 0;
            }
        }

        @media (max-width: 600px) {
            .verification-card {
                padding: 30px;
            }
            
            .info-text {
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-card">
            <div class="logo-section">
                <img src="{{ asset('logo.png') }}" alt="Logo CPEG MARIE-ALAIN">
                <h1>CPEG MARIE-ALAIN</h1>
                <p>Vérification de votre email</p>
            </div>

            <div class="verification-icon">
                <i class="fas fa-envelope-circle-check"></i>
            </div>

            <div class="info-text">
                Merci pour votre inscription ! Avant de commencer, pourriez-vous vérifier votre adresse email en cliquant sur le lien que nous venons de vous envoyer ? Si vous n'avez pas reçu l'email, nous vous en enverrons un autre avec plaisir.
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="success-message">
                    Un nouveau lien de vérification a été envoyé à l'adresse email que vous avez fournie lors de l'inscription.
                </div>
            @endif

            <div class="actions-container">
                <form method="POST" action="{{ route('verification.send') }}" class="resend-form">
                    @csrf
                    <button type="submit" class="submit-button">
                        <i class="fas fa-paper-plane"></i>
                        Renvoyer l'email de vérification
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="logout-form">
                    @csrf
                    <button type="submit" class="logout-button">
                        <i class="fas fa-sign-out-alt"></i>
                        Se déconnecter
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>