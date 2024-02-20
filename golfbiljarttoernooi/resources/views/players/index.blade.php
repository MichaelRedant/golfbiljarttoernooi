@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Spelerslijst</h1>

    @php
        // Groepeer de spelers per team
        $playersByTeam = $players->sortBy('last_name')->groupBy('team.name');
    @endphp

    @foreach ($playersByTeam as $teamName => $players)
        <h2>{{ $teamName ?? 'Niet toegewezen' }}</h2>
        @foreach ($players as $player)
            <div class="player">
                <p><strong>Naam:</strong> {{ $player->first_name }} {{ $player->last_name }}</p>
                <p><strong>Divisie:</strong> {{ $player->division->name ?? 'Niet toegewezen' }}</p>
                <p><strong>Team:</strong> {{ $player->team->name ?? 'Niet toegewezen' }}</p>

                <div class="actions">
                    <a href="{{ route('players.show', $player->id) }}" class="btn btn-primary">Bekijk</a>
                    <a href="{{ route('players.edit', $player->id) }}" class="btn btn-warning">Bewerk</a>
                    <form action="{{ route('players.destroy', $player->id) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Weet je zeker dat je deze speler wilt verwijderen?')">Verwijder</button>
                    </form>
                </div>
            </div>
            <!-- Voeg een horizontale lijn toe na elke speler -->
            <hr>
        @endforeach
    @endforeach

    <a href="{{ route('players.create') }}" class="btn btn-primary">Nieuwe Speler Toevoegen</a>
</div>
@endsection
