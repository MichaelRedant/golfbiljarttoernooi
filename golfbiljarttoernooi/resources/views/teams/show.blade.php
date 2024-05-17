@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card mb-4">
        <div class="card-header">
            <h1 class="d-flex align-items-center">
                <i class="fas fa-users mr-2"></i> Team Details - {{ $team->name }}
            </h1>
        </div>
        <div class="card-body">
            <form action="{{ route('teams.show', $team) }}" method="GET">
                <div class="form-group">
                    <label for="season_id"><i class="fas fa-calendar-alt"></i> Kies een seizoen:</label>
                    <select id="season_id" name="season_id" class="form-control" onchange="this.form.submit()">
                        @foreach ($seasons as $season)
                            <option value="{{ $season->id }}" {{ $season->id == $currentSeasonId ? 'selected' : '' }}>
                                {{ $season->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
            <p><strong><i class="fas fa-layer-group"></i> Divisie:</strong> {{ $team->division->name }}</p>
            <p><strong><i class="fas fa-trophy"></i> Aantal Gewonnen:</strong> {{ $currentTeamStanding['games_won'] }}</p>
            <p><strong><i class="fas fa-thumbs-down"></i> Aantal Verloren:</strong> {{ $currentTeamStanding['games_lost'] }}</p>
            <p><strong><i class="fas fa-handshake"></i> Aantal Gelijk:</strong> {{ $currentTeamStanding['games_draw'] }}</p>
            <p><strong><i class="fas fa-star"></i> Totaal Punten:</strong> {{ $currentTeamStanding['points'] }}</p>
        </div>
        <div class="card-footer d-flex justify-content-between">
            @if(auth()->user() && auth()->user()->role === 'admin')
            <a href="{{ route('teams.edit', $team) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Bewerk {{ $team->name }}
            </a>
            @endif
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Terug
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-chart-line"></i> Team Rankings</h2>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Naam</th>
                        <th>Gewonnen</th>
                        <th>Verloren</th>
                        <th>Gelijk</th>
                        <th>Punten</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($standings as $index => $standing)
                        <tr @if($standing['team_id'] == $team->id) class="table-success" @endif>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $standing['team_name'] }}</td>
                            <td>{{ $standing['games_won'] }}</td>
                            <td>{{ $standing['games_lost'] }}</td>
                            <td>{{ $standing['games_draw'] }}</td>
                            <td>{{ $standing['points'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
