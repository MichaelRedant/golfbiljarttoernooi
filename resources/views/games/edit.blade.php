@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Wedstrijdformulier voor {{ $game->homeTeam->name }} vs {{ $game->awayTeam->name }}</h1>

    <div class="card my-4">
        <div class="card-body">
            <h5 class="card-title">Wedstrijdinformatie</h5>
            <p><strong>Thuisploeg:</strong> <a href="{{ route('teams.show', $game->homeTeam->id) }}">{{ $game->homeTeam->name }}</a></p>
            <p><strong>Bezoekers:</strong> <a href="{{ route('teams.show', $game->awayTeam->id) }}">{{ $game->awayTeam->name }}</a></p>
            <p><strong>Locatie:</strong> {{ $game->homeTeam->location }}</p>
            <p><strong>Datum:</strong> {{ $game->date->format('d-m-Y') }}</p>
        </div>
    </div>

    <form id="matchForm" action="{{ route('games.update', $game->id) }}" method="POST">
        @csrf
        @method('PUT')

        <input type="hidden" name="home_team_id" value="{{ $game->homeTeam->id }}">
        <input type="hidden" name="away_team_id" value="{{ $game->awayTeam->id }}">
        <input type="hidden" name="date" value="{{ $game->date->format('d-m-Y') }}">
        <input type="hidden" name="division_id" value="{{ $game->division_id }}">
        <input type="hidden" name="season_id" value="{{ $game->season_id }}">

        <div class="form-group">
            <label for="forfeit_team">Forfait:</label>
            <select class="form-control" id="forfeit_team" name="forfeit_team">
                <option value="">Selecteer team</option>
                <option value="home" {{ $game->forfeit_by == 'home' ? 'selected' : '' }}>{{ $game->homeTeam->name }}</option>
                <option value="away" {{ $game->forfeit_by == 'away' ? 'selected' : '' }}>{{ $game->awayTeam->name }}</option>
            </select>
        </div>

        <div class="card">
            <div class="card-header">Wedstrijdscore</div>
            <div class="card-body">
                <div class="form-group">
                    <label>Thuis score:</label>
                    <input type="text" class="form-control" id="home_score" name="home_score" value="{{ $game->home_score }}" readonly>
                </div>
                <div class="form-group">
                    <label>Uit score:</label>
                    <input type="text" class="form-control" id="away_score" name="away_score" value="{{ $game->away_score }}" readonly>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Kapiteins en reservespelers</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label>Kapitein {{ $game->homeTeam->name }}</label>
                        <select class="form-control player-select" name="home_captain">
                            <option value="">Kies Kapitein</option>
                            @foreach ($homeTeamPlayers as $player)
                                <option value="{{ $player->id }}" {{ $player->id == $game->home_captain ? 'selected' : '' }}>
                                    {{ $player->first_name }} {{ $player->last_name }} - {{ $player->team->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Kapitein {{ $game->awayTeam->name }}</label>
                        <select class="form-control player-select" name="away_captain">
                            <option value="">Kies Kapitein</option>
                            @foreach ($awayTeamPlayers as $player)
                                <option value="{{ $player->id }}" {{ $player->id == $game->away_captain ? 'selected' : '' }}>
                                    {{ $player->first_name }} {{ $player->last_name }} - {{ $player->team->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Spelers en Scores</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ $game->homeTeam->name }}</th>
                                <th>Team</th>
                                <th>{{ $game->awayTeam->name }}</th>
                                <th>Team</th>
                                <th>1M</th>
                                <th>2M</th>
                                <th>Belle</th>
                                <th>Uitslag</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($game->manches as $index => $manche)
                            <tr>
                                <td>
                                    <select class="form-control player-select wide-select" data-player-type="home" data-row-index="{{ $index }}" name="scores[{{ $index }}][home_player]">
                                        <option value="">-- Selecteer een teamlid --</option>
                                        @foreach ($homeTeamPlayers as $player)
                                        <option value="{{ $player->id }}" {{ $player->id == $manche->player1_id ? 'selected' : '' }}>
                                            {{ $player->first_name }} {{ $player->last_name }} - {{ $player->team->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="small" id="home-team-{{ $index }}">{{ $manche->player1->team->name }}</td>
                                <td>
                                    <select class="form-control player-select wide-select" data-player-type="away" data-row-index="{{ $index }}" name="scores[{{ $index }}][away_player]">
                                        <option value="">-- Selecteer een teamlid --</option>
                                        @foreach ($awayTeamPlayers as $player)
                                        <option value="{{ $player->id }}" {{ $player->id == $manche->player2_id ? 'selected' : '' }}>
                                            {{ $player->first_name }} {{ $player->last_name }} - {{ $player->team->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="small" id="away-team-{{ $index }}">{{ $manche->player2->team->name }}</td>
                                <td>
                                    <input type="number" class="form-control manche" name="scores[{{ $index }}][1M]" value="{{ $manche->score1 }}" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control manche" name="scores[{{ $index }}][2M]" value="{{ $manche->score2 }}" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control belle" name="scores[{{ $index }}][Belle]" value="{{ $manche->belle_score }}" {{ $manche->score1 != $manche->score2 ? 'readonly' : '' }}>
                                </td>
                                <td>
                                    <input type="text" class="form-control result" value="{{ $manche->score1 + $manche->belle_score > $manche->score2 ? 1 : ($manche->score2 > $manche->score1 + $manche->belle_score ? 2 : 'Draw') }}" readonly>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="text-center mt-4 mb-4">
            <button type="submit" class="btn btn-primary" id="saveButton">Wedstrijd bijwerken</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('tbody tr');
    const homeScoreInput = document.getElementById('home_score');
    const awayScoreInput = document.getElementById('away_score');
    const forfeitTeamSelect = document.getElementById('forfeit_team');

    forfeitTeamSelect.addEventListener('change', function() {
        if (this.value === 'home') {
            homeScoreInput.value = 0;
            awayScoreInput.value = 3;
        } else if (this.value === 'away') {
            homeScoreInput.value = 3;
            awayScoreInput.value = 0;
        }
    });

    function updateResults() {
        let homeWins = 0;
        let awayWins = 0;

        rows.forEach(row => {
            const manche1Input = row.querySelector('input[name*="[1M]"]');
            const manche2Input = row.querySelector('input[name*="[2M]"]');
            const belleInput = row.querySelector('input[name*="[Belle]"]');
            const resultInput = row.querySelector('input.result');

            let homePoints = 0;
            let awayPoints = 0;

            if (parseInt(manche1Input.value) === 1) homePoints++;
            if (parseInt(manche2Input.value) === 2) awayPoints++;
            if (parseInt(manche1Input.value) === 2) awayPoints++;
            if (parseInt(manche2Input.value) === 1) homePoints++;

            if (homePoints === awayPoints) {
                belleInput.removeAttribute('readonly');
            } else {
                belleInput.setAttribute('readonly', true);
                belleInput.value = ""; // Reset belle input if not a draw
            }

            if (belleInput.value === "1") homePoints++;
            if (belleInput.value === "2") awayPoints++;

            resultInput.value = `${homePoints} - ${awayPoints}`;

            if (homePoints > awayPoints) homeWins++;
            if (awayPoints > homePoints) awayWins++;
        });

        homeScoreInput.value = homeWins;
        awayScoreInput.value = awayWins;
    }

    rows.forEach(row => {
        const inputs = row.querySelectorAll('.manche, .belle');
        inputs.forEach(input => {
            input.addEventListener('input', updateResults);
        });
    });

    document.querySelectorAll('.player-select').forEach(select => {
        select.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const teamName = selectedOption.text.split(' - ')[1] || 'Geen team gevonden';
            const playerType = this.dataset.playerType;
            const rowIndex = this.dataset.rowIndex;
            document.getElementById(`${playerType}-team-${rowIndex}`).textContent = teamName;
            updateResults();
        });
    });

    updateResults();
});
</script>
@endsection
