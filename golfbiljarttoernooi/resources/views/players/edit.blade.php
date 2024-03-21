@extends('layouts.app')

@section('content')
    <h1>Bewerk Speler</h1>

    <form action="{{ route('players.update', $player) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="first_name">Voornaam:</label>
            <input type="text" name="first_name" class="form-control" id="first_name" value="{{ old('first_name', $player->first_name) }}" required>
        </div>
        <div class="form-group">
            <label for="last_name">Achternaam:</label>
            <input type="text" name="last_name" class="form-control" id="last_name" value="{{ old('last_name', $player->last_name) }}" required>
        </div>
        <div class="form-group">
            <label for="team_id">Team:</label>
            <select name="team_id" class="form-control" id="team_id">
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}" {{ (old('team_id', $player->team_id) == $team->id) ? 'selected' : '' }}>{{ $team->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="division_id">Divisie:</label>
            <select name="division_id" class="form-control" id="division_id">
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}" {{ (old('division_id', $player->division_id) == $division->id) ? 'selected' : '' }}>{{ $division->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="photo">Foto:</label>
            <input type="file" name="photo" class="form-control" id="photo">
            @if($player->photo)
                <img src="{{ asset('storage/photos/' . $player->photo) }}" width="100" alt="Speler foto">
            @endif
        </div>
        <button type="submit" class="btn btn-primary">Opslaan</button>
    </form>
@endsection
