@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center">
                @if($player->photo)
                    <img src="{{ asset('storage/photos/' . $player->photo) }}" alt="Speler Foto" class="rounded-circle mr-3" style="width: 100px; height: 100px;">
                @else
                    <img src="{{ asset('images/placeholder-avatar.png') }}" alt="Geen foto beschikbaar" class="rounded-circle mr-3" style="width: 100px; height: 100px;">
                @endif
                <h1 class="h4 mb-0">{{ $player->first_name }} {{ $player->last_name }}</h1>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <strong><i class="fas fa-users"></i> Team:</strong> <a href="{{ route('teams.show', $player->team_id) }}">{{ $player->team->name }}</a>
            </div>
            <form action="{{ route('players.show', $player->id) }}" method="GET">
                <div class="form-group">
                    <label for="season_id"><i class="fas fa-calendar-alt"></i> Seizoen:</label>
                    <select name="season_id" id="season_id" class="form-control" onchange="this.form.submit()">
                        @foreach($seasons as $season)
                            <option value="{{ $season->id }}" {{ $season->id == $currentSeasonId ? 'selected' : '' }}>
                                {{ $season->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
            <div>
                <p><strong><i class="fas fa-trophy"></i> Gewonnen Matchen:</strong> {{ $matchesWon }}</p>
                <p><strong><i class="fas fa-thumbs-down"></i> Verloren Matchen:</strong> {{ $matchesLost }}</p>
            </div>
            @if(auth()->user() && (auth()->user()->role === 'admin' || auth()->user()->role === 'speler'))
            <a href="{{ route('players.edit', $player->id) }}" class="btn btn-primary"><i class="fas fa-edit"></i> Bewerk Speler</a>
            @endif
            <!-- Terug knop toevoegen -->
            <a href="{{ url()->previous() }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Terug</a>
        </div>
    </div>
</div>
@endsection
