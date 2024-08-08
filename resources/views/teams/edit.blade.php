@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Team Bewerken</h3>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('teams.update', $team) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name" class="form-label">Team Naam:</label>
                    <input type="text" name="name" class="form-control" id="name" value="{{ $team->name }}" required>
                </div>
                <div class="mb-3">
                    <label for="location" class="form-label">Locatie:</label>
                    <input type="text" name="location" class="form-control" id="location" value="{{ $team->location }}">
                </div>
                <div class="mb-3">
                    <label for="division_ids" class="form-label">Reeksen:</label>
                    <div id="division_ids">
                        @foreach ($divisions as $division)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="division_{{ $division->id }}" name="division_ids[]" value="{{ $division->id }}"
                                    {{ $team->divisions->contains($division->id) ? 'checked' : '' }}>
                                <label class="form-check-label" for="division_{{ $division->id }}">{{ $division->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Opslaan</button>
            </form>

            <h2 class="mt-4">Spelers in dit Team</h2>
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
                            @endforeach
                        </optgroup>
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
                            <form action="{{ route('players.destroy', $player->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Weet je zeker dat je deze speler volledig wilt verwijderen?')">
                                    Verwijder Speler
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .form-check {
        margin-bottom: 10px;
    }
</style>
@endsection
