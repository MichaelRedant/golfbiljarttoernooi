<!-- resources/views/games/for-division-season.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1>Wedstrijden voor Divisie: {{ $division->name }} - Seizoen: {{ $season->name }}</h1>

    <!-- Dropdown for season selection -->
    <div class="mb-4">
        <label for="season-select" class="form-label">Kies een seizoen:</label>
        <select id="season-select" class="form-select" onchange="location = this.value;">
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

    @if ($games->isNotEmpty())
        @php
            $matchDays = $games->keys();
        @endphp
        @foreach ($matchDays as $matchDay)
            <div class="card mb-4">
                <div class="card-header">
                    {{ \Carbon\Carbon::parse($matchDay)->format('d-m-Y') }}
                </div>
                <ul class="list-group list-group-flush">
                    @foreach ($games[$matchDay] as $game)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    @if ($game->homeTeam)
                                        <a href="{{ route('teams.show', $game->homeTeam->id) }}">{{ $game->homeTeam->name }}</a>
                                    @elseif ($game->bye_team_id)
                                        Bye - {{ $game->byeTeam->name }}
                                    @else
                                        No Home Team
                                    @endif
                                    vs
                                    @if ($game->awayTeam)
                                        <a href="{{ route('teams.show', $game->awayTeam->id) }}">{{ $game->awayTeam->name }}</a>
                                    @elseif ($game->bye_team_id)
                                        <!-- No output needed for away team in case of bye -->
                                    @else
                                        No Away Team
                                    @endif
                                </div>
                                <div>
                                    @if (!$game->played)
                                        <a href="{{ route('games.form', $game->id) }}" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-play-circle"></i> Wedstrijd Spelen
                                        </a>
                                    @else
                                        <a href="{{ route('games.edit', $game->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-pencil-alt"></i> Wedstrijd Bewerken
                                        </a>
                                    @endif
                                    <a href="{{ route('games.show', $game->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Bekijk
                                    </a>
                                    <form action="{{ route('games.destroy', $game->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Weet je zeker dat je deze wedstrijd wilt verwijderen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash-alt"></i> Verwijder
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    @else
        <p>Geen geplande wedstrijden gevonden voor het geselecteerde seizoen.</p>
    @endif

    <h2>Voorbije Wedstrijden</h2>
    @if ($pastGames->isNotEmpty())
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Thuis Team</th>
                    <th>Uit Team</th>
                    <th>Uitslag</th>
                    <th>Actie</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pastGames as $game)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($game->date)->format('d-m-Y') }}</td>
                        <td><a href="{{ route('teams.show', $game->homeTeam->id) }}">{{ $game->homeTeam ? $game->homeTeam->name : 'Bye' }}</a></td>
                        <td><a href="{{ route('teams.show', $game->awayTeam->id) }}">{{ $game->awayTeam ? $game->awayTeam->name : 'Bye' }}</a></td>
                        <td>{{ $game->home_score ?? '' }} : {{ $game->away_score ?? '' }}</td>
                        <td><a href="{{ route('games.show', $game->id) }}" class="btn btn-primary"><i class="fas fa-eye"></i> Wedstrijd bekijken</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Geen voorbije wedstrijden dit seizoen.</p>
    @endif
</div>
@endsection
