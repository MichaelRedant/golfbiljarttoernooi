@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4 text-center"><i class="fas fa-trophy"></i> Team Klassement</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('rankings.teams', ['division' => $division->id]) }}" method="GET" class="mb-4">
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
                            <tr class="table-row-hover">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td><a href="{{ route('teams.show', ['team' => $standing['team_id']]) }}">{{ $standing['team_name'] }}</a></td>
                                <td class="text-center">{{ $standing['games_played'] }}</td>
                                <td class="text-center">{{ $standing['games_won'] }}</td>
                                <td class="text-center">{{ $standing['games_lost'] }}</td>
                                <td class="text-center">{{ $standing['games_draw'] }}</td>
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

                const gamesWonA = parseInt(a.cells[3].innerText) || 0;
                const gamesWonB = parseInt(b.cells[3].innerText) || 0;

                if (gamesWonA !== gamesWonB) {
                    return gamesWonB - gamesWonA; // Sorteren op gewonnen wedstrijden
                }

                const matchesWonA = parseInt(a.cells[6].innerText) || 0;
                const matchesWonB = parseInt(b.cells[6].innerText) || 0;

                return matchesWonB - matchesWonA; // Sorteren op gewonnen matchen
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
</style>
@endsection
