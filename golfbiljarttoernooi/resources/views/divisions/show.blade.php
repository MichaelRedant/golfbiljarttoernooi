@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $division->name }} - Overzicht</h1>

    <h2>Volgende wedstrijden</h2>
    @foreach ($nextGames as $game)
        <p>{{ $game->homeTeam->name }} vs {{ $game->awayTeam->name }} - {{ $game->date->toFormattedDateString() }}</p>
    @endforeach

    <h2>Vorige wedstrijden</h2>
    @foreach ($pastGames as $game)
        <p>{{ $game->homeTeam->name }} {{ $game->home_score }} - {{ $game->away_score }} {{ $game->awayTeam->name }}</p>
    @endforeach

    <h2>Standen</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Team</th>
                <th>W</th>
                <th>D</th>
                <th>L</th>
                <th>Points</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($standings as $standing)
                <tr>
                    <td>{{ $standing['team_name'] }}</td>
                    <td>{{ $standing['games_won'] }}</td>
                    <td>{{ $standing['games_drawn'] }}</td>
                    <td>{{ $standing['games_lost'] }}</td>
                    <td>{{ $standing['points'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
