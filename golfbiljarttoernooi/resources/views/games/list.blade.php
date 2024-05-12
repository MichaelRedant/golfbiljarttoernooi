@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Wedstrijden voor {{ $division->name }} - Seizoen {{ $season->name }}</h1>

    <!-- Dropdown for season selection -->
    <div class="mb-3">
        <label for="season-select" class="form-label">Kies een Seizoen:</label>
        <select id="season-select" class="form-control" onchange="location = this.value;">
            @foreach ($seasons as $seasonOption)
                <option value="{{ route('games.for-division-season', ['division_id' => $division->id, 'season_id' => $seasonOption->id]) }}" {{ $season->id == $seasonOption->id ? 'selected' : '' }}>
                    {{ $seasonOption->name }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Button to create a new game -->
    <div class="mb-4">
        <a href="{{ route('games.create', ['division_id' => $division->id, 'season_id' => $season->id]) }}" class="btn btn-success">Nieuwe Wedstrijd Toevoegen</a>
    </div>

    @if($games->isEmpty())
        <p>Geen geplande wedstrijden gevonden voor het geselecteerde seizoen.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Thuis Team</th>
                    <th>Uit Team</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                @foreach($games as $game)
                    <tr>
                        <td>{{ $game->date->format('d-m-Y') }}</td>
                        <td>{{ $game->homeTeam->name }}</td>
                        <td>{{ $game->awayTeam->name }}</td>
                        <td>
                            <a href="{{ route('games.edit', $game) }}" class="btn btn-primary">Bewerken</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
