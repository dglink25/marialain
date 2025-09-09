<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MARIE ALAIN</title>
    {{-- liens CSS vers /public --}}
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <nav>
        <a href="{{ route('home') }}">Accueil</a>
        @auth
            <a href="{{ route('schools.index') }}">Écoles</a>
            <a href="{{ route('classes.index') }}">Classes</a>
            <a href="{{ route('invitations.index') }}">Invitations</a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline">@csrf <button type="submit">Déconnexion</button></form>
        @else
            <a href="{{ route('login') }}">Connexion</a>
        @endauth
    </nav>

    <main>
        @if(session('success'))<div>{{ session('success') }}</div>@endif
        @if(session('error'))<div>{{ session('error') }}</div>@endif
        @yield('content')
    </main>
</body>
</html>