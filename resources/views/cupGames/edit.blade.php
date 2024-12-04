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
            <p><strong>Ronde:</strong> {{ $game->round->round_name }}</p>


        </div>
    </div>
    @if($game->round->round_name === '1/8 Finale Terugwedstrijd' || $game->round->round_name === '1/4 Finale Terugwedstrijd')
    <div class="card mt-4" id="totalScoreCard">
        <div class="card-header">Totale Score</div>
        <div class="card-body" id="totalScoreContent">
            <p><strong>Heenwedstrijd:</strong> {{ $game->homeTeam->name }} {{ $totalScore['first_leg_home_score'] }} - {{ $totalScore['first_leg_away_score'] }} {{ $game->awayTeam->name }}</p>
            <p><strong>Terugwedstrijd:</strong> {{ $game->homeTeam->name }} {{ $totalScore['second_leg_home_score'] }} - {{ $totalScore['second_leg_away_score'] }} {{ $game->awayTeam->name }}</p>
            <hr>
            <h5><strong>Totaal:</strong> {{ $game->homeTeam->name }} {{ $totalScore['total_home_score'] }} - {{ $totalScore['total_away_score'] }} {{ $game->awayTeam->name }}</h5>
        </div>
    </div>
    @endif
    
    

    <form id="cupMatchForm" action="{{ route('cup-games.update-live-score', $game->id) }}" method="POST" target="hiddenIframe">


        @csrf
        @method('PUT')
         <!-- Hidden iframe to handle polling without page reload -->
         <iframe id="hiddenIframe" name="hiddenIframe" style="display:none;"></iframe>
        
        <input type="hidden" name="home_team_id" value="{{ $game->homeTeam->id }}">
        <input type="hidden" name="away_team_id" value="{{ $game->awayTeam->id }}">
        <input type="hidden" name="date" value="{{ $game->date->format('d-m-Y') }}">
        <input type="hidden" name="division_id" value="{{ $game->division_id }}">
        <input type="hidden" name="season_id" value="{{ $game->season_id }}">
        <input type="hidden" name="round" value="{{ json_encode($game->round) }}">



        <div class="card">
            <div class="card-header">Kapiteins en reservespelers</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-6 mb-2">
                        <label>Kapitein {{ $game->homeTeam->name }}</label>
                        <select class="form-control player-select" name="home_captain">
                            <option value="">Speler</option>
                            @foreach ($homeTeamPlayers as $player)
                                <option value="{{ $player->id }}" {{ $player->id == optional($game->homeTeam->captain)->id ? 'selected' : '' }}>
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
                                <option value="{{ $player->id }}" {{ $player->id == optional($game->awayTeam->captain)->id ? 'selected' : '' }}>
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
                                <option value="{{ $player->id }}" {{ $player->id == optional($game->homeTeam->reserve)->id ? 'selected' : '' }}>
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
                                <option value="{{ $player->id }}" {{ $player->id == optional($game->awayTeam->reserve)->id ? 'selected' : '' }}>
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
                        <input type="text" class="form-control" id="home_score" name="home_score" value="{{ $game->home_score ?? '0' }}" readonly>
                    </div>
                    <div class="form-group">
                        <label>Uit ploeg {{ $game->awayTeam->name }}:</label>
                        <input type="text" class="form-control" id="away_score" name="away_score" value="{{ $game->away_score ?? '0' }}" readonly>
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
                                <tr>
                                    <td data-label="{{ $game->homeTeam->name }}">
                                        <select class="form-control player-select wide-select" data-player-type="home" data-row-index="{{ $i }}" name="scores[{{ $i }}][home_player]">
                                            <option value="">Selecteer speler</option>
                                            @foreach ($sortedHomeTeamPlayers as $player)
                                                <option value="{{ $player->id }}">{{ $player->first_name }} {{ $player->last_name }} - {{ $player->team->name }}</option>
                                            @endforeach
                                            <option value="forfeit">Forfait</option>
                                        </select>
                                    </td>
                                    <td class="small" data-label="Team" id="home-team-{{ $i }}"></td>
                                    <td data-label="{{ $game->awayTeam->name }}">
                                        <select class="form-control player-select wide-select" data-player-type="away" data-row-index="{{ $i }}" name="scores[{{ $i }}][away_player]">
                                            <option value="">Selecteer speler</option>
                                            @foreach ($sortedAwayTeamPlayers as $player)
                                                <option value="{{ $player->id }}">{{ $player->first_name }} {{ $player->last_name }} - {{ $player->team->name }}</option>
                                            @endforeach
                                            <option value="forfeit">Forfait</option>
                                        </select>
                                    </td>
                                    <td class="small" data-label="Team" id="away-team-{{ $i }}"></td>
                                    <td data-label="1M">
                                        <input type="number" class="form-control manche" name="scores[{{ $i }}][1M]" maxlength="1" pattern="[12]" required value="0">
                                    </td>
                                    <td data-label="2M">
                                        <input type="number" class="form-control manche" name="scores[{{ $i }}][2M]" maxlength="1" pattern="[12]" required value="0">
                                    </td>
                                    <td data-label="Belle">
                                        <input type="number" class="form-control belle" name="scores[{{ $i }}][Belle]" maxlength="1" pattern="[12]" readonly value="0">
                                    </td>
                                </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        </div>

        
        <div class="card mt-4">
            <div class="card-header bg-warning text-white">Testmatch</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered testmatch">
                        <thead>
                            <tr class="text-center">
                                <th>{{ $game->homeTeam->name }}</th>
                                <th>{{ $game->awayTeam->name }}</th>
                                <th>1M</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < 3; $i++)
                                <tr>
                                    <td>
                                        <select class="form-control player-select" name="testmatch[{{ $i }}][home_player]">
                                            <option value="">Selecteer speler</option>
                                            @foreach ($homeTeamPlayers as $player)
                                                <option value="{{ $player->id }}">
                                                    {{ $player->first_name }} {{ $player->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control player-select" name="testmatch[{{ $i }}][away_player]">
                                            <option value="">Selecteer speler</option>
                                            @foreach ($awayTeamPlayers as $player)
                                                <option value="{{ $player->id }}">
                                                    {{ $player->first_name }} {{ $player->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control manche" name="testmatch[{{ $i }}][1M]" 
                                               min="1" max="2">
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        


</form>
<!-- Formulier voor echte goedkeuring -->
<form action="{{ route('cupGames.approve', ['cup' => $game->cup_id, 'game' => $game->id]) }}" method="POST">
    @csrf
    <button type="submit" class="btn btn-success">Bevestig Goedkeuring</button>
</form>



</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('cupMatchForm');
    const scoreInputs = document.querySelectorAll('.manche, .belle');
    const rows = document.querySelectorAll('tbody tr');
    const homeScoreInput = document.getElementById('home_score');
    const awayScoreInput = document.getElementById('away_score');
    const saveButton = document.getElementById('saveButton');
    const playerSelects = document.querySelectorAll('.player-select');
    const scoreTable = document.querySelector('.table-responsive');
    const captainReserveSection = document.querySelector('.card:nth-child(3)'); 
    const playersAndScoresSection = document.querySelector('.card:nth-child(4)'); 
    const gameId = {{ $game->id }}; 
    const EXPIRY_TIME = 32400000; 
    const pollingInterval = 10000; // Poll every 10 seconds
    // Laad opgeslagen formuliergegevens
    loadFormData();

    // Eventlisteners voor inputverwerking
    form.addEventListener('input', function () {
        updateResults();
        saveFormData();
        updateLiveScore(gameId);
    });

    // Polling mechanism for live score updates
    setInterval(fetchLiveScoreData, pollingInterval);


      // Definieer de teamnamen
    const homeTeamName = '{{ $game->homeTeam->name }}';
    const awayTeamName = '{{ $game->awayTeam->name }}';
 
    
    
    form.addEventListener('input', function(event) {
        updateResults();
        saveFormData();
        updateLiveScore(gameId);
    });

    form.addEventListener('submit', function() {
        localStorage.removeItem(`matchFormData_${gameId}`);
        localStorage.removeItem(`matchFormDataExpiry_${gameId}`);
    });

    

    function setFieldsDisabled(disable) {
        scoreInputs.forEach(input => {
            input.disabled = disable;
            if (disable) {
                input.value = ''; 
            }
        });
        playerSelects.forEach(select => {
            select.disabled = disable;
        });
    }

    document.getElementById('cupMatchForm').addEventListener('submit', function() {
    alert('Wedstrijd succesvol bijgewerkt!');
});

function fetchLiveScoreData() {
    fetch(`/cup-games/${gameId}/live-score`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Fetched live score data:', data);
            if (data && data.scores && Array.isArray(data.scores)) {
                populateForm(data);
                updateResults();
            } else {
                console.error('Invalid live score data structure:', data);
            }
        })
        .catch(error => {
            console.error('Error fetching live score:', error);
        });
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



playerSelects.forEach(select => {
        select.addEventListener('change', updateAvailableOptions);
    });

     // Update de wedstrijdresultaten
     function updateResults() {
    let homeScore = 0;
    let awayScore = 0;

    // Reguliere wedstrijdscores verwerken
    document.querySelectorAll('table tbody tr').forEach((row) => {
        const homePlayerSelect = row.querySelector('select[name*="[home_player]"]');
        const awayPlayerSelect = row.querySelector('select[name*="[away_player]"]');
        const firstMatchInput = row.querySelector('input[name*="[1M]"]');
        const secondMatchInput = row.querySelector('input[name*="[2M]"]');
        const belleInput = row.querySelector('input[name*="[Belle]"]');

        let homeSetsWon = 0;
        let awaySetsWon = 0;

        if (firstMatchInput && secondMatchInput) {
            if (firstMatchInput.value && secondMatchInput.value) {
                // Controleer of een belle nodig is
                if (firstMatchInput.value !== secondMatchInput.value) {
                    belleInput.removeAttribute('readonly');
                } else {
                    belleInput.setAttribute('readonly', true);
                    belleInput.value = ''; // Reset belle indien niet nodig
                }

                // Sets toewijzen
                if (firstMatchInput.value === '1') homeSetsWon++;
                if (firstMatchInput.value === '2') awaySetsWon++;
                if (secondMatchInput.value === '1') homeSetsWon++;
                if (secondMatchInput.value === '2') awaySetsWon++;
            }

            if (homeSetsWon === awaySetsWon && belleInput.value) {
                if (belleInput.value === '1') homeSetsWon++;
                if (belleInput.value === '2') awaySetsWon++;
            }

            if (homeSetsWon > awaySetsWon) homeScore++;
            if (awaySetsWon > homeSetsWon) awayScore++;
        }
    });

    // Update testmatch-scores
    document.querySelectorAll('table.testmatch tbody tr').forEach((row) => {
        const testMatchInput = row.querySelector('input[name*="[1M]"]');
        if (testMatchInput && testMatchInput.value) {
            if (testMatchInput.value === '1') homeScore++;
            if (testMatchInput.value === '2') awayScore++;
        }
    });

    // Update totale score
    document.getElementById('home_score').value = homeScore;
    document.getElementById('away_score').value = awayScore;
}



document.querySelectorAll('.player-select').forEach(select => {
    select.addEventListener('change', function() {
        const selectedOption = select.options[select.selectedIndex];
        console.log(`Selected player: ${selectedOption.text}`);
        let teamName;
        if (selectedOption.value === 'forfeit') {
            const playerType = select.dataset.playerType;
            if (playerType === 'home') {
                teamName = homeTeamName;
            } else if (playerType === 'away') {
                teamName = awayTeamName;
            } else {
                teamName = 'Geen team gevonden';
            }
        } else {
            teamName = selectedOption.text.split(' - ')[1] || 'Geen team gevonden';
        }
        const playerType = select.dataset.playerType;
        const rowIndex = select.dataset.rowIndex;
        const teamCell = document.getElementById(`${playerType}-team-${rowIndex}`);
        if (teamCell) {
            teamCell.textContent = teamName;
        }
        
        updateAvailableOptions();
        updateTeamNames();
        updateResults();
    });
});
/* 
function updateCupLiveScore(gameId) {
    const homeScore = document.getElementById('home_score').value;
    const awayScore = document.getElementById('away_score').value;
    const homeCaptain = document.querySelector('select[name="home_captain"]').value;
    const awayCaptain = document.querySelector('select[name="away_captain"]').value;
    const homeReserve = document.querySelector('select[name="home_reserve"]').value;
    const awayReserve = document.querySelector('select[name="away_reserve"]').value;

    // Verwerken van de scores
    const scores = [];
    rows.forEach((row, index) => {
        const homePlayer = row.querySelector(`select[name="scores[${index}][home_player]"]`).value;
        const awayPlayer = row.querySelector(`select[name="scores[${index}][away_player]"]`).value;
        const firstManche = row.querySelector(`input[name="scores[${index}][1M]"]`).value;
        const secondManche = row.querySelector(`input[name="scores[${index}][2M]"]`).value;
        const belle = row.querySelector(`input[name="scores[${index}][Belle]"]`).value;

        scores.push({
            home_player: homePlayer || null,
            away_player: awayPlayer || null,
            '1M': firstManche || null,
            '2M': secondManche || null,
            Belle: belle || null
        });
    });

    // Data versturen naar de server
    fetch(`/cup-games/${gameId}/update-live-score`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            home_score: homeScore,
            away_score: awayScore,
            home_captain: homeCaptain,
            away_captain: awayCaptain,
            home_reserve: homeReserve,
            away_reserve: awayReserve,
            scores: scores,
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to update live score');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            console.log('Live score updated successfully');
        } else {
            alert('Er is een fout opgetreden bij het opslaan.');
        }
    })
    .catch(error => {
        console.error('Error updating live score:', error);
        alert('Er is een fout opgetreden bij het bijwerken van de live score.');
    });
}

 */


function saveFormData() {
    const formData = new FormData(document.getElementById('cupMatchForm'));
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

    // Als er geldige lokale gegevens zijn, laad deze in
    if (savedData && expiry && Date.now() < expiry) {
        const data = JSON.parse(savedData);
        Object.keys(data).forEach(key => {
            const element = document.querySelector(`[name="${key}"]`);
            if (element) {
                if (element.type === 'checkbox' || element.type === 'radio') {
                    element.checked = data[key];
                } else {
                    element.value = data[key];
                }
            }
        });
    } else {
        localStorage.removeItem(`matchFormData_${gameId}`);
        localStorage.removeItem(`matchFormDataExpiry_${gameId}`);
    }

    // Daarna live score gegevens ophalen en formulier updaten
    fetch(`/cup-games/${gameId}/live-score`)
        .then(response => response.json())
        .then(data => {
            if (data) {
                populateForm(data);
                updateResults();
                updateTeamNames();
            }
        })
        .catch(error => console.error('Error fetching live score data:', error));
}
/* 

function updateCupLiveScore(gameId) {
    const homeScore = document.getElementById('home_score').value;
    const awayScore = document.getElementById('away_score').value;
    const homeCaptain = document.querySelector('select[name="home_captain"]').value;
    const awayCaptain = document.querySelector('select[name="away_captain"]').value;
    const homeReserve = document.querySelector('select[name="home_reserve"]').value;
    const awayReserve = document.querySelector('select[name="away_reserve"]').value;

    // Verwerken van reguliere scores
    const scores = [];
    rows.forEach((row, index) => {
        const homePlayer = row.querySelector(`select[name="scores[${index}][home_player]"]`).value;
        const awayPlayer = row.querySelector(`select[name="scores[${index}][away_player]"]`).value;
        const firstManche = row.querySelector(`input[name="scores[${index}][1M]"]`).value;
        const secondManche = row.querySelector(`input[name="scores[${index}][2M]"]`).value;
        const belle = row.querySelector(`input[name="scores[${index}][Belle]"]`).value;

        scores.push({
            home_player: homePlayer || null,
            away_player: awayPlayer || null,
            '1M': firstManche || null,
            '2M': secondManche || null,
            Belle: belle || null,
        });
    });

    // Verwerken van testmatch-scores
    const testmatchScores = [];
    document.querySelectorAll('table.testmatch tbody tr').forEach((row, index) => {
        const homePlayer = row.querySelector(`select[name="testmatch[${index}][home_player]"]`).value;
        const awayPlayer = row.querySelector(`select[name="testmatch[${index}][away_player]"]`).value;
        const result = row.querySelector(`input[name="testmatch[${index}][1M]"]`).value;

        if (homePlayer && awayPlayer && result) {
            testmatchScores.push({
                home_player: homePlayer,
                away_player: awayPlayer,
                '1M': result,
            });
        }
    });

    // Data versturen naar de server
    fetch(`/cup-games/${gameId}/update-live-score`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
            home_score: homeScore,
            away_score: awayScore,
            home_captain: homeCaptain,
            away_captain: awayCaptain,
            home_reserve: homeReserve,
            away_reserve: awayReserve,
            scores: scores,
            testmatch: testmatchScores,
        }),
    })
        .then(response => {
            if (!response.ok) throw new Error('Failed to update live score');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('Live score updated successfully');
            } else {
                alert('Er is een fout opgetreden bij het opslaan.');
            }
        })
        .catch(error => {
            console.error('Error updating live score:', error);
            alert('Er is een fout opgetreden bij het bijwerken van de live score.');
        });
}
 */

// Function to populate the form fields with live score data

function populateForm(data) {
    console.log('Populating form with data:', data);

    // Check if scores is an array, if not, log error and return
    if (!Array.isArray(data.scores)) {
        console.error('Expected scores to be an array but received:', data.scores);

        // Attempt to fix the structure if it's an object with players
        if (data.scores && typeof data.scores === 'object' && data.scores.home_players && data.scores.away_players) {
            if (data.scores.home_players.length === data.scores.away_players.length && data.scores.home_players.length > 0) {
                console.log('Converting home_players and away_players into scores array');
                data.scores = data.scores.home_players.map((homePlayer, index) => {
                    const awayPlayer = data.scores.away_players[index] || { name: 'Nog niet gestart', team: 'Onbekend' };
                    return {
                        home_player_name: homePlayer.name,
                        home_player_team: homePlayer.team,
                        away_player_name: awayPlayer.name,
                        away_player_team: awayPlayer.team,
                        '1M': '',
                        '2M': '',
                        'Belle': '',
                        'WinnerId': null
                    };
                });
            } else {
                console.error('Mismatch between home_players and away_players array lengths or one of them is empty.');
                return;
            }
        } else {
            console.warn('Cannot proceed with populating form. Invalid scores data format.');
            return;
        }
    }

    // Populate standard fields
    for (const key in data) {
        if (data.hasOwnProperty(key)) {
            const element = document.querySelector(`[name="${key}"]`);
            if (element) {
                console.log(`Populating field ${key} with value:`, data[key]);
                element.value = data[key] !== null ? data[key] : ''; // Fallback to empty string if value is null
            }
        }
    }

    // Populate score arrays
    data.scores.forEach((score, index) => {
        const homePlayerInput = document.querySelector(`[name="scores[${index}][home_player]"]`);
        const awayPlayerInput = document.querySelector(`[name="scores[${index}][away_player]"]`);
        const firstMancheInput = document.querySelector(`[name="scores[${index}][1M]"]`);
        const secondMancheInput = document.querySelector(`[name="scores[${index}][2M]"]`);
        const belleInput = document.querySelector(`[name="scores[${index}][Belle]"]`);

        // Check if home player select element exists
        if (homePlayerInput && score.home_player_name) {
            const homePlayerOption = Array.from(homePlayerInput.options).find(option => option.text.trim() === score.home_player_name.trim());
            if (homePlayerOption) {
                homePlayerInput.value = homePlayerOption.value;
                console.log(`Setting home player for row ${index}: ${score.home_player_name}`);
            } else {
                console.warn(`Home player ${score.home_player_name} not found in options for row ${index}`);
            }
        }

        // Check if away player select element exists
        if (awayPlayerInput && score.away_player_name) {
            const awayPlayerOption = Array.from(awayPlayerInput.options).find(option => option.text.trim() === score.away_player_name.trim());
            if (awayPlayerOption) {
                awayPlayerInput.value = awayPlayerOption.value;
                console.log(`Setting away player for row ${index}: ${score.away_player_name}`);
            } else {
                console.warn(`Away player ${score.away_player_name} not found in options for row ${index}`);
            }
        }

        // Update manche and belle inputs if they exist
        if (firstMancheInput) {
            firstMancheInput.value = score['1M'] !== null ? score['1M'] : ''; // Null fallback
        }
        if (secondMancheInput) {
            secondMancheInput.value = score['2M'] !== null ? score['2M'] : ''; // Null fallback
        }
        if (belleInput) {
            belleInput.value = score.Belle !== null ? score.Belle : ''; // Null fallback
        }
    });

    // Populate captains and reserves
    populateCaptainsAndReserves(data);
}

// Helper function for captains and reserves
function populateCaptainsAndReserves(data) {
    const homeCaptainInput = document.querySelector('select[name="home_captain"]');
    const awayCaptainInput = document.querySelector('select[name="away_captain"]');
    const homeReserveInput = document.querySelector('select[name="home_reserve"]');
    const awayReserveInput = document.querySelector('select[name="away_reserve"]');

    if (homeCaptainInput && data.home_captain_name) {
        const homeCaptainOption = Array.from(homeCaptainInput.options).find(option => option.text.includes(data.home_captain_name));
        if (homeCaptainOption) {
            homeCaptainInput.value = homeCaptainOption.value;
            console.log(`Setting home captain: ${data.home_captain_name}`);
        } else {
            console.warn(`Home captain ${data.home_captain_name} not found`);
        }
    }

    if (awayCaptainInput && data.away_captain_name) {
        const awayCaptainOption = Array.from(awayCaptainInput.options).find(option => option.text.includes(data.away_captain_name));
        if (awayCaptainOption) {
            awayCaptainInput.value = awayCaptainOption.value;
            console.log(`Setting away captain: ${data.away_captain_name}`);
        } else {
            console.warn(`Away captain ${data.away_captain_name} not found`);
        }
    }

    if (homeReserveInput && data.home_reserve_name) {
        const homeReserveOption = Array.from(homeReserveInput.options).find(option => option.text.includes(data.home_reserve_name));
        if (homeReserveOption) {
            homeReserveInput.value = homeReserveOption.value;
            console.log(`Setting home reserve: ${data.home_reserve_name}`);
        } else {
            console.warn(`Home reserve ${data.home_reserve_name} not found`);
        }
    }

    if (awayReserveInput && data.away_reserve_name) {
        const awayReserveOption = Array.from(awayReserveInput.options).find(option => option.text.includes(data.away_reserve_name));
        if (awayReserveOption) {
            awayReserveInput.value = awayReserveOption.value;
            console.log(`Setting away reserve: ${data.away_reserve_name}`);
        } else {
            console.warn(`Away reserve ${data.away_reserve_name} not found`);
        }
    }
}


fetch(`/games/${gameId}/live-score`, {
    method: 'GET',
    headers: {
        'Content-Type': 'application/json',
    }
})
    .then(response => response.json())
    .then(data => {
        console.log('Fetched live score data:', data);
        if (data && Array.isArray(data.scores)) {
            populateForm(data); // Populate the form with live score data
        } else {
            console.error('Live score data does not contain a valid scores array:', data.scores);
        }
    })
    .catch(error => {
        console.error('Error fetching live score data:', error);
    });


// Update the live score (send to server)
function updateLiveScore(gameId) {
    console.log("Updating Live Score for Game ID:", gameId);

    let homeScore = 0;
    let awayScore = 0;

    const homeCaptain = document.querySelector('select[name="home_captain"]')?.value || null;
    const awayCaptain = document.querySelector('select[name="away_captain"]')?.value || null;
    const homeReserve = document.querySelector('select[name="home_reserve"]')?.value || null;
    const awayReserve = document.querySelector('select[name="away_reserve"]')?.value || null;

    const scores = [];
    const testmatchScores = [];

    // Verzamel reguliere wedstrijdscores
    document.querySelectorAll('table tbody tr:not(.testmatch-row)').forEach((row, index) => {
        const homePlayer = row.querySelector(`select[name="scores[${index}][home_player]"]`)?.value || null;
        const awayPlayer = row.querySelector(`select[name="scores[${index}][away_player]"]`)?.value || null;
        const firstManche = row.querySelector(`input[name="scores[${index}][1M]"]`)?.value || null;
        const secondManche = row.querySelector(`input[name="scores[${index}][2M]"]`)?.value || null;
        const belle = row.querySelector(`input[name="scores[${index}][Belle]"]`)?.value || null;

        if (homePlayer && awayPlayer) {
            let homeSetsWon = 0;
            let awaySetsWon = 0;

            if (firstManche === '1') homeSetsWon++;
            if (firstManche === '2') awaySetsWon++;
            if (secondManche === '1') homeSetsWon++;
            if (secondManche === '2') awaySetsWon++;

            // Belle wordt alleen overwogen als nodig
            if (homeSetsWon === awaySetsWon) {
                if (belle === '1') homeSetsWon++;
                if (belle === '2') awaySetsWon++;
            }

            // Update totale score
            if (homeSetsWon > awaySetsWon) homeScore++;
            if (awaySetsWon > homeSetsWon) awayScore++;

            scores.push({
                home_player: homePlayer,
                away_player: awayPlayer,
                '1M': firstManche,
                '2M': secondManche,
                Belle: belle,
            });
        }
    });

    // Verzamel testmatch-resultaten
    document.querySelectorAll('table.testmatch tbody tr').forEach((row, index) => {
        const homePlayer = row.querySelector(`select[name="testmatch[${index}][home_player]"]`)?.value || null;
        const awayPlayer = row.querySelector(`select[name="testmatch[${index}][away_player]"]`)?.value || null;
        const result = row.querySelector(`input[name="testmatch[${index}][1M]"]`)?.value || null;

        if (homePlayer && awayPlayer && result) {
            if (result === '1') homeScore++;
            if (result === '2') awayScore++;

            testmatchScores.push({
                home_player: homePlayer,
                away_player: awayPlayer,
                '1M': result,
            });
        }
    });

    // Update UI met totale score
    const homeScoreInput = document.getElementById('home_score');
    const awayScoreInput = document.getElementById('away_score');
    if (homeScoreInput) homeScoreInput.value = homeScore;
    if (awayScoreInput) awayScoreInput.value = awayScore;

    console.log("Final Scores to Send:", { scores, testmatchScores });

    // Verstuur data naar de server
    fetch(`/cup-games/${gameId}/update-live-score`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
            game_id: gameId,
            home_score: homeScore,
            away_score: awayScore,
            home_captain: homeCaptain,
            away_captain: awayCaptain,
            home_reserve: homeReserve,
            away_reserve: awayReserve,
            scores,
            testmatch: testmatchScores,
        }),
    })
        .then(response => {
            if (!response.ok) throw new Error('Failed to update live score');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('Live score updated successfully.');
            } else {
                alert('Error occurred while saving.');
            }
        })
        .catch(error => {
            console.error('Error updating live score:', error);
            alert('Error occurred while updating the live score.');
        });
}





