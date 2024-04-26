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
        <button type="submit" class="btn btn-primary">Opslaan</button>
        <h2 class="mt-4 p-4">Spelers in dit Team</h2>
<table class="table">
    <thead>
        <tr>
            <th>Spelernaam</th>
            <th>Acties</th>
            <th>Verplaats naar ander Team</th> <!-- Extra kolom voor het verplaatsingsformulier -->
        </tr>
    </thead>
    <tbody>
        @foreach ($players as $player)
        <tr>
            <td>
                 <a href="{{ route('players.edit', $player) }}">{{ $player->first_name }} {{ $player->last_name }}</a>  
            </td>
          
            <td>
                <a href="{{ route('players.edit', $player) }}" class="btn btn-info">Bewerk</a>
                <a href="{{ route('players.remove', ['team' => $team->id, 'player' => $player->id]) }}" class="btn btn-warning">Verwijder uit team</a>
            </td>
            <td>
                <form action="{{ route('players.moveToTeam', $player) }}" method="POST">
                    @csrf
                    <select name="new_team_id" class="form-control">
                        @foreach ($allTeams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-success btn-sm mt-1">Verplaats</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

        
    </form>
</div>
    
@endsection
 