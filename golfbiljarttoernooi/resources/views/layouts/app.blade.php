<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golfbiljart</title>
    <!-- Voeg hier eventuele CSS-bestanden toe -->
    <link href="{{ asset('css/stijl.css') }}" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">


</head>
<body class="content-wrapper">
    <header>
        <!-- Navigatiemenu -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">Golfbiljart Bond Aalst</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('games.index') }}">Wedstrijdkalender</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('teams.index') }}">Teams</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('players.index') }}">Spelers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('rankings.index') }}">Rankings</a></li>
            </ul>
        </div>
    </div>
    <div class="navbar-text">
        Donkere mode: <label class="switch">
          <input type="checkbox" id="nightModeToggle">
          <span class="slider round"></span>
        </label>
    </div>
</nav>

    </header>

    <main>
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            <img src="{{ asset('images/Pixapop_black.webp') }}" alt="Pixapop Logo" class="footer-logo">
            <p class="footer-text">Designed & created by <a href="https://pixapop.be" target="_blank">Pixapop webdesign</a> © {{ date('Y') }}</p>
        </div>
    </footer>
    <!-- Optioneel JavaScript -->
<!-- jQuery eerst, dan Popper.js, dan Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nightModeToggle = document.getElementById('nightModeToggle');
    const isNightMode = localStorage.getItem('nightMode') === 'true';

    // Stel de toggle in op basis van opgeslagen voorkeur
    nightModeToggle.checked = isNightMode;
    document.body.classList.toggle('night-mode', isNightMode);

    // Luister naar veranderingen in de toggle
    nightModeToggle.addEventListener('change', function() {
        document.body.classList.toggle('night-mode', this.checked);
        // Bewaar de voorkeur in localStorage
        localStorage.setItem('nightMode', this.checked);
    });
});
</script>


</body>
</html>
