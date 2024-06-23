@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Bewerk Wedstrijd</h1>
    <form action="{{ route('games.update', $game->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <!-- Datum Input -->
        <div class="mb-3">
            <label for="date" class="form-label">Datum:</label>
            <input type="date" class="form-control" id="date" name="date" value="{{ $game->date->toDateString() }}" required>
        </div>
        
        <!-- Scores -->
        <div class="mb-3">
            <label for="home_score" class="form-label">Thuis Score:</label>
            <input type="number" class="form-control" id="home_score" name="home_score" value="{{ $game->home_score }}">
        </div>
        <div class="mb-3">
            <label for="away_score" class="form-label">Uit Score:</label>
            <input type="number" class="form-control" id="away_score" name="away_score" value="{{ $game->away_score }}">
        </div>
        
        <!-- Bye Checkbox -->
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_bye" name="is_bye" {{ $game->bye_team_id ? 'checked' : '' }}>
            <label class="form-check-label" for="is_bye">Is dit een bye?</label>
        </div>
        
        <!-- Bye Team Select -->
        <div class="mb-3" id="bye_team_select" style="{{ $game->bye_team_id ? '' : 'display: none;' }}">
            <label for="bye_team_id" class="form-label">Bye Team:</label>
            <select id="bye_team_id" name="bye_team_id" class="form-control">
                <option value="">Selecteer een team voor bye</option>
                @foreach ($teams as $team)
                <option value="{{ $team->id }}" {{ $team->id == $game->bye_team_id ? 'selected' : '' }}>{{ $team->name }}</option>
                @endforeach
            </select>
        </div>
        
        <!-- Teams Select -->
        <div class="mb-3" id="team_selects" style="{{ $game->bye_team_id ? 'display: none;' : '' }}">
            <label for="home_team_id" class="form-label">Thuis Team:</label>
            <select id="home_team_id" name="home_team_id" class="form-control">
                <option value="">Selecteer een team</option>
                @foreach ($teams as $team)
                <option value="{{ $team->id }}" {{ $team->id == $game->home_team_id ? 'selected' : '' }}>{{ $team->name }}</option>
                @endforeach
            </select>
            <label for="away_team_id" class="form-label">Uit Team:</label>
            <select id="away_team_id" name="away_team_id" class="form-control">
                <option value="">Selecteer een team</option>
                @foreach ($teams as $team)
                <option value="{{ $team->id }}" {{ $team->id == $game->away_team_id ? 'selected' : '' }}>{{ $team->name }}</option>
                @endforeach
            </select>
        </div>
        
        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Opslaan</button>
        <a href="{{ route('games.for-division-season', ['division_id' => $game->division_id, 'season_id' => $game->season_id]) }}" class="btn btn-secondary">Terug</a>
    </form>
</div>

<script>
document.getElementById('is_bye').onchange = function() {
    document.getElementById('bye_team_select').style.display = this.checked ? '' : 'none';
    document.getElementById('team_selects').style.display = this.checked ? 'none' : '';
};
</script>
@endsection
