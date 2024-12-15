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
            <h1>Welkom, {{ auth()->user()->name }}</h1>
        </div>

        <!-- Te Spelen Wedstrijden (van gisteren en vandaag) -->
        @if(isset($todayGames) && $todayGames->isNotEmpty())
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-calendar-day"></i> Te Spelen Wedstrijden ({{ \Carbon\Carbon::today()->format('d-m-Y') }})
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-dark">
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
        </div>
        @else
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-calendar-day"></i> Geen wedstrijden vandaag of gisteren</h5>
                    <p class="text-muted">Er zijn geen wedstrijden beschikbaar om te spelen.</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Aankomende Wedstrijden -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-calendar-alt"></i> Aankomende Wedstrijden</span>
                        <button class="btn btn-link p-0" type="button" data-toggle="collapse" data-target="#upcoming-games-list" aria-expanded="false" aria-controls="upcoming-games-list">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </h5>
                    <div class="collapse" id="upcoming-games-list">
                        @if(isset($upcomingGames) && $upcomingGames->isNotEmpty())
                            <ul class="list-group">
                                @foreach($upcomingGames as $game)
                                <li class="list-group-item">
                                    <strong>{{ $game->homeTeam->name ?? 'Onbekend' }}</strong> vs <strong>{{ $game->awayTeam->name ?? 'Onbekend' }}</strong>
                                    <span class="text-muted d-block">Datum: {{ $game->date->format('d-m-Y') }}</span>
                                </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">Geen aankomende wedstrijden.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Wachtende Goedkeuringen -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-trophy"></i> Wachtende Goedkeuringen</span>
                        <button class="btn btn-link p-0" type="button" data-toggle="collapse" data-target="#pendingGamesList" aria-expanded="true" aria-controls="pendingGamesList">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                    </h5>
                    <div class="collapse show" id="pendingGamesList">
                        @if($pendingGames->isEmpty())
                            <p class="text-muted">Geen wedstrijden wachten op goedkeuring.</p>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach($pendingGames as $pendingGame)
                                <li class="list-group-item d-flex justify-content-between">
                                    <div>
                                        <strong>{{ $pendingGame->homeTeam->name }}</strong> vs <strong>{{ $pendingGame->awayTeam->name }}</strong>
                                        <span class="text-muted">({{ $pendingGame->home_score }} - {{ $pendingGame->away_score }})</span>
                                    </div>
                                    <a href="{{ route('games.requestApproval', $pendingGame->id) }}" class="btn btn-primary btn-sm">Goedkeuren</a>
                                </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Bekerwedstrijden Wachtend op Goedkeuring -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-medal"></i> Bekerwedstrijden Wachtend op Goedkeuring</span>
                        <button class="btn btn-link p-0" type="button" data-toggle="collapse" data-target="#pendingCupGamesList" aria-expanded="true" aria-controls="pendingCupGamesList">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                    </h5>
                    <div class="collapse show" id="pendingCupGamesList">
                        @if(isset($pendingCupGames) && $pendingCupGames->isNotEmpty())
                            <ul class="list-group list-group-flush">
                                @foreach($pendingCupGames as $cupGame)
                                <li class="list-group-item d-flex justify-content-between">
                                    <div>
                                        <strong>{{ $cupGame->homeTeam->name }}</strong> vs <strong>{{ $cupGame->awayTeam->name }}</strong>
                                        <span class="text-muted">({{ $cupGame->home_score }} - {{ $cupGame->away_score }})</span>
                                    </div>
                                    <a href="{{ route('cupGames.requestApproval', ['cup' => $cupGame->cup_id, 'cupGame' => $cupGame->id]) }}" class="btn btn-primary btn-sm">Goedkeuren</a>
                                </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">Geen bekerwedstrijden wachten op goedkeuring.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle chevrons in collapsible sections
        $('.collapse').on('show.bs.collapse', function () {
            $(this).prev('.card-title').find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }).on('hide.bs.collapse', function () {
            $(this).prev('.card-title').find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        });
    });
</script>
@endsection
