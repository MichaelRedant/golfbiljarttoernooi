@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center">Wedstrijdformulier voor {{ $game->homeTeam->name }} vs {{ $game->awayTeam->name }}</h1>

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
        <input type="hidden" name="date" value="{{ $game->date->format('Y-m-d') }}">
        <input type="hidden" name="division_id" value="{{ $game->division_id }}">
        <input type="hidden" name="season_id" value="{{ $game->season_id }}">

        <div class="form-group">
            <label for="forfeit_team">Dit team geeft forfait:</label>
            <select class="form-control" id="forfeit_team" name="forfeit_team">
                <option value="">Selecteer team</option>
                <option value="home" {{ $game->forfeit_by == 'home' ? 'selected' : '' }}>{{ $game->homeTeam->name }}</option>
                <option value="away" {{ $game->forfeit_by == 'away' ? 'selected' : '' }}>{{ $game->awayTeam->name }}</option>
            </select>
        </div>

        <div class="card">
            <div class="card-header">Kapiteins en reservespelers</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-6 mb-2">
                        <label>Kapitein {{ $game->homeTeam->name }}</label>
                        <select class="form-control player-select" name="home_captain">
                            <option value="">Speler</option>
                            @foreach ($homeTeamPlayers as $player)
                                <option value="{{ $player->id }}" {{ $player->id == $game->home_captain ? 'selected' : '' }}>
                                    {{ $player->first_name }} {{ $player->last_name }} - {{ $player->team->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 mb-2">
                        <label>Kapitein {{ $game->awayTeam->name }}</label>
                        <select class="form-control player-select" name="away_captain">
                            <option value="">Speler</option>
                            @foreach ($awayTeamPlayers as $player)
                                <option value="{{ $player->id }}" {{ $player->id == $game->away_captain ? 'selected' : '' }}>
                                    {{ $player->first_name }} {{ $player->last_name }} - {{ $player->team->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 mb-2">
                        <label>Reservespeler {{ $game->homeTeam->name }}</label>
                        <select class="form-control player-select" name="home_reserve">
                            <option value="">Speler</option>
                            @foreach ($homeTeamPlayers as $player)
                                <option value="{{ $player->id }}" {{ $player->id == $game->home_reserve ? 'selected' : '' }}>
                                    {{ $player->first_name }} {{ $player->last_name }} - {{ $player->team->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 mb-2">
                        <label>Reservespeler {{ $game->awayTeam->name }}</label>
                        <select class="form-control player-select" name="away_reserve">
                            <option value="">Speler</option>
                            @foreach ($awayTeamPlayers as $player)
                                <option value="{{ $player->id }}" {{ $player->id == $game->away_reserve ? 'selected' : '' }}>
                                    {{ $player->first_name }} {{ $player->last_name }} - {{ $player->team->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div id="scoreSection">
            <div class="card">
                <div class="card-header">Wedstrijdscore</div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Thuisploeg {{ $game->homeTeam->name }}:</label>
                        <input type="number" class="form-control" id="home_score" name="home_score" value="{{ $game->home_score }}">
                    </div>
                    <div class="form-group">
                        <label>Uit ploeg {{ $game->awayTeam->name }}:</label>
                        <input type="number" class="form-control" id="away_score" name="away_score" value="{{ $game->away_score }}">
                    </div>
                </div>
            </div>

            @php
                $sortedHomeTeamPlayers = $homeTeamPlayers->sortBy(function($player) {
                    return $player->team->name . ' ' . $player->first_name . ' ' . $player->last_name;
                });

                $sortedAwayTeamPlayers = $awayTeamPlayers->sortBy(function($player) {
                    return $player->team->name . ' ' . $player->first_name . ' ' . $player->last_name;
                });
            @endphp

            <div class="card">
                <div class="card-header">Spelers en Scores</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="responsive-small-text">
                                    <th>{{ $game->homeTeam->name }}</th>
                                    <th>Team</th>
                                    <th>{{ $game->awayTeam->name }}</th>
                                    <th>Team</th>
                                    <th>1M</th>
                                    <th>2M</th>
                                    <th>Belle</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i = 0; $i < 6; $i++)
                                    @php
                                        $manche = $game->manches->get($i) ?? null;
                                        $homePlayerId = $manche ? $manche->player1_id : '';
                                        $awayPlayerId = $manche ? $manche->player2_id : '';
                                        $score1 = $manche ? $manche->score1 : '';
                                        $score2 = $manche ? $manche->score2 : '';
                                        $belleScore = $manche ? $manche->belle_score : '';
                                    @endphp
                                    <tr>
                                        <td data-label="{{ $game->homeTeam->name }}">
                                            <select class="form-control player-select wide-select" data-player-type="home" data-row-index="{{ $i }}" name="scores[{{ $i }}][home_player]">
                                                <option value="">Selecteer speler</option>
                                                @foreach ($sortedHomeTeamPlayers as $player)
                                                    <option value="{{ $player->id }}" {{ $player->id == $homePlayerId ? 'selected' : '' }}>
                                                        {{ $player->first_name }} {{ $player->last_name }} - {{ $player->team->name }}
                                                    </option>
                                                @endforeach
                                                <option value="forfeit" {{ $homePlayerId == 'forfeit' ? 'selected' : '' }}>Forfait</option>
                                            </select>
                                        </td>
                                        <td class="small" data-label="Team" id="home-team-{{ $i }}">
                                            {{ $homePlayerId ? $sortedHomeTeamPlayers->firstWhere('id', $homePlayerId)->team->name : '' }}
                                        </td>
                                        <td data-label="{{ $game->awayTeam->name }}">
                                            <select class="form-control player-select wide-select" data-player-type="away" data-row-index="{{ $i }}" name="scores[{{ $i }}][away_player]">
                                                <option value="">Selecteer speler</option>
                                                @foreach ($sortedAwayTeamPlayers as $player)
                                                    <option value="{{ $player->id }}" {{ $player->id == $awayPlayerId ? 'selected' : '' }}>
                                                        {{ $player->first_name }} {{ $player->last_name }} - {{ $player->team->name }}
                                                    </option>
                                                @endforeach
                                                <option value="forfeit" {{ $awayPlayerId == 'forfeit' ? 'selected' : '' }}>Forfait</option>
                                            </select>
                                        </td>
                                        <td class="small" data-label="Team" id="away-team-{{ $i }}">
                                            {{ $awayPlayerId ? $sortedAwayTeamPlayers->firstWhere('id', $awayPlayerId)->team->name : '' }}
                                        </td>
                                        <td data-label="1M">
                                            <input type="number" class="form-control manche" name="scores[{{ $i }}][1M]" value="{{ $score1 }}" maxlength="1">
                                        </td>
                                        <td data-label="2M">
                                            <input type="number" class="form-control manche" name="scores[{{ $i }}][2M]" value="{{ $score2 }}" maxlength="1">
                                        </td>
                                        <td data-label="Belle">
                                            <input type="number" class="form-control belle" name="scores[{{ $i }}][Belle]" value="{{ $belleScore }}" maxlength="1" {{ $score1 != $score2 ? 'readonly' : '' }}>
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
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
    const form = document.getElementById('matchForm');
    const rows = document.querySelectorAll('tbody tr');
    const homeScoreInput = document.getElementById('home_score');
    const awayScoreInput = document.getElementById('away_score');
    const forfeitTeamSelect = document.getElementById('forfeit_team');
    const playerSelects = document.querySelectorAll('.player-select');
    const captainReserveSection = document.querySelector('.card:nth-child(3)');
    const playersAndScoresSection = document.querySelector('.card:nth-child(4)');
    const gameId = {{ $game->id }};
    const EXPIRY_TIME = 32400000;

    const homeTeamName = '{{ $game->homeTeam->name }}';
    const awayTeamName = '{{ $game->awayTeam->name }}';

    loadFormData();

    form.addEventListener('input', function(event) {
        updateResults();
        saveFormData();
        updateLiveScore(gameId);
    });

    form.addEventListener('submit', function() {
        localStorage.removeItem(`matchFormData_${gameId}`);
        localStorage.removeItem(`matchFormDataExpiry_${gameId}`);
        updateLiveScore(gameId);
    });

    forfeitTeamSelect.addEventListener('change', function() {
        if (this.value === 'home') {
            homeScoreInput.value = 0;
            awayScoreInput.value = 6;
            captainReserveSection.style.display = 'none';
            playersAndScoresSection.style.display = 'none';
        } else if (this.value === 'away') {
            homeScoreInput.value = 6;
            awayScoreInput.value = 0;
            captainReserveSection.style.display = 'none';
            playersAndScoresSection.style.display = 'none';
        } else {
            let homeWins = 0;
            let awayWins = 0;

            rows.forEach(row => {
                const manche1Input = row.querySelector('input[name*="[1M]"]');
                const manche2Input = row.querySelector('input[name*="[2M]"]');
                const belleInput = row.querySelector('input[name*="[Belle]"]');

                let homePoints = 0;
                let awayPoints = 0;

                if (parseInt(manche1Input.value) === 1) homePoints++;
                if (parseInt(manche2Input.value) === 2) awayPoints++;
                if (parseInt(manche1Input.value) === 2) awayPoints++;
                if (parseInt(manche2Input.value) === 1) homePoints++;

                if (homePoints === awayPoints && belleInput.value) {
                    if (parseInt(belleInput.value) === 1) homePoints++;
                    if (parseInt(belleInput.value) === 2) awayPoints++;
                }

                if (homePoints > awayPoints) homeWins++;
                if (awayPoints > homePoints) awayWins++;
            });

            homeScoreInput.value = homeWins;
            awayScoreInput.value = awayWins;

            captainReserveSection.style.display = '';
            playersAndScoresSection.style.display = '';
        }
    });

    playerSelects.forEach(select => {
        select.addEventListener('change', updateAvailableOptions);
    });

    rows.forEach(row => {
        const inputs = row.querySelectorAll('.manche, .belle');
        inputs.forEach(input => {
            input.addEventListener('input', updateResults);
        });
    });

    document.querySelectorAll('.player-select').forEach(select => {
        select.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            let teamName;
            if (selectedOption.value === 'forfeit') {
                const playerType = this.dataset.playerType;
                teamName = (playerType === 'home') ? homeTeamName : awayTeamName;
            } else {
                teamName = selectedOption.text.split(' - ')[1] || 'Geen team gevonden';
            }
            const playerType = this.dataset.playerType;
            const rowIndex = this.dataset.rowIndex;
            document.getElementById(`${playerType}-team-${rowIndex}`).textContent = teamName;
            updateAvailableOptions();
            updateTeamNames();
            updateResults();
        });
    });

    function updateResults() {
        let homeScore = 0;
        let awayScore = 0;

        rows.forEach(row => {
            const homePlayerSelect = row.querySelector('select[name*="[home_player]"]');
            const awayPlayerSelect = row.querySelector('select[name*="[away_player]"]');
            const firstMatchInput = row.querySelector('input[name*="[1M]"]');
            const secondMatchInput = row.querySelector('input[name*="[2M]"]');
            const belleInput = row.querySelector('input[name*="[Belle]"]');
            const resultCell = row.querySelector('.result-cell');
            let result = '';

            const homePlayer = homePlayerSelect.value;
            const awayPlayer = awayPlayerSelect.value;

            let isMatchComplete = false;

            if ((homePlayer === 'forfeit' || awayPlayer === 'forfeit')) {
                isMatchComplete = true;
            } else if (firstMatchInput.value && secondMatchInput.value) {
                isMatchComplete = true;
            }

            if (!homePlayer && !awayPlayer) {
                result = '';
            } else if (!isMatchComplete) {
                result = 'Match loopt';
            } else if (homePlayer === 'forfeit' && awayPlayer === 'forfeit') {
                result = 'Beide forfait';
            } else if (homePlayer === 'forfeit') {
                awayScore++;
                result = 'Uit wint (forfait)';
            } else if (awayPlayer === 'forfeit') {
                homeScore++;
                result = 'Thuis wint (forfait)';
            } else {
                let homeSetsWon = 0;
                let awaySetsWon = 0;

                if (firstMatchInput.value === '1') homeSetsWon++;
                if (firstMatchInput.value === '2') awaySetsWon++;

                if (secondMatchInput.value === '1') homeSetsWon++;
                if (secondMatchInput.value === '2') awaySetsWon++;

                if (firstMatchInput.value !== secondMatchInput.value) {
                    belleInput.removeAttribute('readonly');
                } else {
                    belleInput.setAttribute('readonly', true);
                    belleInput.value = '';
                }

                if (belleInput.value === '1') homeSetsWon++;
                if (belleInput.value === '2') awaySetsWon++;

                if (homeSetsWon > awaySetsWon) {
                    homeScore++;
                    result = 'Thuis wint';
                } else if (awaySetsWon > homeSetsWon) {
                    awayScore++;
                    result = 'Uit wint';
                } else {
                    result = 'Gelijkspel';
                }
            }

            if (resultCell) {
                resultCell.textContent = result;
            }
        });

        homeScoreInput.value = homeScore;
        awayScoreInput.value = awayScore;
    }

    function updateAvailableOptions() {
        let selectedPlayers = [];

        rows.forEach(row => {
            const homePlayerSelect = row.querySelector('select[name*="[home_player]"]');
            const awayPlayerSelect = row.querySelector('select[name*="[away_player]"]');

            if (homePlayerSelect && homePlayerSelect.value && homePlayerSelect.value !== 'forfeit') {
                selectedPlayers.push(homePlayerSelect.value);
            }
            if (awayPlayerSelect && awayPlayerSelect.value && awayPlayerSelect.value !== 'forfeit') {
                selectedPlayers.push(awayPlayerSelect.value);
            }
        });

        rows.forEach(row => {
            const homePlayerSelect = row.querySelector('select[name*="[home_player]"]');
            const awayPlayerSelect = row.querySelector('select[name*="[away_player]"]');

            [homePlayerSelect, awayPlayerSelect].forEach(select => {
                if (select) {
                    let currentSelection = select.value;
                    let options = select.querySelectorAll('option');
                    options.forEach(option => {
                        if (selectedPlayers.includes(option.value) && option.value !== currentSelection) {
                            option.disabled = true;
                        } else {
                            option.disabled = false;
                        }
                    });
                }
            });
        });
    }

    function updateLiveScore(gameId) {
        const homeScore = document.getElementById('home_score').value;
        const awayScore = document.getElementById('away_score').value;

        const homeCaptain = document.querySelector('select[name="home_captain"]').value;
        const awayCaptain = document.querySelector('select[name="away_captain"]').value;
        const homeReserve = document.querySelector('select[name="home_reserve"]').value;
        const awayReserve = document.querySelector('select[name="away_reserve"]').value;

        const forfeitTeam = document.getElementById('forfeit_team').value;

        let scores = [];
        rows.forEach((row, index) => {
            const homePlayer = row.querySelector(`select[name="scores[${index}][home_player]"]`).value;
            const awayPlayer = row.querySelector(`select[name="scores[${index}][away_player]"]`).value;
            const firstManche = row.querySelector(`input[name="scores[${index}][1M]"]`).value;
            const secondManche = row.querySelector(`input[name="scores[${index}][2M]"]`).value;
            const belle = row.querySelector(`input[name="scores[${index}][Belle]"]`).value;

            scores.push({
                home_player: homePlayer || null,
                away_player: awayPlayer || null,
                '1M': firstManche || 'N/A',
                '2M': secondManche || 'N/A',
                Belle: belle || 'N/A'
            });
        });

        fetch(`/games/${gameId}/update-live-score`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                home_score: homeScore,
                away_score: awayScore,
                home_captain: homeCaptain,
                away_captain: awayCaptain,
                home_reserve: homeReserve,
                away_reserve: awayReserve,
                scores: scores,
                forfeit_team: forfeitTeam
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Live score updated successfully');
            } else {
                console.error('Failed to update live score');
            }
        })
        .catch(error => console.error('Error updating live score:', error));
    }

    function saveFormData() {
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });
        localStorage.setItem(`matchFormData_${gameId}`, JSON.stringify(data));
        localStorage.setItem(`matchFormDataExpiry_${gameId}`, Date.now() + EXPIRY_TIME);
    }

    function loadFormData() {
        const savedData = localStorage.getItem(`matchFormData_${gameId}`);
        const expiry = localStorage.getItem(`matchFormDataExpiry_${gameId}`);
        if (savedData && expiry && Date.now() < expiry) {
            const data = JSON.parse(savedData);
            Object.keys(data).forEach(key => {
                const element = form.querySelector(`[name="${key}"]`);
                if (element) {
                    if (element.type === 'checkbox' || element.type === 'radio') {
                        element.checked = data[key];
                    } else {
                        element.value = data[key];
                    }
                }
            });
            updateResults();
            updateTeamNames();
        } else {
            localStorage.removeItem(`matchFormData_${gameId}`);
            localStorage.removeItem(`matchFormDataExpiry_${gameId}`);
        }
    }
    
    const scoreInputs = document.querySelectorAll('.manche, .belle');

scoreInputs.forEach(input => {
    input.addEventListener('input', function(event) {
        const value = this.value;

        // Alleen 1 of 2 toestaan als invoer
        if (value !== '1' && value !== '2') {
            this.value = ''; // Wis ongeldige invoer
        }
    });

    // Optioneel: voorkomen dat ongeldige tekens worden getypt
    input.addEventListener('keydown', function(event) {
        // Toestaan van backspace, tab, enter, pijltoetsen
        if (
            event.key === 'Backspace' || 
            event.key === 'Tab' || 
            event.key === 'Enter' || 
            event.key === 'ArrowLeft' || 
            event.key === 'ArrowRight'
        ) {
            return;
        }

        // Alleen '1' of '2' toestaan
        if (event.key !== '1' && event.key !== '2') {
            event.preventDefault(); // Voorkom andere invoer
        }
    });
});

    function updateTeamNames() {
        document.querySelectorAll('.player-select').forEach(select => {
            const selectedOption = select.options[select.selectedIndex];
            if (selectedOption && selectedOption.value) {
                let teamName;
                if (selectedOption.value === 'forfeit') {
                    const playerType = select.dataset.playerType;
                    teamName = (playerType === 'home') ? homeTeamName : awayTeamName;
                } else {
                    teamName = selectedOption.text.split(' - ')[1] || 'Geen team gevonden';
                }
                const playerType = select.dataset.playerType;
                const rowIndex = select.dataset.rowIndex;
                const teamCell = document.getElementById(`${playerType}-team-${rowIndex}`);
                if (teamCell) {
                    teamCell.textContent = teamName;
                }
            }
        });
    }

    updateResults();
    updateAvailableOptions();
});
</script>

@endsection

@section('styles')
<style>
.player-select {
    width: 100%;
}

.manche, .belle {
    max-width: 50px;
    text-align: center;
}

.table-responsive {
    display: block;
}

@media (max-width: 767px) {
    .table thead {
        display: none;
    }

    .table tbody tr {
        display: block;
        margin-bottom: 10px;
        border-bottom: 1px solid #dee2e6;
    }

    .table tbody tr td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        font-size: 0.875rem;
        text-align: left;
    }

    .table tbody tr td:before {
        content: attr(data-label);
        font-weight: bold;
        flex: 1;
        padding-right: 10px;
        color: #333;
    }

    .form-control {
        width: 100%;
        font-size: 0.875rem;
        padding: 0.4rem;
    }
}
</style>
@endsection
