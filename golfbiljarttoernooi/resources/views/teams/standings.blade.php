@extends('layouts.app')

@section('content')
<div class="container card">
    <h1>Team Klassement</h1>

    <!-- Dropdown voor seizoen selectie -->
    <form action="{{ route('teams.standings') }}" method="GET">
        <div class="form-group">
            <label for="season_id">Kies een seizoen:</label>
            <select id="season_id" name="season_id" class="form-control" onchange="this.form.submit()">
                @foreach ($seasons as $season)
                    <option value="{{ $season->id }}" {{ $season->id == $currentSeasonId ? 'selected' : '' }}>
                        {{ $season->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Team</th>
                <th>Gewonnen</th>
                <th>Verloren</th>
                <th>Gelijk</th>
                <th>Punten</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($standings as $index => $standing)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><a href="{{ route('teams.show', ['team' => $standing['team_id']]) }}">{{ $standing['team_name'] }}</a></td>
                    <td>{{ $standing['games_won'] }}</td>
                    <td>{{ $standing['games_lost'] }}</td>
                    <td>{{ $standing['games_draw'] }}</td>
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
