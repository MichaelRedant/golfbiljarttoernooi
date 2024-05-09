@extends('layouts.app')

@section('content')
<div class="container">
    <div class="player-card card">
        <div class="player-card-header">
            @if($player->photo)
                <img src="{{ asset('storage/photos/' . $player->photo) }}" alt="Speler Foto" class="player-avatar">
            @else
                {{-- <img src="{{ asset('placeholder.png') }}" alt="Geen foto beschikbaar"> --}}
            @endif
            <h2 class="player-name">{{ $player->first_name }} {{ $player->last_name }}</h2>
        </div>
        <div class="player-card-body">
            <div class="player-details">
                <p><strong>Team:</strong> <a href="{{ route('teams.show', $player->team_id) }}">{{ $player->team->name }}</a></p>
                <form action="{{ route('players.show', $player->id) }}" method="GET">
                    <div class="form-group">
                        <label for="season_id">Seizoen:</label>
                        <select name="season_id" id="season_id" class="form-control" onchange="this.form.submit()">
                            @foreach($seasons as $season)
                                <option value="{{ $season->id }}" {{ $season->id == $currentSeasonId ? 'selected' : '' }}>
                                    {{ $season->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="player-stats">
               {{--  <p><strong>Wedstrijden Gewonnen:</strong> {{ $gamesWon }}</p>
                <p><strong>Wedstrijden Verloren:</strong> {{ $gamesLost }}</p>
                <p><strong>Wedstrijden Gelijkgespeeld:</strong> {{ $gamesDraw }}</p> --}}
                <p><strong>Gewonnen Matchen:</strong> {{ $matchesWon }}</p>
                <p><strong>Verloren Matchen:</strong> {{ $matchesLost }}</p>
                @if(auth()->user() && auth()->user()->role === 'admin')
                <a href="{{ route('players.edit', $player->id) }}" class="btn btn-primary">Bewerk Speler</a>
                @endif
                <!-- Terug knop toevoegen -->
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Terug</a>
            </div>
        </div>
    </div>
</div>
@endsection
