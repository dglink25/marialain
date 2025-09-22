<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPEG MARIE-ALAIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100 text-gray-800">
    <div class="bg-white shadow-lg rounded-lg p-8 max-w-lg text-center">
        <h1 class="text-3xl font-bold text-red-600 mb-4">Aucune année académique active pour le moment</h1>
        <p class="text-lg mb-6">Veuillez contactez le fondé ou l'administrateur de la plateforme pour accéder à cette fonctionnalité.</p>
        <a href="{{ url()->previous() }}" 
           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Retour
        </a>
    </div>
</body>
</html>
