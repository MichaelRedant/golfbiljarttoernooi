@extends('layouts.app')

@section('content')
<div class="container mt-3">
    <h1>Competitie - {{ $division->name }}</h1>
    <h2>Volgende Speeldag: {{ $nextMatchday->format('d-m-Y') }}</h2>

    <!-- Aankomende of recente wedstrijden -->
    <div class="upcoming-games">
        @foreach ($upcomingGames as $game)
            <div class="game-card card mb-3">
                <div class="card-body">
                    <h5 class="card-title">{{ $game->homeTeam->name }} <span>{{ $game->home_score }} - {{ $game->away_score }}</span> {{ $game->awayTeam->name }}</h5>
                    <p class="card-text">{{ $game->date->format('d-m-Y') }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Team ranking -->
    <div class="team-ranking mt-4">
        <h2>Team Klassement</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Team</th>
                    <th>Gespeld</th>
                    <th>W</th>
                    <th>G</th>
                    <th>V</th>
                    <th>Pt.</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($standings as $index => $standing)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><a href="{{ route('teams.show', ['team' => $standing['team_id']]) }}">{{ $standing['team_name'] }}</a></td>
                        <td>{{ $standing['games_played'] }}</td>
                        <td>{{ $standing['games_won'] }}</td>
                        <td>{{ $standing['games_draw'] }}</td>
                        <td>{{ $standing['games_lost'] }}</td>
                        <td>{{ $standing['points'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Historische speeldagen -->
    <div class="past-matchdays mt-5">
        @foreach ($pastMatchdays as $date => $games)
            <div class="matchday">
                <h3>{{ $date }}</h3>
                @foreach ($games as $game)
                    <div class="past-game">
                        <span>{{ $game->homeTeam->name }}</span> <strong>{{ $game->home_score }} - {{ $game->away_score }}</strong> <span>{{ $game->awayTeam->name }}</span>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
@endsection
