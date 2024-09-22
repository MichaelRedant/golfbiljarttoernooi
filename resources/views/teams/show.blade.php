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

    /* Mobile-friendly styling */
    @media (max-width: 768px) {
        .table th, .table td {
            font-size: 12px;
            padding: 8px;
        }
        .table-responsive {
            overflow-x: auto;
        }
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
                <i class="fas fa-users mr-2"></i> Details {{ $team->name }}
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
                <p><strong><i class="fas fa-handshake"></i> Gelijk:</strong> {{ $currentTeamStanding['games_draw'] ?? 'N/A' }}</p>
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
                                    <th rowspan="2">#</th>
                                    <th rowspan="2">Naam</th>
                                    <th rowspan="2">Team</th>
                                    <th colspan="1">Wedstrijden</th>
                                    <th colspan="2">Matchen</th>
                                    <th colspan="2">Manches</th>
                                    <th rowspan="2">Punten</th>
                                </tr>
                                <tr>
                                    <th>Gespeeld</th>
                                    <th>Gewonnen</th>
                                    <th>Verloren</th>
                                    <th>Gewonnen</th>
                                    <th>Verloren</th>
                                </tr>
                            </thead>
                            <tbody id="player-ranking-body">
                                @foreach ($teamPlayerStats as $index => $player)
                                    <tr class="table-row-hover">
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td><a href="{{ route('players.show', $player['player_id']) }}">{{ $player['player_name'] }}</a></td>
                                        <td><a href="{{ route('teams.show', ['team' => $player['team_id']]) }}">{{ $player['team_name'] }}</a></td>
                                        <td class="text-center">{{ $player['matches_played'] ?? 0 }}</td>
                                        <td class="text-center">{{ $player['matches_won'] ?? 0 }}</td>
                                        <td class="text-center">{{ $player['matches_lost'] ?? 0 }}</td>
                                        <td class="text-center">{{ $player['manches_won'] ?? 0 }}</td>
                                        <td class="text-center">{{ $player['manches_lost'] ?? 0 }}</td>
                                        <td class="text-center">{{ $player['points'] ?? 0 }}</td>
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
        function sortPlayerTable() {
            const table = document.querySelector('#player-ranking-body');
            const rows = Array.from(table.querySelectorAll('tr'));

            rows.sort((a, b) => {
                const pointsA = parseInt(a.cells[8].innerText) || 0;
                const pointsB = parseInt(b.cells[8].innerText) || 0;

                if (pointsA !== pointsB) {
                    return pointsB - pointsA; // Sorteer op punten
                }

                const matchesWonA = parseInt(a.cells[4].innerText) || 0;
                const matchesWonB = parseInt(b.cells[4].innerText) || 0;

                if (matchesWonA !== matchesWonB) {
                    return matchesWonB - matchesWonA; // Sorteer op gewonnen matchen
                }

                const manchesWonA = parseInt(a.cells[6].innerText) || 0;
                const manchesWonB = parseInt(b.cells[6].innerText) || 0;

                if (manchesWonA !== manchesWonB) {
                    return manchesWonB - manchesWonA; // Sorteer op gewonnen manches
                }

                const matchesLostA = parseInt(a.cells[5].innerText) || 0;
                const matchesLostB = parseInt(b.cells[5].innerText) || 0;

                if (matchesLostA !== matchesLostB) {
                    return matchesLostA - matchesLostB; // Sorteer op verloren matchen (minder is beter)
                }

                const manchesLostA = parseInt(a.cells[7].innerText) || 0;
                const manchesLostB = parseInt(b.cells[7].innerText) || 0;

                return manchesLostA - manchesLostB; // Sorteer op verloren manches (minder is beter)
            });

            rows.forEach((row, index) => {
                row.cells[0].innerText = index + 1; // Update de nummering in de eerste kolom
                table.appendChild(row);
            });
        }

        sortPlayerTable();
    });
</script>
@endsection
