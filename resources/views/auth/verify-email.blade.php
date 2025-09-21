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
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }
        
        .welcome-text {
            margin-bottom: 20px;
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
        
        .verification-icon {
            font-size: 60px;
            color: #2575fc;
            margin-bottom: 20px;
        }
        
        .info-text {
            color: #5a6c7d;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .success-message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
            font-size: 14px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .actions-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 25px;
        }
        
        .resend-form {
            margin-bottom: 10px;
        }
        
        .submit-button {
            background: #2575fc;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .submit-button:hover {
            background: #1c64e0;
        }
        
        .logout-form {
            margin-top: 5px;
        }
        
        .logout-button {
            color: #7f8c8d;
            text-decoration: none;
            font-size: 14px;
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
            
            .info-text {
                font-size: 14px;
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
            <p>Vérification de votre email</p>
        </div>
        
        <div class="right-panel">
            <div class="welcome-text">
                <h1>Vérification requise</h1>
                <p>Validez votre adresse email pour continuer</p>
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