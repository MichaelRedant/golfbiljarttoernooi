<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">Golfbiljart Bond Aalst</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="{{ route('home') }}">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownDivisions" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Divisies
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownDivisions">
                        @foreach ($divisions as $division)
                        <a class="dropdown-item" href="{{ route('divisions.show', $division->id) }}">
                            {{ $division->name }}
                        </a>
                        @endforeach
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('rankings.index') }}">Rankings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $hasLiveMatches ? 'text-primary' : '' }}" href="{{ route('live-scores') }}">Live</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownInfo" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Informatie
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownInfo">
                        <a class="dropdown-item" href="{{ route('clubs.index') }}">Clubs</a>
                        <a class="dropdown-item" href="{{ route('teams.index') }}">Teams</a>
                        <a class="dropdown-item" href="{{ route('players.index') }}">Spelers</a>
                        <a class="dropdown-item" href="{{ route('teams.addresses') }}">Adressen</a>
                    </div>
                </li>
                @auth
                    @if(Auth::user()->role == 'admin')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">Admin Dashboard</a>
                        </li>
                    @endif
                @endauth
            </ul>
            <ul class="navbar-nav ms-auto">
                @auth
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                        <a class="dropdown-item" href="{{ route('dashboard') }}">Dashboard</a>
                        @if(Auth::user()->role == 'admin')
                            <a class="dropdown-item" href="{{ route('players.create') }}">Nieuwe Speler Toevoegen</a>
                            <a class="dropdown-item" href="{{ route('teams.create') }}">Nieuw Team Toevoegen</a>
                            <a class="dropdown-item" href="{{ route('divisions.create') }}">Nieuwe Divisie Toevoegen</a>
                            <a class="dropdown-item" href="{{ route('clubs.create') }}">Nieuwe Club Toevoegen</a>
                            <a class="dropdown-item" href="{{ route('seasons.create') }}">Nieuw Seizoen Toevoegen</a>
                            <a class="dropdown-item" href="{{ route('games.create', ['division_id' => $divisions->first()->id ?? null, 'season_id' => $currentSeason->id ?? null]) }}">Nieuwe Wedstrijd</a> <!-- Corrected this line -->
                        @endif
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Uitloggen
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </li>
                <div class="vr mx-3"></div>
                <div class="navbar-text d-flex align-items-center">
                    <i class="fas fa-sun" style="color:#e5e500;"></i>
                    <label class="switch mx-2">
                        <input type="checkbox" id="nightModeToggle">
                        <span class="slider round"></span>
                    </label>
                    <i class="fas fa-moon" style="color: black;"></i>
                </div>
                @endauth
                @guest
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">Login</a>
                </li>
                <div class="vr mx-3"></div>
                <div class="navbar-text d-flex align-items-center">
                    <i class="fas fa-sun" style="color:#e5e500;"></i>
                    <label class="switch mx-2">
                        <input type="checkbox" id="nightModeToggle">
                        <span class="slider round"></span>
                    </label>
                    <i class="fas fa-moon" style="color: black;"></i>
                </div>
                @endguest
            </ul>
        </div>
    </div>
</nav>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const nightModeToggle = document.getElementById('nightModeToggle');
        const isNightMode = localStorage.getItem('nightMode') === 'true';
    
        // Set the toggle based on saved preference
        nightModeToggle.checked = isNightMode;
        document.body.classList.toggle('night-mode', isNightMode);
    
        // Listen for changes in the toggle
        nightModeToggle.addEventListener('change', function() {
            document.body.classList.toggle('night-mode', this.checked);
            // Save the preference in localStorage
            localStorage.setItem('nightMode', this.checked);
        });
    });
</script>
