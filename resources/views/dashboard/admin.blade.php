@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">Welkom {{ auth()->user()->name }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-calendar-alt"></i> Wedstrijden</h5>
                    @if(isset($currentSeason))
                        <a href="{{ route('game.create', ['season_id' => $currentSeason->id]) }}" class="btn btn-outline-secondary d-block mb-2">
                            <i class="fas fa-calendar-plus"></i> Plan Wedstrijd
                        </a>
                        @foreach($divisions as $division)
                            <a href="{{ route('games.for-division-season', ['division_id' => $division->id, 'season_id' => $currentSeason->id]) }}" class="btn btn-outline-secondary d-block mb-2">
                                <i class="fas fa-eye"></i> Bekijk Wedstrijden van {{ $division->name }}
                            </a>
                        @endforeach
                        
                    @else
                        <p>Geen seizoenen of reeksen beschikbaar.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-users"></i> Gebruikersbeheer</h5>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-primary d-block mb-2">
                        <i class="fas fa-users"></i> Bekijk Gebruikers
                    </a>
                    <a href="{{ route('users.create') }}" class="btn btn-outline-secondary d-block mb-2">
                        <i class="fas fa-user-plus"></i> Nieuwe Gebruiker
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-edit"></i> Beheer</h5>
                    <a href="{{ route('seasons.index') }}" class="btn btn-outline-secondary d-block mb-2">
                        <i class="fas fa-calendar-alt"></i> Seizoenen Beheren
                    </a>
                    <a href="{{ route('clubs.index') }}" class="btn btn-outline-secondary d-block mb-2">
                        <i class="fas fa-building"></i> Clubs Beheren
                    </a>
                    <a href="{{ route('divisions.index') }}" class="btn btn-outline-secondary d-block mb-2">
                        <i class="fas fa-sitemap"></i> Reeksen Beheren
                    </a>
                    <a href="{{ route('teams.index') }}" class="btn btn-outline-secondary d-block mb-2">
                        <i class="fas fa-users"></i> Teams Beheren
                    </a>
                    <a href="{{ route('players.index') }}" class="btn btn-outline-secondary d-block mb-2">
                        <i class="fas fa-user"></i> Spelers Beheren
                    </a>
                    <a href="{{ route('cups.index') }}" class="btn btn-outline-secondary d-block mb-2">
                        <i class="fas fa-trophy"></i> Beker Beheer
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-newspaper"></i> Nieuwsbeheer</h5>
                    <a href="{{ route('news.index') }}" class="btn btn-outline-secondary d-block mb-2">
                        <i class="fas fa-eye"></i> Bekijk Nieuws
                    </a>
                    <a href="{{ route('news.create') }}" class="btn btn-outline-secondary d-block mb-2">
                        <i class="fas fa-plus"></i> Voeg Nieuws Toe
                    </a>
                </div>
            </div>
        </div>


       <!-- Wachtende Goedkeuringen van reguliere wedstrijden -->
<div class="col-md-12 mb-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title">
                <i class="fas fa-check"></i> Wachtende Goedkeuringen (Reguliere Wedstrijden)
                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#pendingGamesList" aria-expanded="false" aria-controls="pendingGamesList">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </h5>
            <div class="collapse" id="pendingGamesList">
                @if($pendingGames->isEmpty())
                    <p>Geen wedstrijden wachten op goedkeuring.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($pendingGames as $pendingGame)
                            @if($pendingGame->homeTeam && $pendingGame->awayTeam)
                                <li class="list-group-item d-flex justify-content-between align-items-center p-1">
                                    <span>
                                        <a href="{{ route('teams.show', $pendingGame->homeTeam->id) }}" class="{{ $pendingGame->home_score > $pendingGame->away_score ? 'font-weight-bold' : '' }}">
                                            {{ $pendingGame->homeTeam->name }}
                                        </a>
                                        vs
                                        <a href="{{ route('teams.show', $pendingGame->awayTeam->id) }}" class="{{ $pendingGame->away_score > $pendingGame->home_score ? 'font-weight-bold' : '' }}">
                                            {{ $pendingGame->awayTeam->name }}
                                        </a>
                                        ({{ $pendingGame->home_score ?? 0 }} - {{ $pendingGame->away_score ?? 0 }})
                                        <span class="ml-2">{{ \Carbon\Carbon::parse($pendingGame->date)->format('d-m-Y') }}</span>
                                    </span>

                                    <a href="{{ route('games.requestApproval', $pendingGame->id) }}" class="btn btn-primary btn-sm">Goedkeuren</a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Wachtende Goedkeuringen van bekerwedstrijden -->
<div class="col-md-12 mb-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title">
                <i class="fas fa-trophy"></i> Wachtende Goedkeuringen (Bekerwedstrijden)
                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#pendingCupGamesList" aria-expanded="false" aria-controls="pendingCupGamesList">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </h5>
            <div class="collapse" id="pendingCupGamesList">
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


        <!-- Wedstrijden van Vandaag en Gisteren per Divisie -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-calendar-day"></i> Wedstrijden
                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#todayGamesList" aria-expanded="false" aria-controls="todayGamesList">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </h5>
                
                    <div class="collapse" id="todayGamesList">
                        @if(isset($todayGames) && !$todayGames->isEmpty())
                            @foreach($divisions as $division)
                                @php
                                    $divisionGames = $todayGames->filter(function($game) use ($division) {
                                        return $game->division_id === $division->id;
                                    });
                                @endphp
                                
                                @if(!$divisionGames->isEmpty())
                                    <h6 class="font-weight-bold">{{ $division->name }}</h6>
                                    <ul class="list-group list-group-flush mb-3">
                                        @foreach($divisionGames as $todayGame)
                                            <li class="list-group-item d-flex justify-content-between align-items-center p-1">
                                                <span>
                                                    @if($todayGame->homeTeam)
                                                        <a href="{{ route('teams.show', $todayGame->homeTeam->id) }}">
                                                            {{ $todayGame->homeTeam->name }}
                                                        </a>
                                                    @else
                                                        Onbekend Team
                                                    @endif
                                
                                                    vs
                                
                                                    @if($todayGame->awayTeam)
                                                        <a href="{{ route('teams.show', $todayGame->awayTeam->id) }}">
                                                            {{ $todayGame->awayTeam->name }}
                                                        </a>
                                                    @else
                                                        Onbekend Team
                                                    @endif
                                
                                                    ({{ $todayGame->home_score ?? 0 }} - {{ $todayGame->away_score ?? 0 }})
                                
                                                    <span class="ml-2">{{ \Carbon\Carbon::parse($todayGame->date)->format('d-m-Y') }}</span>
                                                </span>
                                
                                                @if($todayGame->can_start)
                                                    <a href="{{ route('games.form', $todayGame->id) }}" class="btn btn-primary btn-sm">Speel Wedstrijd</a>
                                                @else
                                                    <span class="badge badge-secondary">Niet Beschikbaar</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            @endforeach
                        @else
                            <p>Geen wedstrijden beschikbaar om te spelen.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('selectAll').addEventListener('change', function (e) {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name="game_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = e.target.checked);
        });
    });
</script>
@endsection
