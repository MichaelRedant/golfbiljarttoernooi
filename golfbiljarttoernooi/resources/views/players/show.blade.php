<!-- Player Details View -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="player-card card">
        <div class="player-card-header">
            <img src="{{ Storage::url($player->photo) }}" alt="Speler Foto">
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
                <!-- Add more player stats here -->
            </div>
        </div>
    </div>
</div>
@endsection

