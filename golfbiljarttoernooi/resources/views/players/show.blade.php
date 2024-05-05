@extends('layouts.app')

@section('content')
<div class="container">
    <div class="player-card card">
        <div class="player-card-header">
            @if($player->photo)
                <img src="{{ asset('storage/photos/' . $player->photo) }}" alt="Speler Foto" class="player-avatar">
            @else
                <img src="placeholder.png" alt="Geen foto beschikbaar"> <!-- Vervang dit met je standaard afbeelding -->
            @endif
            <h2 class="player-name">{{ $player->first_name }} {{ $player->last_name }}</h2>
        </div>
        <div class="player-card-body">
            <div class="player-details">
                <p><strong>Team:</strong> <a href="{{ route('teams.show', $player->team_id) }}">{{ $player->team->name }}</a></p>
                <!-- Add more player details here -->
            </div>
            <div class="player-stats">
                <p><strong>Matches Won:</strong> {{ $player->matches_won }}</p>
                <p><strong>Matches Lost:</strong> {{ $player->matches_lost }}</p>
                <a href="{{ route('players.edit', $player->id) }}" class="btn">Bewerk Speler</a>
            </div>
        </div>
    </div>
</div>
@endsection
