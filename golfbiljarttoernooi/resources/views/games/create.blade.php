@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Wedstrijd Aanmaken</h1>
    <form action="{{ route('games.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="season_id">Seizoen:</label>
            <select name="season_id" id="season_id" class="form-control">
                @foreach ($seasons as $season)
                    <option value="{{ $season->id }}">{{ $season->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="division_id">Divisie:</label>
            <select name="division_id" id="division_id" class="form-control">
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                @endforeach
            </select>
        </div>
    
        <div class="form-group">
            <label for="date">Datum:</label>
            <input type="date" class="form-control" name="date" required>
        </div>

        <div class="form-group">
            <label for="home_team_id">Thuis Team:</label>
            <select name="home_team_id" class="form-control">
                <option value="">Selecteer thuis team</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="away_team_id">Uit Team:</label>
            <select name="away_team_id" class="form-control">
                <option value="">Selecteer uit team</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="bye_team_id">Bye (geen spel voor):</label>
            <select name="bye_team_id" class="form-control">
                <option value="">Geen Bye</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Wedstrijd Aanmaken</button>
    </form>
</div>
@endsection
