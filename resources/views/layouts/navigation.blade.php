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
                        Reeksen
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
                        <a class="dropdown-item" href="{{ route('games.kalender') }}">Kalender</a>
                        <a class="dropdown-item" href="{{ route('divisions.index') }}">Reeksen</a>
                        <a class="dropdown-item" href="{{ route('clubs.index') }}">Clubs</a>
                        <a class="dropdown-item" href="{{ route('teams.index') }}">Teams</a>
                        <a class="dropdown-item" href="{{ route('players.index') }}">Spelers</a>
                        <a class="dropdown-item" href="{{ route('teams.addresses') }}">Adressen</a>
                    </div>
                </li>
                @auth
                    @if(Auth::user()->role == 'admin')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdminDashboard" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Dashboard
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdownAdminDashboard">
                                <a class="dropdown-item" href="{{ route('dashboard') }}">Admin Dashboard</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('players.create') }}">Nieuwe Speler Toevoegen</a>
                                <a class="dropdown-item" href="{{ route('teams.create') }}">Nieuw Team Toevoegen</a>
                                <a class="dropdown-item" href="{{ route('divisions.create') }}">Nieuwe Reeks Toevoegen</a>
                                <a class="dropdown-item" href="{{ route('clubs.create') }}">Nieuwe Club Toevoegen</a>
                                <a class="dropdown-item" href="{{ route('seasons.create') }}">Nieuw Seizoen Toevoegen</a>
                                <a class="dropdown-item" href="{{ route('game.create') }}">Nieuwe Wedstrijd</a>
                            </div>
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
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Uitloggen
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </li>
                @endauth
                @guest
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">Login</a>
                </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.nav-item.dropdown').forEach(function(dropdown) {
            dropdown.addEventListener('mouseover', function() {
                const dropdownMenu = this.querySelector('.dropdown-menu');
                const dropdownToggle = this.querySelector('.dropdown-toggle');
                if (!dropdownToggle.classList.contains('show')) {
                    dropdownToggle.classList.add('show');
                    dropdownMenu.classList.add('show');
                }
            });
            dropdown.addEventListener('mouseleave', function() {
                const dropdownMenu = this.querySelector('.dropdown-menu');
                const dropdownToggle = this.querySelector('.dropdown-toggle');
                if (dropdownToggle.classList.contains('show')) {
                    dropdownToggle.classList.remove('show');
                    dropdownMenu.classList.remove('show');
                }
            });
        });
    });
</script>
