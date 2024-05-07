@extends('layouts.app')

@section('content')
<div class="container card">
    <h1>Speler Klassement - Divisie {{ $divisionId }}</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Speler</th>
                <th>Team</th>
                <th>Wedstrijden Gewonnen</th>
                <th>Wedstrijden Gelijkspel</th>
                <th>Wedstrijden Verloren</th>
                <th>Matches Gewonnen</th>
                <th>Matches Verloren</th>
                <th>Punten</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($standings as $standing)
                <tr>
                    <td><a href="{{ route('players.show', ['player' => $standing['player_id']]) }}">{{ $standing['player_name'] }}</a></td>
                    <td><a href="{{ route('teams.show', ['team' => $standing['team_id']]) }}">{{ $standing['team_name'] }}</a></td>
                    <td>{{ $standing['games_won'] }}</td>
                    <td>{{ $standing['games_drawn'] }}</td>
                    <td>{{ $standing['games_lost'] }}</td>
                    <td>{{ $standing['matches_won'] }}</td>
                    <td>{{ $standing['matches_lost']}}</td>
                    <td>{{ $standing['points'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">
        <a href="{{ route('rankings.index') }}" class="btn btn-primary">Terug naar Overzicht</a>
    </div>
</div>
@endsection
