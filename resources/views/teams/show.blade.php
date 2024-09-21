@extends('layouts.app')

@section('content')
<style>
    .highlight-row {
        background-color: #28a745 !important; /* Bootstrap success color */
        color: white !important;
    }

    /* Hover effect for table rows */
    .table-row-hover:hover {
        background-color: #f0f8ff;
        transition: background-color 0.3s ease;
    }

    /* Center table text and style */
    .table th, .table td {
        vertical-align: middle;
        text-align: center;
    }

    .table-bordered td, .table-bordered th {
        border: 1px solid #dee2e6;
    }
</style>

<div class="container">
    @isset($error)
        <div class="alert alert-danger">
            {{ $error }}
        </div>
    @endisset

    <div class="card mb-4">
        <div class="card-header">
            <h1 class="d-flex align-items-center">
                <i class="fas fa-users mr-2"></i>Details {{ $team->name }}
            </h1>
        </div>
        <div class="card-body">
            @if(!$error)
                <p><strong><i class="fas fa-building"></i> Club:</strong> <a href="{{ route('clubs.show', $team->club->id) }}">{{ $team->club->name }}</a></p>
                <form action="{{ route('teams.show', $team) }}" method="GET">
                    <div class="form-group">
                        <label for="season_id"><i class="fas fa-calendar-alt"></i> Kies een seizoen:</label>
                        <select id="season_id" name="season_id" class="form-control" onchange="this.form.submit()">
                            @foreach ($seasons as $season)
                                <option value="{{ $season->id }}" {{ $season->id == $currentSeasonId ? 'selected' : '' }}>
                                    {{ $season->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="division_id"><i class="fas fa-layer-group"></i> Kies een reeks:</label>
                        <select id="division_id" name="division_id" class="form-control" onchange="this.form.submit()">
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}" {{ $division->id == $currentDivisionId ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
                <p><strong><i class="fas fa-trophy"></i> Gewonnen:</strong> {{ $currentTeamStanding['games_won'] ?? 'N/A' }}</p>
                <p><strong><i class="fas fa-thumbs-down"></i> Verloren:</strong> {{ $currentTeamStanding['games_lost'] ?? 'N/A' }}</p>
                <p><strong><i class="fas fa-handshake"></i > Gelijk:</strong> {{ $currentTeamStanding['games_draw'] ?? 'N/A' }}</p>
                <p><strong><i class="fas fa-star"></i> Totaal Punten:</strong> {{ $currentTeamStanding['points'] ?? 'N/A' }}</p>

            @endif
        </div>
        <div class="card-footer d-flex justify-content-between">
            @if(auth()->user() && auth()->user()->role === 'admin')
                <a href="{{ route('teams.edit', $team) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Bewerk {{ $team->name }}
                </a>
            @endif
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Terug
            </a>
        </div>
    </div>

    @if(!$error)
        <!-- Team Rankings -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-chart-line"></i> Ranking van {{ $team->name }}</h2>
            </div>
            <div class="card-body">
                @if($standings && count($standings) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th rowspan="2">#</th>
                                    <th rowspan="2">Team</th>
                                    <th colspan="4">Wedstrijden</th>
                                    <th colspan="2">Matchen</th>
                                    <th colspan="2">Manches</th>
                                    <th rowspan="2">Punten</th>
                                </tr>
                                <tr>
                                    <th>Gespeeld</th>
                                    <th>Gewonnen</th>
                                    <th>Verloren</th>
                                    <th>Gelijk</th>
                                    <th>Gewonnen</th>
                                    <th>Verloren</th>
                                    <th>Gewonnen</th>
                                    <th>Verloren</th>
                                </tr>
                            </thead>
                            <tbody id="ranking-body">
                                @foreach ($standings as $index => $standing)
                                    <tr class="table-row-hover @if($standing['team_id'] == $team->id) highlight-row @endif">
                                        <td>{{ $index + 1 }}</td>
                                        <td><a href="{{ route('teams.show', ['team' => $standing['team_id']]) }}">{{ $standing['team_name'] }}</a></td>
                                        <td>{{ $standing['games_played'] }}</td>
                                        <td>{{ $standing['games_won'] }}</td>
                                        <td>{{ $standing['games_lost'] }}</td>
                                        <td>{{ $standing['games_draw'] }}</td>
                                        <td>{{ $standing['matches_won'] }}</td>
                                        <td>{{ $standing['matches_lost'] }}</td>
                                        <td>{{ $standing['manches_won'] }}</td>
                                        <td>{{ $standing['manches_lost'] }}</td>
                                        <td>{{ $standing['points'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p>Geen rankings beschikbaar.</p>
                @endif
            </div>
        </div>

        <!-- Spelerslijst met Ranking -->
        <div class="card mt-4">
            <div class="card-header">
                <h2><i class="fas fa-users"></i> Spelers in {{ $team->name }}</h2>
            </div>
            <div class="card-body">
                @if($teamPlayerStats && count($teamPlayerStats) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th>#</th>
                                    <th>Naam</th>
                                    <th>Gespeelde Wedstrijden</th>
                                    <th>Gewonnen Matchen</th>
                                    <th>Verloren Matchen</th>
                                    <th>Gewonnen Manches</th>
                                    <th>Verloren Manches</th>
                                    <th>Punten</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($teamPlayerStats as $index => $player)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><a href="{{ route('players.show', $player['player_id']) }}">{{ $player['player_name'] }}</a></td>
                                        <td>{{ $player['matches_played'] ?? 0 }}</td>
                                        <td>{{ $player['matches_won'] ?? 0 }}</td>
                                        <td>{{ $player['matches_lost'] ?? 0 }}</td>
                                        <td>{{ $player['manches_won'] ?? 0 }}</td>
                                        <td>{{ $player['manches_lost'] ?? 0 }}</td>
                                        <td>{{ $player['points'] ?? 0 }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p>Geen spelers ranking beschikbaar.</p>
                @endif
            </div>
        </div>
    @endif
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    function sortTable() {
        const table = document.querySelector('#ranking-body');
        const rows = Array.from(table.querySelectorAll('tr'));

        rows.sort((a, b) => {
            const pointsA = parseInt(a.cells[10].innerText) || 0;
            const pointsB = parseInt(b.cells[10].innerText) || 0;

            if (pointsA !== pointsB) {
                return pointsB - pointsA; // Sorteren op punten
            }

            const gamesWonA = parseInt(a.cells[3].innerText) || 0;
            const gamesWonB = parseInt(b.cells[3].innerText) || 0;

            if (gamesWonA !== gamesWonB) {
                return gamesWonB - gamesWonA; // Sorteren op gewonnen wedstrijden
            }

            const matchesWonA = parseInt(a.cells[6].innerText) || 0;
            const matchesWonB = parseInt(b.cells[6].innerText) || 0;

            return matchesWonB - matchesWonA; // Sorteren op gewonnen matchen
        });

        rows.forEach((row, index) => {
            row.cells[0].innerText = index + 1; // Bijwerken van de rang in de eerste kolom
            table.appendChild(row);
        });
    }

    sortTable();
});


</script>
@endsection
