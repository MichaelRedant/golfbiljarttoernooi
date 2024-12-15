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
   
    
    

    <form id="cupMatchForm" action="{{ route('cup-games.update-live-score', $game->id) }}" method="POST" target="hiddenIframe">


        @csrf
        @method('PUT')
         <!-- Hidden iframe to handle polling without page reload -->
         <iframe id="hiddenIframe" name="hiddenIframe" style="display:none;"></iframe>
        
         <input type="hidden" name="game_id" value="{{ $game->id }}">
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

        @if($game->round->round_name === '1/8 Finale Terugwedstrijd' || $game->round->round_name === '1/4 Finale Terugwedstrijd')
        <div class="card mt-4" id="totalScoreCard">
            <div class="card-header">Totale Score</div>
            <div class="card-body" id="totalScoreContent">
                <p><strong>Heenwedstrijd:</strong> {{ $game->homeTeam->name }} {{ $totalScore['first_leg_home_score'] }} - {{ $totalScore['first_leg_away_score'] }} {{ $game->awayTeam->name }}</p>
                <hr>
                <h5><strong>Totaal:</strong> {{ $game->homeTeam->name }} {{ $totalScore['total_home_score'] }} - {{ $totalScore['total_away_score'] }} {{ $game->awayTeam->name }}</h5>
            </div>
        </div>
        @endif

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
                                        <input type="number" class="form-control manche" name="scores[{{ $i }}][1M]" maxlength="1" pattern="[12]" step="1" value="{{ old('scores.'.$i.'.1M') ?? '' }}">
                                    </td>
                                    <td data-label="2M">
                                        <input type="number" class="form-control manche" name="scores[{{ $i }}][2M]" maxlength="1" pattern="[12]" step="1" value="{{ old('scores.'.$i.'.2M') ?? '' }}">
                                    </td>
                                    <td data-label="Belle">
                                        <input type="number" class="form-control belle" name="scores[{{ $i }}][Belle]" maxlength="1" pattern="[12]" step="1" readonly value="{{ old('scores.'.$i.'.Belle') ?? '' }}">
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
            <div class="card-header">Testmatch</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered testmatch">
                        <thead>
                            <tr>
                                <th>{{ $game->homeTeam->name }}</th>
                                <th>{{ $game->awayTeam->name }}</th>
                                <th>1M</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < 3; $i++)
                            <tr>
                                <td>
                                    <select class="form-control" name="testmatch[{{ $i }}][home_player]">
                                        <option value="">Selecteer speler</option>
                                        @foreach ($homeTeamPlayers as $player)
                                        <option value="{{ $player->id }}">{{ $player->first_name }} {{ $player->last_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control" name="testmatch[{{ $i }}][away_player]">
                                        <option value="">Selecteer speler</option>
                                        @foreach ($awayTeamPlayers as $player)
                                        <option value="{{ $player->id }}">{{ $player->first_name }} {{ $player->last_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="form-control" name="testmatch[{{ $i }}][1M]" min="1" max="2" placeholder="1 of 2">
                                </td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        


</form>
@if (isset($game->cup) && $game->cup)
    <form action="{{ route('cupGames.requestApproval', ['cup' => $game->cup->id, 'cupGame' => $game->id]) }}" method="GET">
        @csrf
        <button type="submit" class="btn btn-warning">Vraag Goedkeuring aan</button>
    </form>
@else
    <p>De beker voor deze wedstrijd is niet gevonden. Goedkeuring aanvragen is niet mogelijk.</p>
@endif




</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('cupMatchForm');
    const scoreInputs = document.querySelectorAll('.manche, .belle');
    const rows = document.querySelectorAll('tbody tr');
    const homeScoreInput = document.getElementById('home_score');
    const awayScoreInput = document.getElementById('away_score');
    const totalScoreContent = document.getElementById('totalScoreContent');
    const saveButton = document.getElementById('saveButton');
    const playerSelects = document.querySelectorAll('.player-select');
    const scoreTable = document.querySelector('.table-responsive');
    const captainReserveSection = document.querySelector('.card:nth-child(3)'); 
    const playersAndScoresSection = document.querySelector('.card:nth-child(4)'); 
    const gameId = {{ $game->id }}; 
    const EXPIRY_TIME = 32400000; 
    const pollingInterval = 10000; // Poll every 10 seconds
    fetchLiveScoreData();
    loadFormData();

    setInterval(fetchLiveScoreData, pollingInterval);


      // Definieer de teamnamen
    const homeTeamName = '{{ $game->homeTeam->name }}';
    const awayTeamName = '{{ $game->awayTeam->name }}';
     // Heenwedstrijd scores (constant geladen van de server)
    const firstLegHomeScore = parseInt('{{ $totalScore["first_leg_home_score"] }}', 10) || 0;
    const firstLegAwayScore = parseInt('{{ $totalScore["first_leg_away_score"] }}', 10) || 0;

 
    
    
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
    ////('Wedstrijd succesvol bijgewerkt!');
});

function fetchLiveScoreData() {
    console.log("Fetching live score data for Cup game...");

    fetch(`/cup-games/${gameId}/live-score`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error: ${response.status} - ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log("Received live score data:", data);

            // Validatie: Controleer of 'data.scores' een array is
            if (!data || !Array.isArray(data.scores)) {
                console.warn("Invalid or missing 'scores' array in live score data:", data.scores);

                // Maak een lege scores array als fallback
                data.scores = initializeDefaultScores(data);
            }

            // Synchroniseer de gegevens met het formulier
            populateForm(data);

            // Werk de resultaten en teamnamen bij
            updateResults();
            updateTeamNames();

            // Zorg ervoor dat beschikbare opties worden bijgewerkt
            updateAvailableOptions();

            console.log("Live score data successfully synchronized with the form.");
        })
        .catch(error => {
            console.error("Error fetching live score data:", error);
            alert("Er is een fout opgetreden bij het ophalen van live score gegevens. Controleer de verbinding en probeer opnieuw.");
        });
}


function initializeDefaultScores(data) {
    return Array.from({ length: 6 }).map(() => ({
        home_player_name: 'Nog niet gestart',
        away_player_name: 'Nog niet gestart',
        home_player_team: data.home_team_name || 'Onbekend',
        away_player_team: data.away_team_name || 'Onbekend',
        '1M': '',
        '2M': '',
        Belle: '',
        WinnerId: null,
    }));
}


document.addEventListener('DOMContentLoaded', () => {
    // Add input validation for manche and belle fields
    const inputs = document.querySelectorAll('.manche, .belle');

    inputs.forEach(input => {
        input.addEventListener('input', function (e) {
            const value = e.target.value;
            if (!/^[12]?$/.test(value)) {
                e.target.value = value.slice(0, -1); // Remove last input if invalid
            }
        });

        input.addEventListener('keypress', function (e) {
            if (e.target.value.length >= 1) {
                e.preventDefault(); // Prevent further input after one character
            }
        });
    });

    // Start polling for live scores
    setInterval(fetchLiveScoreData, 10000); // Adjust polling interval as needed
});


function updateAvailableOptions() {
    let selectedPlayers = [];

    // Verzamel reeds geselecteerde spelers
    const rows = document.querySelectorAll('tbody tr:not(.testmatch-row)');
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

    // Update opties per select-element
    rows.forEach(row => {
        const homePlayerSelect = row.querySelector('select[name*="[home_player]"]');
        const awayPlayerSelect = row.querySelector('select[name*="[away_player]"]');
        const cardHeader = row.closest('.card')?.querySelector('.card-header');
        const isPlayerScoreSection = cardHeader?.textContent.trim() === 'Spelers en Scores';

        [homePlayerSelect, awayPlayerSelect].forEach(select => {
            if (select) {
                const currentTeam = select.dataset.team; // Team-id gekoppeld aan de select
                const currentSelection = select.value;
                const options = select.querySelectorAll('option');

                options.forEach(option => {
                    const optionTeam = option.dataset.team;

                    // Alleen spelers van hetzelfde team tonen in de sectie "Spelers en Scores"
                    if (isPlayerScoreSection && optionTeam !== currentTeam) {
                        option.disabled = true; // Optie uitschakelen
                    } else if (
                        selectedPlayers.includes(option.value) &&
                        option.value !== currentSelection
                    ) {
                        option.disabled = true; // Speler is al geselecteerd
                    } else {
                        option.disabled = false; // Optie inschakelen
                    }
                });
            }
        });
    });

    // Zorg ervoor dat opties in testmatch altijd beschikbaar zijn
    const testmatchRows = document.querySelectorAll('table.testmatch tbody tr');
    testmatchRows.forEach(row => {
        const homeSelect = row.querySelector('select[name*="[home_player]"]');
        const awaySelect = row.querySelector('select[name*="[away_player]"]');

        [homeSelect, awaySelect].forEach(select => {
            if (select) {
                const options = select.querySelectorAll('option');
                options.forEach(option => {
                    option.disabled = false; // Geen beperkingen in testmatch
                });
            }
        });
    });
}

// Voeg eventlisteners toe aan player-selects
if (typeof playerSelects === 'undefined') {
    const playerSelects = document.querySelectorAll('.player-select');
    playerSelects.forEach(select => {
        select.addEventListener('change', updateAvailableOptions);
    });
} else {
    playerSelects.forEach(select => {
        select.addEventListener('change', updateAvailableOptions);
    });
}




function updateResults() {
    let homeScore = 0;
    let awayScore = 0;

    // Haal de Total Score sectie op
    const totalScoreContent = document.getElementById('totalScoreContent');

    // Heenwedstrijd scores (constant geladen van de server)
    const firstLegHomeScore = parseInt('{{ $totalScore["first_leg_home_score"] }}', 10) || 0;
    const firstLegAwayScore = parseInt('{{ $totalScore["first_leg_away_score"] }}', 10) || 0;

    // Verwerk reguliere wedstrijdscores
    const rows = document.querySelectorAll('tbody tr:not(.testmatch-row)');
    if (!rows.length) {
        console.warn('Geen rijen gevonden in de tabel om scores bij te werken.');
        return;
    }

    rows.forEach((row, index) => {
        const homePlayerSelect = row.querySelector(`select[name="scores[${index}][home_player]"]`);
        const awayPlayerSelect = row.querySelector(`select[name="scores[${index}][away_player]"]`);
        const firstMatchInput = row.querySelector(`input[name="scores[${index}][1M]"]`);
        const secondMatchInput = row.querySelector(`input[name="scores[${index}][2M]"]`);
        const belleInput = row.querySelector(`input[name="scores[${index}][Belle]"]`);
        const resultCell = row.querySelector('.result-cell');

        // Valideer of vereiste velden aanwezig zijn
        if (!homePlayerSelect || !awayPlayerSelect || !firstMatchInput || !secondMatchInput || !belleInput) {
            console.warn(`Rij ${index}: Vereiste velden ontbreken. Controleer je HTML-structuur.`);
            return;
        }

        const homePlayer = homePlayerSelect.value;
        const awayPlayer = awayPlayerSelect.value;
        let isMatchComplete = false;
        let result = '';

        if (!homePlayer && !awayPlayer) {
            result = ''; // Geen spelers geselecteerd
        } else if (
            homePlayer === 'forfeit' || 
            awayPlayer === 'forfeit' || 
            (firstMatchInput.value && secondMatchInput.value)
        ) {
            isMatchComplete = true;
        }

        if (!isMatchComplete) {
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

            // Verwerk scores van manches
            if (firstMatchInput.value === '1') homeSetsWon++;
            if (firstMatchInput.value === '2') awaySetsWon++;
            if (secondMatchInput.value === '1') homeSetsWon++;
            if (secondMatchInput.value === '2') awaySetsWon++;

            // Belle mag alleen ingevuld worden als de manches ongelijk zijn
            if (firstMatchInput.value !== secondMatchInput.value) {
                belleInput.removeAttribute('readonly');
            } else {
                belleInput.setAttribute('readonly', true);
                belleInput.value = ''; // Wis bestaande Belle-waarde
            }

            // Verwerk Belle
            if (belleInput.value === '1') homeSetsWon++;
            if (belleInput.value === '2') awaySetsWon++;

            // Bereken resultaten
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

        // Update het resultaat in de UI
        if (resultCell) {
            resultCell.textContent = result;
        }
    });

    // Verwerk testmatch-scores
    document.querySelectorAll('table.testmatch tbody tr').forEach((row, index) => {
        const testMatchResult = row.querySelector(`input[name="testmatch[${index}][1M]"]`)?.value;

        if (testMatchResult === '1') homeScore++;
        if (testMatchResult === '2') awayScore++;
    });

    // Update totale wedstrijdscores in de UI
    const homeScoreInput = document.getElementById('home_score');
    const awayScoreInput = document.getElementById('away_score');

    if (homeScoreInput) homeScoreInput.value = homeScore;
    if (awayScoreInput) awayScoreInput.value = awayScore;

    // Update Total Score sectie
    if (totalScoreContent) {
        const totalHomeScore = firstLegHomeScore + homeScore;
        const totalAwayScore = firstLegAwayScore + awayScore;

        totalScoreContent.innerHTML = `
            <p><strong>Heenwedstrijd:</strong> {{ $game->homeTeam->name }} ${firstLegHomeScore} - ${firstLegAwayScore} {{ $game->awayTeam->name }}</p>
            <hr>
            <h5><strong>Totaal:</strong> {{ $game->homeTeam->name }} ${totalHomeScore} - ${totalAwayScore} {{ $game->awayTeam->name }}</h5>
        `;
    }
}

// Eventlisteners toevoegen voor inputs en select-elementen
document.addEventListener('DOMContentLoaded', () => {
    const rows = document.querySelectorAll('tbody tr');

    // Voeg eventlisteners toe aan inputs voor manches en Belle
    rows.forEach(row => {
        const inputs = row.querySelectorAll('.manche, .belle');
        inputs.forEach(input => {
            input.addEventListener('input', updateResults);
        });
    });

    // Voeg eventlisteners toe aan spelersselecties
    document.querySelectorAll('.player-select').forEach(select => {
        select.addEventListener('change', function () {
            const selectedOption = select.options[select.selectedIndex];
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
});


 function saveFormData() {
    const formElement = document.getElementById('cupMatchForm');

    if (!formElement) {
        console.error('Form element with ID "cupMatchForm" not found.');
        return;
    }

    const formData = new FormData(formElement);
    const data = {};

    // Collect all form data into an object
    formData.forEach((value, key) => {
        data[key] = value;
    });

    try {
        // Save form data and expiry time to localStorage
        localStorage.setItem(`matchFormData_${gameId}`, JSON.stringify(data));
        localStorage.setItem(`matchFormDataExpiry_${gameId}`, Date.now() + EXPIRY_TIME);

        console.log('Form data successfully saved to localStorage.', data);
    } catch (error) {
        console.error('Error saving form data to localStorage:', error);
    }
}


function loadFormData() {
    const savedData = localStorage.getItem(`matchFormData_${gameId}`);
    const expiry = localStorage.getItem(`matchFormDataExpiry_${gameId}`);

    // Als er geldige lokale gegevens zijn, laad deze in
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
        console.log("Local form data loaded.");
    } else {
        localStorage.removeItem(`matchFormData_${gameId}`);
        localStorage.removeItem(`matchFormDataExpiry_${gameId}`);
    }

    // Daarna live score gegevens ophalen en formulier updaten
    fetch(`/cup-games/${gameId}/live-score`)
        .then(response => response.json())
        .then(data => {
            console.log('Live score data fetched during page load:', data);

            // Alleen als er live score data is, overschrijven we mogelijk de opgeslagen gegevens
            if (data) {
                populateForm(data); // Vul het formulier met live score data
                updateResults(); // Bereken de resultaten opnieuw op basis van de live gegevens
                updateTeamNames(); // Werk de teamnamen bij
            }
        })
        .catch(error => console.error('Error fetching live score data:', error));
}

// Function to populate the form fields with live score data
function populateForm(data) {
    console.log('Populating form with data:', data);

    // Update team scores
    const homeScoreInput = document.getElementById('home_score');
    const awayScoreInput = document.getElementById('away_score');

    if (homeScoreInput) {
        homeScoreInput.value = data.home_score ?? 0; // Default to 0 if no home score
    } else {
        console.warn('Home score input field not found.');
    }

    if (awayScoreInput) {
        awayScoreInput.value = data.away_score ?? 0; // Default to 0 if no away score
    } else {
        console.warn('Away score input field not found.');
    }

    // Populate captain and reserve fields
    populateCaptainsAndReserves(data);

    // Update player selections and scores
    if (Array.isArray(data.scores) && data.scores.length > 0) {
        data.scores.forEach((score, index) => {
            const homePlayerInput = document.querySelector(`[name="scores[${index}][home_player]"]`);
            const awayPlayerInput = document.querySelector(`[name="scores[${index}][away_player]"]`);
            const firstMancheInput = document.querySelector(`[name="scores[${index}][1M]"]`);
            const secondMancheInput = document.querySelector(`[name="scores[${index}][2M]"]`);
            const belleInput = document.querySelector(`[name="scores[${index}][Belle]"]`);

            // Populate home player
            if (homePlayerInput) {
                const homePlayerOption = Array.from(homePlayerInput.options).find(option => option.text.includes(score.home_player_name));
                homePlayerInput.value = homePlayerOption ? homePlayerOption.value : '';
            } else {
                console.warn(`Home player input field for index ${index} not found.`);
            }

            // Populate away player
            if (awayPlayerInput) {
                const awayPlayerOption = Array.from(awayPlayerInput.options).find(option => option.text.includes(score.away_player_name));
                awayPlayerInput.value = awayPlayerOption ? awayPlayerOption.value : '';
            } else {
                console.warn(`Away player input field for index ${index} not found.`);
            }

            // Populate score fields
            if (firstMancheInput) {
                firstMancheInput.value = score['1M'] || ''; // Default to empty string
            } else {
                console.warn(`First manche input field for index ${index} not found.`);
            }

            if (secondMancheInput) {
                secondMancheInput.value = score['2M'] || ''; // Default to empty string
            } else {
                console.warn(`Second manche input field for index ${index} not found.`);
            }

            if (belleInput) {
                belleInput.value = score['Belle'] || ''; // Default to empty string
            } else {
                console.warn(`Belle input field for index ${index} not found.`);
            }
        });
    } else {
        console.warn('Scores data is not valid or missing:', data.scores);
    }

    // Update TestMatch scores if present
    if (Array.isArray(data.testmatch_scores) && data.testmatch_scores.length > 0) {
        data.testmatch_scores.forEach((testMatch, index) => {
            const testMatchHomePlayer = document.querySelector(`[name="testmatch[${index}][home_player]"]`);
            const testMatchAwayPlayer = document.querySelector(`[name="testmatch[${index}][away_player]"]`);
            const testMatchResult = document.querySelector(`[name="testmatch[${index}][1M]"]`);

            // Populate test match home player
            if (testMatchHomePlayer) {
                const homeOption = Array.from(testMatchHomePlayer.options).find(option => option.text.includes(testMatch.home_player_name));
                testMatchHomePlayer.value = homeOption ? homeOption.value : '';
            } else {
                console.warn(`Test match home player input field for index ${index} not found.`);
            }

            // Populate test match away player
            if (testMatchAwayPlayer) {
                const awayOption = Array.from(testMatchAwayPlayer.options).find(option => option.text.includes(testMatch.away_player_name));
                testMatchAwayPlayer.value = awayOption ? awayOption.value : '';
            } else {
                console.warn(`Test match away player input field for index ${index} not found.`);
            }

            // Populate test match result
            if (testMatchResult) {
                testMatchResult.value = testMatch['1M'] || ''; // Default to empty string
            } else {
                console.warn(`Test match result input field for index ${index} not found.`);
            }
        });
    } else {
        console.warn('Test match scores data is not valid or missing:', data.testmatch_scores);
    }
}



// Helper function to populate captains and reserves
function populateCaptainsAndReserves(data) {
    const homeCaptainInput = document.querySelector('select[name="home_captain"]');
    const awayCaptainInput = document.querySelector('select[name="away_captain"]');
    const homeReserveInput = document.querySelector('select[name="home_reserve"]');
    const awayReserveInput = document.querySelector('select[name="away_reserve"]');

    // Populate captain and reserve fields
    if (homeCaptainInput && data.home_captain_name) {
        const homeCaptainOption = Array.from(homeCaptainInput.options).find(option => option.text.includes(data.home_captain_name));
        if (homeCaptainOption) homeCaptainInput.value = homeCaptainOption.value;
    }

    if (awayCaptainInput && data.away_captain_name) {
        const awayCaptainOption = Array.from(awayCaptainInput.options).find(option => option.text.includes(data.away_captain_name));
        if (awayCaptainOption) awayCaptainInput.value = awayCaptainOption.value;
    }

    if (homeReserveInput && data.home_reserve_name) {
        const homeReserveOption = Array.from(homeReserveInput.options).find(option => option.text.includes(data.home_reserve_name));
        if (homeReserveOption) homeReserveInput.value = homeReserveOption.value;
    }

    if (awayReserveInput && data.away_reserve_name) {
        const awayReserveOption = Array.from(awayReserveInput.options).find(option => option.text.includes(data.away_reserve_name));
        if (awayReserveOption) awayReserveInput.value = awayReserveOption.value;
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
    console.log("Starting live score update for Game ID:", gameId);

    // Fetch CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        alert("CSRF-token ontbreekt. De score kan niet worden opgeslagen.");
        return;
    }

    // Fetch form fields
    const homeScoreInput = document.getElementById('home_score');
    const awayScoreInput = document.getElementById('away_score');
    const homeCaptainInput = document.querySelector('select[name="home_captain"]');
    const awayCaptainInput = document.querySelector('select[name="away_captain"]');
    const homeReserveInput = document.querySelector('select[name="home_reserve"]');
    const awayReserveInput = document.querySelector('select[name="away_reserve"]');

    // Retrieve values from the form fields
    const homeScore = homeScoreInput ? parseInt(homeScoreInput.value) || 0 : null;
    const awayScore = awayScoreInput ? parseInt(awayScoreInput.value) || 0 : null;
    const homeCaptain = homeCaptainInput?.value || null;
    const awayCaptain = awayCaptainInput?.value || null;
    const homeReserve = homeReserveInput?.value || null;
    const awayReserve = awayReserveInput?.value || null;

    const scores = [];
    const testmatchScores = [];

    // Gather regular match scores
    document.querySelectorAll('table tbody tr:not(.testmatch-row)').forEach((row, index) => {
        const homePlayer = row.querySelector(`select[name="scores[${index}][home_player]"]`)?.value || null;
        const awayPlayer = row.querySelector(`select[name="scores[${index}][away_player]"]`)?.value || null;
        const firstManche = row.querySelector(`input[name="scores[${index}][1M]"]`)?.value || null;
        const secondManche = row.querySelector(`input[name="scores[${index}][2M]"]`)?.value || null;
        const belle = row.querySelector(`input[name="scores[${index}][Belle]"]`)?.value || null;

        if (homePlayer && awayPlayer) {
            scores.push({
                home_player: homePlayer,
                away_player: awayPlayer,
                '1M': firstManche,
                '2M': secondManche,
                Belle: belle,
            });
        } else {
            console.warn(`Row ${index}: Missing home or away player. Skipping row.`);
        }
    });

    // Gather test match scores
    document.querySelectorAll('table.testmatch tbody tr').forEach((row, index) => {
        const homePlayer = row.querySelector(`select[name="testmatch[${index}][home_player]"]`)?.value || null;
        const awayPlayer = row.querySelector(`select[name="testmatch[${index}][away_player]"]`)?.value || null;
        const result = row.querySelector(`input[name="testmatch[${index}][1M]"]`)?.value || null;

        if (homePlayer && awayPlayer && result) {
            testmatchScores.push({
                home_player: homePlayer,
                away_player: awayPlayer,
                '1M': result,
            });
        } else {
            console.warn(`Test match row ${index}: Missing player or result. Skipping row.`);
        }
    });

    // Validate payload
    if (!gameId || homeScore === null || awayScore === null) {
        alert("Vul alle verplichte velden in voordat u de wedstrijd opslaat.");
        return;
    }

    if (scores.length === 0) {
        alert("Geen geldige wedstrijdscores gevonden. Controleer de invoer.");
        return;
    }

    // Prepare payload
    const payload = {
        game_id: gameId,
        home_score: homeScore,
        away_score: awayScore,
        home_captain: homeCaptain,
        away_captain: awayCaptain,
        home_reserve: homeReserve,
        away_reserve: awayReserve,
        scores,
        testmatch: testmatchScores,
    };

    console.log("Payload ready to send:", payload);

    // Send payload to server
    fetch(`/cup-games/${gameId}/update-live-score`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload),
    })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || `HTTP error! Status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log("Live score updated successfully:", data);
                alert("De live score is succesvol opgeslagen!");
            } else {
                console.error("Server-side validation failed:", data);
                alert("Fout opgetreden bij het opslaan van de live score. Controleer de invoer.");
            }
        })
        .catch(error => {
            console.error("Error updating live score:", error);
            alert("Er is een fout opgetreden bij het opslaan van de live score. Probeer het opnieuw.");
        });
}



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

    /**
     * Werk de Total Score bij
     */
     function updateTotalScore() {
        if (!homeScoreInput || !awayScoreInput || !totalScoreContent) return;

        const currentHomeScore = parseInt(homeScoreInput.value, 10) || 0;
        const currentAwayScore = parseInt(awayScoreInput.value, 10) || 0;

        // Totale scores berekenen
        const totalHomeScore = firstLegHomeScore + currentHomeScore;
        const totalAwayScore = firstLegAwayScore + currentAwayScore;

        // Update Total Score sectie
        totalScoreContent.innerHTML = `
            <p><strong>Heenwedstrijd:</strong> {{ $game->homeTeam->name }} ${firstLegHomeScore} - ${firstLegAwayScore} {{ $game->awayTeam->name }}</p>
            <hr>
            <h5><strong>Totaal:</strong> {{ $game->homeTeam->name }} ${totalHomeScore} - ${totalAwayScore} {{ $game->awayTeam->name }}</h5>
        `;
    }

    function updateTestMatchScores() {
    const homeScoreInput = document.getElementById('home_score');
    const awayScoreInput = document.getElementById('away_score');

    if (!homeScoreInput || !awayScoreInput) {
        console.warn('Score inputs not found.');
        return;
    }

    let homeScore = parseInt(homeScoreInput.dataset.initialValue) || 0;
    let awayScore = parseInt(awayScoreInput.dataset.initialValue) || 0;

    document.querySelectorAll('table.testmatch tbody tr').forEach((row, index) => {
        const testMatchInput = row.querySelector(`input[name="testmatch[${index}][1M]"]`);

        if (testMatchInput && testMatchInput.value) {
            const winner = parseInt(testMatchInput.value);

            if (winner === 1) {
                homeScore += 1; // Thuisploeg krijgt 1 punt
            } else if (winner === 2) {
                awayScore += 1; // Uitploeg krijgt 1 punt
            }
        }
    });

    homeScoreInput.value = homeScore;
    awayScoreInput.value = awayScore;
}

// Eventlistener toevoegen
document.querySelectorAll('input[name^="testmatch"]').forEach(input => {
    input.addEventListener('change', updateTestMatchScores);
});

// Opslaan van initiële score in dataset
document.addEventListener('DOMContentLoaded', () => {
    const homeScoreInput = document.getElementById('home_score');
    const awayScoreInput = document.getElementById('away_score');

    if (homeScoreInput) {
        homeScoreInput.dataset.initialValue = homeScoreInput.value || 0;
    }
    if (awayScoreInput) {
        awayScoreInput.dataset.initialValue = awayScoreInput.value || 0;
    }
});




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
// Eventlistener toevoegen voor dynamische updates
    homeScoreInput.addEventListener('input', updateTotalScore);
    awayScoreInput.addEventListener('input', updateTotalScore);

    // Initialiseer bij laden van de pagina
    updateTotalScore();

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