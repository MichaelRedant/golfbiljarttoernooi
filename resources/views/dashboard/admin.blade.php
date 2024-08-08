@extends('layouts.app')

@section('header')
<h2 class="font-semibold text-xl leading-tight">
    {{ __('Dashboard') }}
</h2>
@endsection

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
                    @if(isset($divisions) && $divisions->isNotEmpty() && isset($currentSeason))
                        @foreach($divisions as $division)
                            <a href="{{ route('game.create', ['division_id' => $division->id, 'season_id' => $currentSeason->id]) }}" class="btn btn-outline-secondary d-block mb-2">
                                <i class="fas fa-calendar-plus"></i> Plan Wedstrijd
                            </a>
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
 
        <!-- New section for quick access to edit seasons, clubs, divisions, teams, and players -->
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
                </div>
            </div>
        </div>

        <!-- New section for news management -->
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

        <!-- New section for sponsor management -->
        <div class="col-md-12">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-ad"></i> Sponsorbeheer</h5>
                    <a href="{{ route('sponsors.index') }}" class="btn btn-outline-secondary d-block mb-2">
                        <i class="fas fa-eye"></i> Bekijk Sponsors
                    </a>
                    <a href="{{ route('sponsors.create') }}" class="btn btn-outline-secondary d-block mb-2">
                        <i class="fas fa-plus"></i> Voeg Sponsor Toe
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-check"></i> Wachtende Goedkeuringen</h5>
                    @if($pendingGames->isEmpty())
                        <p>Geen wedstrijden wachten op goedkeuring.</p>
                    @else
                        <form action="{{ route('games.bulkApprove') }}" method="POST">
                            @csrf
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                                <label class="form-check-label" for="selectAll">Selecteer alles</label>
                            </div>
                            <ul class="list-group list-group-flush">
                                @foreach($pendingGames as $pendingGame)
                                    @if($pendingGame->homeTeam && $pendingGame->awayTeam)
                                    <li class="list-group-item d-flex justify-content-between align-items-center p-1">
                                        <input type="checkbox" name="game_ids[]" value="{{ $pendingGame->id }}">
                                        <span>
                                            <a href="{{ route('teams.show', $pendingGame->homeTeam->id) }}" class="{{ $pendingGame->home_score > $pendingGame->away_score ? 'font-weight-bold' : '' }}">
                                                {{ $pendingGame->homeTeam->name }}
                                            </a>
                                            vs
                                            <a href="{{ route('teams.show', $pendingGame->awayTeam->id) }}" class="{{ $pendingGame->away_score > $pendingGame->home_score ? 'font-weight-bold' : '' }}">
                                                {{ $pendingGame->awayTeam->name }}
                                            </a>
                                            ({{ $pendingGame->home_score }} - {{ $pendingGame->away_score }})
                                        </span>
                                        <a href="{{ route('games.requestApproval', $pendingGame->id) }}" class="btn btn-primary btn-sm">Bekijk Details</a>
                                    </li>
                                    @endif
                                @endforeach
                            </ul>
                            <button type="submit" class="btn btn-success mt-3">Goedkeuren Geselecteerde Wedstrijden</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('selectAll').addEventListener('change', function (e) {
        const checkboxes = document.querySelectorAll('input[type="checkbox"][name="game_ids[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = e.target.checked);
    });
});
</script>
