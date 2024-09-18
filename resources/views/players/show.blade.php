@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h1 class="h4 mb-0">{{ $player->first_name }} {{ $player->last_name }}</h1>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <strong><i class="fas fa-users"></i> Team:</strong> 
                <a href="{{ route('teams.show', $player->team_id) }}">{{ $player->team->name }}</a>
            </div>
            <form action="{{ route('players.show', $player->id) }}" method="GET">
                <div class="form-group">
                    <label for="season_id"><i class="fas fa-calendar-alt"></i> Seizoen:</label>
                    <select name="season_id" id="season_id" class="form-control" onchange="this.form.submit()">
                        @foreach($seasons as $season)
                            <option value="{{ $season->id }}" {{ $season->id == $currentSeasonId ? 'selected' : '' }}>
                                {{ $season->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            @php
                $playerStanding = null;
                foreach ($standings as $standing) {
                    if ($standing['player_id'] == $player->id) {
                        $playerStanding = $standing;
                        break;
                    }
                }
            @endphp

            @if($playerStanding)
                <div>
                    <p><strong><i class="fas fa-trophy"></i> Gewonnen Wedstrijden:</strong> {{ $playerStanding['matches_won'] }}</p>
                    <p><strong><i class="fas fa-thumbs-down"></i> Verloren Wedstrijden:</strong> {{ $playerStanding['matches_lost'] }}</p>
                    <p><strong><i class="fas fa-medal"></i> Gewonnen Manches:</strong> {{ $playerStanding['manches_won'] }}</p>
                    <p><strong><i class="fas fa-hand-paper"></i> Verloren Manches:</strong> {{ $playerStanding['manches_lost'] }}</p>
                    <p><strong><i class="fas fa-list-ol"></i> Plaats dit seizoen:</strong> {{ $playerRank }}</p>
                    <p><strong><i class="fas fa-star"></i> Punten:</strong> {{ $playerStanding['points'] }}</p>
                </div>
            @else
                <p>Geen gegevens beschikbaar voor deze speler in dit seizoen.</p>
            @endif

            @if(auth()->user() && (auth()->user()->role === 'admin' || auth()->user()->role === 'speler'))
                <a href="{{ route('players.edit', $player->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Bewerk Speler
                </a>
            @endif
            <a href="{{ url()->previous() }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Terug</a>
        </div>
    </div>

    <!-- Tabbladen voor de verschillende divisies -->
    <ul class="nav nav-tabs mt-4" id="divisionTabs" role="tablist">
        @foreach($divisions as $division)
            <li class="nav-item">
                <a class="nav-link {{ $division->id == $currentDivisionId ? 'active' : '' }}" 
                   href="{{ route('players.show', ['player' => $player->id, 'division_id' => $division->id, 'season_id' => $currentSeasonId]) }}">
                    {{ $division->name }}
                </a>
            </li>
        @endforeach
    </ul>

    <!-- Spelers Ranking sectie -->
    <div class="card mt-4">
        <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> Ranking van {{ $player->first_name }} {{ $player->last_name }}</h3>
        </div>
        <div class="card-body">
            <div id="rankingContent">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Plaats Dit Seizoen</th>
                            <th>Naam</th>
                            <th>Team</th>
                            <th>Gsp.</th>
                            <th>G.</th>
                            <th>V.</th>
                            <th>MG.</th>
                            <th>MV.</th>
                            <th>Pt.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($standings as $index => $standing)
                            <tr class="{{ $standing['player_id'] == $player->id ? 'table-success' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td><a href="{{ route('players.show', $standing['player_id']) }}">{{ $standing['player_name'] }}</a></td>
                                <td><a href="{{ route('teams.show', $standing['team_id']) }}">{{ $standing['team_name'] }}</a></td>
                                <td>{{ $standing['matches_played'] }}</td>
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
        </div>
    </div>
</div>

<!-- Verborgen formulier voor tab wisseling -->
<form id="divisionForm" method="GET" action="{{ route('players.show', $player->id) }}">
    <input type="hidden" name="season_id" value="{{ $currentSeasonId }}">
    <input type="hidden" name="division_id" id="divisionInput" value="{{ $currentDivisionId }}">
</form>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.nav-link').forEach(function (tab) {
            tab.addEventListener('click', function (e) {
                e.preventDefault();
                document.getElementById('divisionInput').value = tab.getAttribute('href').split('division_id=')[1];
                document.getElementById('divisionForm').submit();
            });
        });
    });
</script>
@endsection
