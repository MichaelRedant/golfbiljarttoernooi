@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4 text-center"><i class="fas fa-trophy"></i> Speler Klassement</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('rankings.players', ['division' => $division->id]) }}" method="GET" class="mb-4">
                <div class="form-group">
                    <label for="season_id"><i class="fas fa-calendar-alt"></i> Kies een seizoen:</label>
                    <select id="season_id" name="season_id" class="form-control" onchange="this.form.submit()">
                        @foreach ($seasons as $season)
                            <option value="{{ $season->id }}" {{ $season->id == $seasonId ? 'selected' : '' }}>
                                {{ $season->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            @if (empty($standings))
    <div class="alert alert-warning" role="alert">
        Geen klassement beschikbaar voor de geselecteerde reeks en seizoen.
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Speler</th>
                    <th>Team</th>
                    <th>Gewonnen</th>
                    <th>Verloren</th>
                    <th>Punten</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($standings as $index => $standing)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><a href="{{ route('players.show', ['player' => $standing['player_id']]) }}">{{ $standing['player_name'] }}</a></td>
                        <td><a href="{{ route('teams.show', ['team' => $standing['team_id']]) }}">{{ $standing['team_name'] }}</a></td>
                        <td>{{ $standing['matches_won'] }}</td>
                        <td>{{ $standing['matches_lost']}}</td>
                        <td>{{ $standing['points'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
        </div>
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('rankings.index') }}" class="btn btn-secondary btn-lg w-100"><i class="fas fa-arrow-left"></i> Terug naar Overzicht</a>
    </div>
</div>
@endsection
