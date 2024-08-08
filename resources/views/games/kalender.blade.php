@extends('layouts.app')

@section('content')
<div class="container mt-4">
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <h1>Kalender</h1>

    <!-- Dropdown for division and season selection -->
    <div class="mb-4">
        <form action="{{ route('games.kalender') }}" method="GET" id="selectionForm">
            <div class="form-group">
                <label for="division-select" class="form-label">Kies een reeks:</label>
                <select id="division-select" name="division_id" class="form-control" onchange="document.getElementById('selectionForm').submit()">
                    @foreach ($divisions as $division)
                        <option value="{{ $division->id }}" {{ $selectedDivisionId == $division->id ? 'selected' : '' }}>
                            {{ $division->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="season-select" class="form-label">Kies een seizoen:</label>
                <select id="season-select" name="season_id" class="form-control" onchange="document.getElementById('selectionForm').submit()">
                    @foreach ($seasons as $season)
                        <option value="{{ $season->id }}" {{ $selectedSeasonId == $season->id ? 'selected' : '' }}>
                            {{ $season->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if ($upcomingGames->isNotEmpty())
        <h2>
            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#upcomingGamesCollapse" aria-expanded="true" aria-controls="upcomingGamesCollapse">
                Aankomende Wedstrijden <i class="fas fa-chevron-down"></i>
            </button>
        </h2>
        <div class="collapse show" id="upcomingGamesCollapse">
            @foreach ($upcomingGames as $date => $games)
                <div class="card mb-4">
                    <div class="card-header">
                        {{ $date }}
                    </div>
                    <ul class="list-group list-group-flush">
                        @foreach ($games as $game)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        @if ($game->homeTeam)
                                            <a href="{{ route('teams.show', $game->homeTeam->id) }}">{{ $game->homeTeam->name }}</a>
                                        @else
                                            Bye - {{ $game->byeTeam ? $game->byeTeam->name : 'Geen Thuis Team' }}
                                        @endif
                                        vs
                                        @if ($game->awayTeam)
                                            <a href="{{ route('teams.show', $game->awayTeam->id) }}">{{ $game->awayTeam->name }}</a>
                                        @else
                                            Bye - {{ $game->byeTeam ? $game->byeTeam->name : 'Geen Uit Team' }}
                                        @endif
                                    </div>
                                    <div>
                                        {{ $game->homeTeam->location ?? 'Locatie onbekend' }}
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @else
        <p>Geen geplande wedstrijden gevonden voor het geselecteerde seizoen.</p>
    @endif

    <h2>
        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#pastGamesCollapse" aria-expanded="true" aria-controls="pastGamesCollapse">
            Voorbije Wedstrijden <i class="fas fa-chevron-down"></i>
        </button>
    </h2>
    <div class="collapse show" id="pastGamesCollapse">
        @if ($pastGames->isNotEmpty())
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Thuis Team</th>
                        <th>Uit Team</th>
                        <th>Uitslag</th>
                        <th>Locatie</th>
                        <th>Actie</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pastGames as $game)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($game->date)->format('d-m-Y') }}</td>
                            <td>
                                @if ($game->homeTeam)
                                    <a href="{{ route('teams.show', $game->homeTeam->id) }}">{{ $game->homeTeam->name }}</a>
                                @else
                                    Bye
                                @endif
                            </td>
                            <td>
                                @if ($game->awayTeam)
                                    <a href="{{ route('teams.show', $game->awayTeam->id) }}">{{ $game->awayTeam->name }}</a>
                                @else
                                    Bye
                                @endif
                            </td>
                            <td>{{ $game->home_score ?? '' }} : {{ $game->away_score ?? '' }}</td>
                            <td>{{ $game->homeTeam->location ?? 'Locatie onbekend' }}</td>
                            <td>
                                <a href="{{ route('games.show', $game->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Bekijk
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Geen voorbije wedstrijden dit seizoen.</p>
        @endif
    </div>
</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-toggle="collapse"]').forEach(function(element) {
            element.addEventListener('click', function() {
                const icon = this.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-chevron-down');
                    icon.classList.toggle('fa-chevron-up');
                }
            });
        });
    });
</script>
