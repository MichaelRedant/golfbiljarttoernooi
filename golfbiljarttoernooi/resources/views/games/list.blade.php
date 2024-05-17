@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Wedstrijden voor {{ $division->name }} - {{ $season->name }}</h1>

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
        <a href="{{ route('games.create', ['division_id' => $division->id, 'season_id' => $season->id]) }}" class="btn btn-success">
            <i class="fas fa-plus-circle"></i> Nieuwe Wedstrijd Toevoegen
        </a>
    </div>

    @if($games->isEmpty())
        <p>Geen geplande wedstrijden gevonden voor het geselecteerde seizoen.</p>
    @else
        <table class="table table-striped">
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
                    <td>
                        @if($game->homeTeam)
                            {{ $game->homeTeam->name }}
                        @elseif($game->bye_team_id)
                            Bye - {{ $game->byeTeam->name }}
                        @else
                            No Home Team
                        @endif
                    </td>
                    <td>
                        @if($game->awayTeam)
                            {{ $game->awayTeam->name }}
                        @elseif($game->bye_team_id)
                            <!-- No output needed for away team in case of bye -->
                        @else
                            No Away Team
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('games.edit', $game->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Bewerk wedstrijdkalender
                        </a>
                        @if(!$game->played)
                            <a href="{{ route('games.form', $game->id) }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-play-circle"></i> Wedstrijd Spelen
                            </a>
                        @else
                            <a href="{{ route('games.edit', $game->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-pencil-alt"></i> Wedstrijd Bewerken
                            </a>
                        @endif
                        <form action="{{ route('games.destroy', $game->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Weet je zeker dat je deze wedstrijd wilt verwijderen?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash-alt"></i> Verwijder
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
