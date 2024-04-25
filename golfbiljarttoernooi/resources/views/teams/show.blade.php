@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Team Details - {{ $team->name }}</h1>
        </div>
        <div class="card-body">
            <p><strong>Divisie:</strong> {{ $team->division->name }}</p>
            <p><strong>Aantal Gewonnen:</strong> {{ $teamStats['games_won'] ?? 'Data niet beschikbaar' }}</p>
            <p><strong>Aantal Verloren:</strong> {{ $teamStats['games_lost'] ?? 'Data niet beschikbaar' }}</p>
            <p><strong>Aantal Gelijk:</strong> {{ $teamStats['games_draw'] ?? 'Data niet beschikbaar' }}</p>
            <p><strong>Totaal Punten:</strong> {{ $teamStats['points'] ?? 'Data niet beschikbaar' }}</p>
            <p><strong>Plaats dit Seizoen:</strong> {{ $teamStats['rank'] ?? 'Data niet beschikbaar' }}</p>

            <h3>Spelers</h3>
            @if($team->players->isNotEmpty())
                <ul>
                    @foreach($team->players as $player)
                        <li><a href="{{ route('players.show', $player) }}">{{ $player->first_name }} {{ $player->last_name }}</a></li>
                    @endforeach
                </ul>
            @else
                <p>Er zijn momenteel geen spelers in dit team.</p>
            @endif
        </div>
        <div class="card-footer">
            <a href="{{ route('teams.edit', $team) }}" class="btn btn-secondary">Bewerken</a>
        </div>
    </div>
</div>
@endsection



