@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4 text-center"><i class="fas fa-trophy"></i> Speler Klassement</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('rankings.players', ['division' => $division->id]) }}" method="GET" class="mb-4">
                <div class="form-group">
                    <label for="season_id"><i class="fas fa-calendar-alt"></i> Kies een seizoen:</label>
                    <select id="season_id" name="season_id" class="form-control" onchange="this.form.submit()">
                        @foreach ($seasons as $season)
                            <option value="{{ $season->id }}" {{ $season->id == $seasonId ? 'selected' : '' }}>
                                {{ $season->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            @if (empty($standings))
                <div class="alert alert-warning" role="alert">
                    Geen klassement beschikbaar voor de geselecteerde reeks en seizoen.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="thead-dark text-center">
                            <tr>
                                <th rowspan="2">#</th>
                                <th rowspan="2">Speler</th>
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
                        <tbody id="ranking-body">
                            @foreach ($standings as $index => $standing)
                                <tr class="table-row-hover">
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td><a href="{{ route('players.show', ['player' => $standing['player_id']]) }}">{{ $standing['player_name'] }}</a></td>
                                    <td><a href="{{ route('teams.show', ['team' => $standing['team_id']]) }}">{{ $standing['team_name'] }}</a></td>
                                    <td class="text-center">{{ $standing['matches_played'] }}</td>     
                                    <td class="text-center">{{ $standing['matches_won'] }}</td>
                                    <td class="text-center">{{ $standing['matches_lost'] }}</td>
                                    <td class="text-center">{{ $standing['manches_won'] }}</td>
                                    <td class="text-center">{{ $standing['manches_lost'] }}</td>
                                    <td class="text-center">{{ $standing['points'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('rankings.index') }}" class="btn btn-secondary btn-lg w-100">
            <i class="fas fa-arrow-left"></i> Terug naar Overzicht
        </a>
    </div>
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

                const matchesWonA = parseInt(a.cells[5].innerText) || 0;
                const matchesWonB = parseInt(b.cells[5].innerText) || 0;

                if (matchesWonA !== matchesWonB) {
                    return matchesWonB - matchesWonA; // Sorteren op gewonnen wedstrijden
                }

                const manchesWonA = parseInt(a.cells[7].innerText) || 0;
                const manchesWonB = parseInt(b.cells[7].innerText) || 0;

                return manchesWonB - manchesWonA; // Sorteren op gewonnen manches
            });

            // Plaats gesorteerde rijen opnieuw in de tabel
            rows.forEach((row, index) => {
                row.cells[0].innerText = index + 1; // Correcte nummering
                table.appendChild(row);
            });
        }

        sortTable();
    });
</script>
@endsection

@section('styles')
<style>
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
@endsection
