<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golfbiljart</title>
    <!-- Voeg hier eventuele CSS-bestanden toe -->
    <link href="{{ asset('css/stijl.css') }}" rel="stylesheet">

</head>
<body>
    <header>
        <!-- Navigatiemenu -->
        <nav>
            <ul>
                
            <li><a href="{{ url('/') }}">Home</a></li>
        <li><a href="{{ route('games.index') }}">Wedstrijdkalender</a></li>
        <li><a href="{{ route('divisions.index') }}">Divisies</a></li>
        <li><a href="{{ route('teams.index') }}">Teams</a></li>
        <li><a href="{{ route('players.index') }}">Spelers</a></li>
            </ul>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <!-- Footer inhoud -->
    </footer>
</body>
</html>
