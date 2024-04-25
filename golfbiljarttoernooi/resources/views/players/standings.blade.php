@extends('layouts.app')

@section('content')
<div class="container card">
    <h1>Speler Klassement - Divisie {{ $divisionId }}</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Speler</th>
                <th>Team</th>
                <th>Games Gewonnen</th>
                <th>Games Verloren</th>
                <th>Manches Gewonnen</th>
                <th>Manches Verloren</th>
                <th>Punten</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($standings as $standing)
                <tr>
                    <td><a href="{{ route('players.show', ['player' => $standing['player_id']]) }}">{{ $standing['player_name'] }}</a></td>
                    <td><a href="{{ route('teams.show', $standing['team_id']) }}">{{ $standing['team_name'] }}</a></td>
                    <td>{{ $standing['games_won'] }}</td>
                    <td>{{ $standing['games_lost'] }}</td>
                    <td>{{ $standing['manches_won'] }}</td>
                    <td>{{ $standing['manches_lost'] }}</td>
                    <td>{{ $standing['points'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection