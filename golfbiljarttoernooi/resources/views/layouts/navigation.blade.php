<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">Golfbiljart Bond Aalst</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="{{ route('home') }}">Home <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('games.index') }}">Wedstrijden</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('rankings.index') }}">Rankings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('teams.index') }}">Teams</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('players.index') }}">Spelers</a>
                </li>
                
            </ul>
            <ul class="navbar-nav ms-auto">
                @auth
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Log Out
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
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('register') }}">Registreer</a>
                </li>
                <div class="vr mx-3"></div>
                <div class="navbar-text">
                <i class="fas fa-sun" style="color:#e5e500;"></i>
            <label class="switch">
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
