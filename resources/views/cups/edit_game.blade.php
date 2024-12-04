@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1>Wijzig wedstrijd voor Beker: {{ $cup->name }}</h1>

    <form action="{{ route('cups.games.update', ['cup' => $cup->id, 'game' => $game->id]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label for="round_id">Kies een ronde:</label>
            <select id="round_id" name="round_id" class="form-control" required>
                @foreach($rounds as $round)
                    <option value="{{ $round->id }}" {{ $round->id == $game->cup_round_id ? 'selected' : '' }}>
                        {{ $round->round_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="home_team_id">Thuis Team:</label>
            <select id="home_team_id" name="home_team_id" class="form-control" required>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}" {{ $team->id == $game->home_team_id ? 'selected' : '' }}>
                        {{ $team->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="away_team_id">Uit Team:</label>
            <select id="away_team_id" name="away_team_id" class="form-control" required>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}" {{ $team->id == $game->away_team_id ? 'selected' : '' }}>
                        {{ $team->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="date">Datum van de wedstrijd:</label>
            <input type="date" id="date" name="date" value="{{ $game->date ? $game->date->format('Y-m-d') : '' }}" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Wijzig Wedstrijd</button>
    </form>
</div>
@endsection
