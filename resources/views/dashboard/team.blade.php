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

        <!-- Te Spelen Wedstrijden (van gisteren en vandaag) -->
        @if(isset($todayGames) && $todayGames->isNotEmpty())
<div class="col-md-12 mb-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-calendar-day"></i> Te Spelen Wedstrijden ({{ \Carbon\Carbon::today()->format('d-m-Y') }})</h5>
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
                            @if($game instanceof \App\Models\CupGame && $game->away_team_approved)
                                <a href="{{ route('cup_game.show', ['cup' => $game->cup_id, 'game' => $game->id]) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Bekijken
                                </a>
                            @elseif($gameService->canStartGame($game))
                                @if($game instanceof \App\Models\CupGame)
                                    <a href="{{ route('cup_match_form', ['cup' => $game->cup_id, 'game' => $game->id]) }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-play"></i> Spelen
                                    </a>
                                @else
                                    <a href="{{ route('games.form', $game->id) }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-play"></i> Spelen
                                    </a>
                                @endif
                            @elseif($gameService->canViewGame($game))
                                @if($game instanceof \App\Models\CupGame)
                                    <a href="{{ route('cup_game.show', ['cup' => $game->cup_id, 'game' => $game->id]) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Bekijken
                                    </a>
                                @else
                                    <a href="{{ route('games.show', $game->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Bekijken
                                    </a>
                                @endif
                            @else
                                <button class="btn btn-sm btn-secondary" disabled>
                                    <i class="fas fa-clock"></i> Niet Beschikbaar
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>             
                
            </table>
        </div>
    </div>
</div>
@else
<div class="col-md-12 mb-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-calendar-day"></i> Geen wedstrijden vandaag of gisteren</h5>
            <p>Er zijn geen wedstrijden beschikbaar om te spelen.</p>
        </div>
    </div>
</div>
@endif

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
                    <h5 class="card-title">
                        <i class="fas fa-calendar-alt"></i> Aankomende Wedstrijden
                        <i class="fas fa-chevron-down float-right toggle-icon" data-toggle="collapse" data-target="#upcoming-games-list" aria-expanded="false" aria-controls="upcoming-games-list"></i>
                    </h5>
                    <div class="collapse" id="upcoming-games-list">
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
        </div>

       <!-- Wachtende Goedkeuringen van bekerwedstrijden (Team Dashboard) -->

<div class="col-md-12 mb-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title">
                <i class="fas fa-trophy"></i> Wachtende Goedkeuringen (Bekerwedstrijden)
                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#pendingCupGamesListTeam" aria-expanded="false" aria-controls="pendingCupGamesListTeam">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </h5>
            <div class="collapse" id="pendingCupGamesListTeam">
                @if($pendingCupGames->isEmpty())
                    <p>Geen bekerwedstrijden wachten op goedkeuring.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($pendingCupGames as $pendingCupGame)
                            @if($pendingCupGame->homeTeam && $pendingCupGame->awayTeam)
                                <li class="list-group-item d-flex justify-content-between align-items-center p-1">
                                    <span>
                                        <a href="{{ route('teams.show', $pendingCupGame->homeTeam->id) }}" class="{{ $pendingCupGame->home_score > $pendingCupGame->away_score ? 'font-weight-bold' : '' }}">
                                            {{ $pendingCupGame->homeTeam->name }}
                                        </a>
                                        vs
                                        <a href="{{ route('teams.show', $pendingCupGame->awayTeam->id) }}" class="{{ $pendingCupGame->away_score > $pendingCupGame->home_score ? 'font-weight-bold' : '' }}">
                                            {{ $pendingCupGame->awayTeam->name }}
                                        </a>
                                        ({{ $pendingCupGame->home_score ?? 0 }} - {{ $pendingCupGame->away_score ?? 0 }})
                                        <span class="ml-2">{{ \Carbon\Carbon::parse($pendingCupGame->date)->format('d-m-Y') }}</span>
                                    </span>

                                    <a href="{{ route('cupGames.requestApproval', ['cup' => $pendingCupGame->cup_id, 'cupGame' => $pendingCupGame->id]) }}" class="btn btn-primary btn-sm">Goedkeuren</a>

                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>


    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle the chevron icon on collapse
        $('#upcoming-games-list').on('show.bs.collapse', function () {
            $(this).prev().find('.toggle-icon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }).on('hide.bs.collapse', function () {
            $(this).prev().find('.toggle-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        });

        // Initialize the collapse to be hidden by default
        $('#upcoming-games-list').collapse('hide');
    });
</script>

@endsection
