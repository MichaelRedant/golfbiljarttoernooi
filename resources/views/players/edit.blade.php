@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-3"><i class="fas fa-user-edit"></i> Bewerk {{ $player->first_name }} {{ $player->last_name }}</h1>

    <form action="{{ route('players.update', $player) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label for="first_name"><i class="fas fa-user"></i> Voornaam:</label>
            <input type="text" name="first_name" class="form-control" id="first_name" value="{{ old('first_name', $player->first_name) }}" required>
        </div>

        <div class="form-group mb-3">
            <label for="last_name"><i class="fas fa-user"></i> Achternaam:</label>
            <input type="text" name="last_name" class="form-control" id="last_name" value="{{ old('last_name', $player->last_name) }}" required>
        </div>

        <div class="form-group mb-3">
            <label for="team_id"><i class="fas fa-users"></i> Team:</label>
            <select name="team_id" class="form-control" id="team_id">
                <option value="">Selecteer een team (optioneel)</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}" {{ (old('team_id', $player->team_id) == $team->id) ? 'selected' : '' }}>{{ $team->name }}</option>
                @endforeach
            </select>
        </div>


        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Opslaan</button>
            <a href="{{ route('players.show', $player) }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Terug naar Speler</a>
        </div>
    </form>
</div>
@endsection
