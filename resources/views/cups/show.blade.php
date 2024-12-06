@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="text-center mb-5">Beker: {{ $cup->name }}</h1>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if ($cup->rounds->isEmpty())
        <div class="alert alert-info text-center">
            Er zijn nog geen rondes toegevoegd aan deze beker.
        </div>
    @else
        @foreach ($cup->rounds as $round)
            <h3 class="text-center bg-light p-3 rounded" style="font-size: 1.5rem;">{{ $round->round_name }}</h3>

            @foreach ($round->games as $game)
                @if (!$game->away_team_id || !$game->home_team_id)
                    <!-- Bye wedstrijd -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body text-center">
                            <h4 class="font-weight-bold">
                                {{ $game->homeTeam->name ?? $game->awayTeam->name }}
                            </h4>
                            <p class="text-muted">Vrij</p>

                            <div class="text-center mt-4">
                                @auth
                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('cups.games.edit', ['cup' => $cup->id, 'game' => $game->id]) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Details
                                        </a>
                                        <form action="{{ route('cups.games.destroy', ['cup' => $cup->id, 'game' => $game->id]) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Weet je zeker dat je deze vrijgestelde wedstrijd wilt verwijderen?')">
                                                <i class="fas fa-trash-alt"></i> Verwijderen
                                            </button>
                                        </form>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Normale wedstrijd -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-4 text-center">
                                    <h4>
                                        <a href="{{ route('teams.show', $game->homeTeam->id ?? '#') }}" class="text-dark">
                                            {{ $game->homeTeam->name ?? 'Onbekend' }}
                                        </a>
                                    </h4>
                                </div>
                                <div class="col-md-4 text-center">
                                    <h4 class="font-weight-bold">VS</h4>
                                    <p class="text-muted mb-0">{{ $game->date ? $game->date->format('d-m-Y') : 'Geen datum' }}</p>
                                    <h5 class="mt-3">
                                        <span class="badge badge-info">
                                            @if ($game->away_team_approved)
                                                {{ $game->home_score ?? '-' }} - {{ $game->away_score ?? '-' }}
                                            @else
                                                -
                                            @endif
                                        </span>
                                    </h5>
                                </div>
                                <div class="col-md-4 text-center">
                                    <h4>
                                        <a href="{{ route('teams.show', $game->awayTeam->id ?? '#') }}" class="text-dark">
                                            {{ $game->awayTeam->name ?? 'Onbekend' }}
                                        </a>
                                    </h4>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                @auth
                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('cups.games.edit', ['cup' => $cup->id, 'game' => $game->id]) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Details
                                        </a>
                                        <form action="{{ route('cups.games.destroy', ['cup' => $cup->id, 'game' => $game->id]) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Weet je zeker dat je deze wedstrijd wilt verwijderen?')">
                                                <i class="fas fa-trash-alt"></i> Verwijderen
                                            </button>
                                        </form>
                                        <a href="{{ route('cupGames.edit', ['game' => $game->id]) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Aanpassen
                                        </a>
                                    @endif

                                    @php
                                        $isToday = \Carbon\Carbon::parse($game->date)->isToday();
                                    @endphp

                                    @if(auth()->user()->isAdmin() || (auth()->user()->team_id == $game->home_team_id && $isToday))
                                        <a href="{{ route('cups.games.start', ['cup' => $cup->id, 'game' => $game->id]) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-play"></i> Speel
                                        </a>
                                    @endif
                                @endauth

                                @if($game->away_team_approved)
                                    <a href="{{ route('cup_game.show', ['game' => $game->id]) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Bekijken
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @endforeach
    @endif

    @auth
        @if(auth()->user()->isAdmin())
            <div class="mt-5 text-center">
                <a href="{{ route('cups.edit', ['cup' => $cup->id]) }}" class="btn btn-secondary">
                    <i class="fas fa-edit"></i> Bewerk {{ $cup->name }}
                </a>
                <a href="{{ route('cups.select-games', ['cup' => $cup->id]) }}" class="btn btn-success">
                    <i class="fas fa-plus-circle"></i> Voeg nieuwe wedstrijd toe aan {{ $cup->name }}
                </a>
            </div>
        @endif
    @endauth
</div>
@endsection