// Handle form submission to save live score data
form.addEventListener('submit', function(event) {
    event.preventDefault(); // Voorkom standaardformulier verzenden

    const formData = new FormData(form); // Maak een FormData object
    const formDataObject = {};

    // Zet FormData om in een object
    formData.forEach((value, key) => {
        formDataObject[key] = value;
    });

    // Stuur formuliergegevens naar de server om de live score voor bekerwedstrijden op te slaan
    fetch(`cup-games/${gameId}/update-live-score`, {  // Let op de aangepaste URL voor bekerwedstrijden
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}' // CSRF token voor beveiliging
        },
        body: JSON.stringify(formDataObject) // Zet FormData om in een JSON string
    })
    .then(response => response.json())
    
    .catch(error => console.error('Fout bij het opslaan van de live score:', error));
});

playerSelects.forEach(select => {
    select.addEventListener('change', function() {
        const rowIndex = this.dataset.rowIndex;
        const homeSelect = document.querySelector(`select[name="scores[${rowIndex}][home_player]"]`);
        const awaySelect = document.querySelector(`select[name="scores[${rowIndex}][away_player]"]`);
        const manche1Input = document.querySelector(`input[name="scores[${rowIndex}][1M]"]`);
        const manche2Input = document.querySelector(`input[name="scores[${rowIndex}][2M]"]`);

        // Check if all necessary elements exist before proceeding
        if (homeSelect && awaySelect && manche1Input && manche2Input) {
            if (homeSelect.value === 'forfeit') {
                // Home player forfeits, away player wins both sets
                manche1Input.value = 2;
                manche2Input.value = 2;
                manche1Input.readOnly = true;
                manche2Input.readOnly = true;
            } else if (awaySelect.value === 'forfeit') {
                // Away player forfeits, home player wins both sets
                manche1Input.value = 1;
                manche2Input.value = 1;
                manche1Input.readOnly = true;
                manche2Input.readOnly = true;
            } else {
                // Reset scores and make inputs editable again
                manche1Input.value = '';
                manche2Input.value = '';
                manche1Input.readOnly = false;
                manche2Input.readOnly = false;
            }
            updateResults(); // Update the results based on the new input
        } else {
            console.warn(`Elements for row index ${rowIndex} not found. Ensure all inputs exist.`);
        }
    });
});
/* 

function updateCupLiveScore(gameId) {
    const homeScore = document.getElementById('home_score').value;
    const awayScore = document.getElementById('away_score').value;
    const homeCaptain = document.querySelector('select[name="home_captain"]').value;
    const awayCaptain = document.querySelector('select[name="away_captain"]').value;
    const homeReserve = document.querySelector('select[name="home_reserve"]').value;
    const awayReserve = document.querySelector('select[name="away_reserve"]').value;

    // Verwerken van de scores
    const scores = [];
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach((row, index) => {
        const homePlayer = row.querySelector(`select[name="scores[${index}][home_player]"]`).value;
        const awayPlayer = row.querySelector(`select[name="scores[${index}][away_player]"]`).value;
        const firstManche = row.querySelector(`input[name="scores[${index}][1M]"]`).value;
        const secondManche = row.querySelector(`input[name="scores[${index}][2M]"]`).value;
        const belle = row.querySelector(`input[name="scores[${index}][Belle]"]`).value;

        scores.push({
            home_player: homePlayer || null,
            away_player: awayPlayer || null,
            '1M': firstManche || null,
            '2M': secondManche || null,
            Belle: belle || null
        });
    });

    // Data versturen naar de server
    fetch(`/cup-games/${gameId}/update-live-score`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            home_score: homeScore,
            away_score: awayScore,
            home_captain: homeCaptain,
            away_captain: awayCaptain,
            home_reserve: homeReserve,
            away_reserve: awayReserve,
            scores: scores,
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to update live score');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            console.log('Live score updated successfully');
        } else {
            alert('Er is een fout opgetreden bij het opslaan.');
        }
    })
    .catch(error => {
        console.error('Error updating live score:', error);
        alert('Er is een fout opgetreden bij het bijwerken van de live score.');
    });
} */

