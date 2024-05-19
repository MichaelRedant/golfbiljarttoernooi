@extends('layouts.app')

@section('content')
<div class="card p-3">
    <h1>Team Bewerken</h1>

    <form action="{{ route('teams.update', $team) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Team Naam:</label>
            <input type="text" name="name" class="form-control" id="name" value="{{ $team->name }}" required>
        </div>
        <div class="form-group">
            <label for="division_id">Divisie:</label>
            <select name="division_id" class="form-control" id="division_id" required>
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}" {{ $team->division_id == $division->id ? 'selected' : '' }}>
                        {{ $division->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="location">Locatie:</label>
            <input type="text" name="location" class="form-control" id="location" value="{{ $team->location }}">
        </div>
        <button type="submit" class="btn btn-primary">Opslaan</button>
    </form>

    <h2 class="mt-4 p-4">Spelers in dit Team</h2>
    <form action="{{ route('players.assignToTeam', $team) }}" method="POST" class="mb-3">
        @csrf
        <div class="input-group">
            <select name="player_id" class="form-control">
                <option value="">Selecteer een speler om toe te voegen</option>
                @foreach ($allPlayers->groupBy('team.name') as $teamName => $playersGroup)
                    <optgroup label="{{ $teamName ?? 'Geen team' }}">
                        @foreach ($playersGroup as $player)
                            @if ($player->team_id !== $team->id)
                                <option value="{{ $player->id }}">
                                    {{ $player->first_name }} {{ $player->last_name }}
                                </option>
                            @endif
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            <button type="submit" class="btn btn-success"><i class="fas fa-plus-circle"></i> Speler Toevoegen</button>
        </div>
    </form>
    
    <table class="table">
        <thead>
            <tr>
                <th>Spelernaam</th>
                <th>Team</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($players as $player)
            <tr>
                <td>
                    <a href="{{ route('players.edit', $player) }}">{{ $player->first_name }} {{ $player->last_name }}</a>
                </td>
                <td>{{ $player->team->name ?? 'Geen team' }}</td>
                <td>
                    <a href="{{ route('players.edit', $player) }}" class="btn btn-info">Bewerk</a>
                    <form action="{{ route('players.removeFromTeam', ['team' => $team->id, 'player' => $player->id]) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Weet je zeker dat je deze speler uit het team wilt verwijderen?')">
                            Verwijder uit Team
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
