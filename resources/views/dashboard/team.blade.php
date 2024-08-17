@extends('layouts.app')

@section('header')
<h2 class="font-semibold text-xl leading-tight text-center">
    {{ __('Dashboard') }}
</h2>
@endsection

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12 text-center mb-4">
            <h1>Welkom {{ auth()->user()->name }}</h1>
        </div>

        @if(isset($todayGames) && $todayGames->isNotEmpty())
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-calendar-day"></i> Wedstrijden van Vandaag ({{ \Carbon\Carbon::today()->format('d-m-Y') }})</h5>
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Datum</th>
                                    <th>Thuis Team</th>
                                    <th>Uit Team</th>
                                    <th>Actie</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($todayGames as $game)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($game->date)->format('d-m-Y') }}</td>
                                        <td>
                                            <a href="{{ route('teams.show', $game->homeTeam->id) }}">{{ $game->homeTeam->name }}</a>
                                        </td>
                                        <td>
                                            <a href="{{ route('teams.show', $game->awayTeam->id) }}">{{ $game->awayTeam->name }}</a>
                                        </td>
                                        <td>
                                            @if(auth()->check() && auth()->user()->team_id == $game->home_team_id)
                                                <a href="{{ route('games.form', $game->id) }}" class="btn btn-sm btn-success">
                                                    <i class="fas fa-play"></i> Start Wedstrijd
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Existing code for team ranking, upcoming games, etc. -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-list-ol"></i> Team Ranking</h5>
                    <form action="{{ route('dashboard.team') }}" method="GET" class="mb-3">
                        <div class="form-group">
                            <label for="season_id">Selecteer Seizoen:</label>
                            <select name="season_id" id="season_id" class="form-control" onchange="this.form.submit()">
                                @foreach($seasons as $season)
                                    <option value="{{ $season->id }}" {{ $season->id == $currentSeasonId ? 'selected' : '' }}>
                                        {{ $season->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                    @if(isset($currentTeamStanding))
                        <ul class="list-group">
                            <li class="list-group-item"><i class="fas fa-users"></i> {{ $team->name }}</li>
                            <li class="list-group-item"><i class="fas fa-trophy"></i> <strong>Punten:</strong> {{ $currentTeamStanding['points'] }}</li>
                            <li class="list-group-item"><i class="fas fa-check-circle"></i> <strong>Gewonnen:</strong> {{ $currentTeamStanding['games_won'] }}</li>
                            <li class="list-group-item"><i class="fas fa-times-circle"></i> <strong>Verloren:</strong> {{ $currentTeamStanding['games_lost'] }}</li>
                            <li class="list-group-item"><i class="fas fa-handshake"></i> <strong>Gelijkspel:</strong> {{ $currentTeamStanding['games_draw'] }}</li>
                        </ul>
                    @else
                        <p>Geen ranking gegevens beschikbaar.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-calendar-alt"></i> Aankomende Wedstrijden</h5>
                    @if(isset($upcomingGames) && $upcomingGames->isNotEmpty())
                        <ul class="list-group">
                            @foreach($upcomingGames as $game)
                                <li class="list-group-item">
                                    <a href="{{ route('teams.show', $game->homeTeam->id) }}">
                                        <strong>{{ $game->homeTeam->name }}</strong>
                                    </a>
                                    vs
                                    <a href="{{ route('teams.show', $game->awayTeam->id) }}">
                                        <strong>{{ $game->awayTeam->name }}</strong>
                                    </a>
                                    op {{ $game->date->format('d-m-Y') }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p>Geen aankomende wedstrijden.</p>
                    @endif
                </div>
            </div>
        </div>

        @if(isset($pendingGames) && $pendingGames->isNotEmpty())
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-check"></i> Wachtende Goedkeuringen</h5>
                        @if($pendingGames->isEmpty())
                            <p>Geen wedstrijden wachten op goedkeuring.</p>
                        @else
                            <ul class="list-group">
                                @foreach($pendingGames as $pendingGame)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $pendingGame->homeTeam->name }} vs {{ $pendingGame->awayTeam->name }} 
                                        <a href="{{ route('games.requestApproval', $pendingGame->id) }}" class="btn btn-primary btn-sm">Goedkeuren/Afkeuren</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
