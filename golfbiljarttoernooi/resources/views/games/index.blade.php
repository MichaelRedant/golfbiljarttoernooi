@extends('layouts.app')

@section('title', 'Wedstrijdkalender')

@section('content')
<div class="container mt-4">
    <h1>Wedstrijdkalender</h1>

    <!-- Seizoen kiezen -->
    <div class="mb-4">
        <form action="{{ route('games.index') }}" method="GET">
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
    </div>

    <!-- Actieknoppen -->
    <div class="action-buttons mb-4">
        <a href="{{ route('seasons.index') }}" class="btn btn-success">Seizoenen</a>
    </div>
    
    <!-- Kalender weergave -->
    @foreach ($gamesByDate as $date => $gamesOnDate)
        <div class="day">
            <h2>{{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</h2>
            @foreach ($gamesOnDate as $game)
                <div class="game card">
                    <div class="card-body">
                        <p>
                            <a href="{{ route('teams.show', $game->homeTeam->id) }}" class="font-weight-bold">{{ $game->homeTeam->name }}</a>
                            tegen
                            <a href="{{ route('teams.show', $game->awayTeam->id) }}" class="font-weight-bold">{{ $game->awayTeam->name }}</a>
                        </p>
                        <p><span class="font-weight-bold">Uitslag:</span> {{ $game->home_score ?? 'N/A' }} : {{ $game->away_score ?? 'N/A' }}</p>
                        @if (is_null($game->home_score) || is_null($game->away_score))
                            <a href="{{ route('games.form', $game->id) }}" class="btn btn-sm btn-primary">Speel Wedstrijd</a>
                        @else
                            <a href="{{ route('games.show', $game->id) }}" class="btn btn-sm btn-outline-secondary">Bekijk Wedstrijd</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</div>
@endsection
