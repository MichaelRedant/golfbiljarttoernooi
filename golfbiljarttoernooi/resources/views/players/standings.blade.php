@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Speler Klassement - Divisie {{ $division->name }}</h1>
    @if ($standings->isEmpty())
        <p>Geen wedstrijden of spelergegevens gevonden voor dit seizoen.</p>
    @else
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
                        <td>{{ $standing['team_name'] }}</td>
                        <td>{{ $standing['games_won'] }}</td>
                        <td>{{ $standing['games_lost'] }}</td>
                        <td>{{ $standing['manches_won'] }}</td>
                        <td>{{ $standing['manches_lost'] }}</td>
                        <td>{{ $standing['points'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
