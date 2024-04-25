@extends('layouts.app')

@section('content')
<div class="container card">
    <h1>Team Klassement</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Team</th>
                <th>Gewonnen</th>
                <th>Verloren</th>
                <th>Gelijk</th>
                <th>Punten</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($standings as $standing)
                <tr>
                    <td><a href="{{ route('teams.show', ['team' => $standing['team_id']]) }}">{{ $standing['team_name'] }}</a></td>
                    <td>{{ $standing['games_won'] }}</td>
                    <td>{{ $standing['games_lost'] }}</td>
                    <td>{{ $standing['games_draw'] }}</td>
                    <td>{{ $standing['points'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