// Function to fetch and update the total score
function fetchTotalScore() {
    fetch(`/cup-games/${gameId}/total-score`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data) {
                updateTotalScoreSection(data);
            } else {
                console.warn('Total score data is not valid:', data);
            }
        })
        .catch(error => {
            console.error('Error fetching total score:', error);
        });
}

// Function to update the total score section in the UI
function updateTotalScoreSection(totalScore) {
    const totalScoreContent = document.getElementById('totalScoreContent');
    if (totalScoreContent) {
        totalScoreContent.innerHTML = `
            <p><strong>Heenwedstrijd:</strong> {{ $game->homeTeam->name }} ${totalScore.first_leg_home_score} - ${totalScore.first_leg_away_score} {{ $game->awayTeam->name }}</p>
            <p><strong>Terugwedstrijd:</strong> {{ $game->homeTeam->name }} ${totalScore.second_leg_home_score} - ${totalScore.second_leg_away_score} {{ $game->awayTeam->name }}</p>
            <hr>
            <h5><strong>Totaal:</strong> {{ $game->homeTeam->name }} ${totalScore.total_home_score} - ${totalScore.total_away_score} {{ $game->awayTeam->name }}</h5>
        `;
    }
}


function updateTeamNames() {
    document.querySelectorAll('.player-select').forEach(select => {
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption && selectedOption.value) {
            let teamName;
            if (selectedOption.value === 'forfeit') {
                const playerType = select.dataset.playerType;
                if (playerType === 'home') {
                    teamName = homeTeamName;
                } else if (playerType === 'away') {
                    teamName = awayTeamName;
                } else {
                    teamName = 'Geen team gevonden';
                }
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
/* Zorg ervoor dat select elementen op mobiele apparaten goed werken */
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
    /* Verberg de originele header op mobiel */
    .table thead {
        display: none;
    }

    /* Zorg ervoor dat de tabel rijen worden gestapeld */
    .table tbody tr {
        display: block;
        margin-bottom: 10px;
        border-bottom: 1px solid #dee2e6;
    }

    /* Alle cellen worden onder elkaar weergegeven */
    .table tbody tr td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        font-size: 0.875rem;
        text-align: left;
    }

    /* Voeg labels toe voor de cellen in mobiele weergave */
    .table tbody tr td:before {
        content: attr(data-label);
        font-weight: bold;
        flex: 1;
        padding-right: 10px;
        color: #333;
    }

    /* Zorg ervoor dat inputs klein genoeg zijn op mobiel */
    .form-control {
        width: 100%;
        font-size: 0.875rem;
        padding: 0.4rem;
    }
}

</style>
@endsection