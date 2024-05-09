@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Team Details - {{ $team->name }}</h1>
        </div>
        <div class="card-body">
            <form action="{{ route('teams.show', $team) }}" method="GET">
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
            <p><strong>Divisie:</strong> {{ $team->division->name }}</p>
            <p><strong>Aantal Gewonnen:</strong> {{ $teamStats['games_won'] }}</p>
            <p><strong>Aantal Verloren:</strong> {{ $teamStats['games_lost'] }}</p>
            <p><strong>Aantal Gelijk:</strong> {{ $teamStats['games_draw'] }}</p>
            <p><strong>Totaal Punten:</strong> {{ $teamStats['points'] }}</p>
        </div>
        <div class="card-footer">
            @if(auth()->user() && auth()->user()->role === 'admin')
            <a href="{{ route('teams.edit', $team) }}" class="btn btn-primary">Bewerk {{ $team->name }}</a>
            @endif
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Terug</a>
        </div>
    </div>
</div>
@endsection
