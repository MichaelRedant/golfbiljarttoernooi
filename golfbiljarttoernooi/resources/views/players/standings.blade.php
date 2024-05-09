@extends('layouts.app')

@section('content')
<div class="container card">
    <h1>Speler Klassement - Divisie {{ $divisionId }}</h1>

    <!-- Dropdown voor seizoen selectie -->
    <form action="{{ route('players.standings', $divisionId) }}" method="GET">
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
    <!-- Tabel met rankings -->
    <table class="table">
        <thead>
            <tr>
                <th class="text-justify">#</th> <!-- Nieuwe kolom voor rangnummer -->
                <th  class="text-justify">Speler</th>
                <th  class="text-justify">Team</th>
                <th  class="text-justify">Matches Gewonnen</th>
                <th  class="text-justify">Matches Verloren</th>
                <th  class="text-justify">Punten</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($standings as $index => $standing)
                <tr>
                    <td  class="text-justify">{{ $index + 1 }}</td> <!-- Voeg rangnummer toe -->
                    <td  class="text-justify" ><a href="{{ route('players.show', ['player' => $standing['player_id']]) }}">{{ $standing['player_name'] }}</a></td>
                    <td  class="text-justify"><a href="{{ route('teams.show', ['team' => $standing['team_id']]) }}">{{ $standing['team_name'] }}</a></td>
                    <td  class="text-justify">{{ $standing['matches_won'] }}</td>
                    <td  class="text-justify">{{ $standing['matches_lost']}}</td>
                    <td  class="text-justify">{{ $standing['points'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">
        <a href="{{ route('rankings.index') }}" class="btn btn-primary">Terug naar Overzicht</a>
    </div>
</div>
@endsection
