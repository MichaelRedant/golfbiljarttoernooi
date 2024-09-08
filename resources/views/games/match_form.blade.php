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

    <form id="matchForm" action="{{ route('games.update', $game->id) }}" method="POST" target="hiddenIframe">
        @csrf
        @method('PUT')

         <!-- Hidden iframe to handle polling without page reload -->
         <iframe id="hiddenIframe" name="hiddenIframe" style="display:none;"></iframe>

        <input type="hidden" name="home_team_id" value="{{ $game->homeTeam->id }}">
        <input type="hidden" name="away_team_id" value="{{ $game->awayTeam->id }}">
        <input type="hidden" name="date" value="{{ $game->date->format('d-m-Y') }}">
        <input type="hidden" name="division_id" value="{{ $game->division_id }}">
        <input type="hidden" name="season_id" value="{{ $game->season_id }}">

        <div class="form-group">
            <label for="forfeit_team">Dit team geeft forfait:</label>
            <select class="form-control" id="forfeit_team" name="forfeit_team">
                <option value="">Selecteer team</option>
                <option value="home">{{ $game->homeTeam->name }}</option>
                <option value="away">{{ $game->awayTeam->name }}</option>
            </select>
        </div>

        <div id="scoreSection">
            <div class="card">
                <div class="card-header">Wedstrijdscore</div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Thuis score:</label>
                        <input type="text" class="form-control" id="home_score" name="home_score" value="{{ $game->home_score ?? '0' }}" readonly>
                    </div>
                    <div class="form-group">
                        <label>Uit score:</label>
                        <input type="text" class="form-control" id="away_score" name="away_score" value="{{ $game->away_score ?? '0' }}" readonly>
                    </div>
                </div>
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

            <div class="card">
                <div class="card-header">Spelers en Scores</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
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
                                @for ($i = 0; $i < 6; $i++)
                                <tr>
                                    <td>
                                        <select class="form-control player-select wide-select" data-player-type="home" data-row-index="{{ $i }}" name="scores[{{ $i }}][home_player]">
                                            <option value="">Speler</option>
                                            @foreach ($homeTeamPlayers as $player)
                                            <option value="{{ $player->id }}">{{ $player->first_name }} {{ $player->last_name }} - {{ $player->team->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="small" id="home-team-{{ $i }}"></td>
                                    <td>
                                        <select class="form-control player-select wide-select" data-player-type="away" data-row-index="{{ $i }}" name="scores[{{ $i }}][away_player]">
                                            <option value="">Speler</option>
                                            @foreach ($awayTeamPlayers as $player)
                                            <option value="{{ $player->id }}">{{ $player->first_name }} {{ $player->last_name }} - {{ $player->team->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="small" id="away-team-{{ $i }}"></td>
                                    <td>
                                        <input type="text" class="form-control manche" name="scores[{{ $i }}][1M]" maxlength="1" pattern="[12]" required>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control manche" name="scores[{{ $i }}][2M]" maxlength="1" pattern="[12]" required>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control belle" name="scores[{{ $i }}][Belle]" maxlength="1" pattern="[12]" readonly>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control result" readonly>
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
            <button type="submit" class="btn btn-primary btn-lg btn-block" id="saveButton">Wedstrijd laten goedkeuren</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('matchForm');
    const rows = document.querySelectorAll('tbody tr');
    const homeScoreInput = document.getElementById('home_score');
    const awayScoreInput = document.getElementById('away_score');
    const saveButton = document.getElementById('saveButton');
    const forfeitTeamSelect = document.getElementById('forfeit_team');
    const playerSelects = document.querySelectorAll('.player-select');
    const scoreTable = document.querySelector('.table-responsive');
    const captainReserveSection = document.querySelector('.card:nth-child(3)'); 
    const playersAndScoresSection = document.querySelector('.card:nth-child(4)'); 
    const gameId = {{ $game->id }}; 
    const EXPIRY_TIME = 32400000; 
    const pollingInterval = 10000; // Poll every 10 seconds
 
    loadFormData();
    
    form.addEventListener('input', function() {
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

    document.getElementById('matchForm').addEventListener('submit', function() {
    alert('Wedstrijd succesvol bijgewerkt!');
});

    function updateAvailableOptions() {
        let selectedPlayers = [];

        rows.forEach(row => {
            const homePlayerSelect = row.querySelector('select[name*="[home_player]"]');
            const awayPlayerSelect = row.querySelector('select[name*="[away_player]"]');

            if (homePlayerSelect && homePlayerSelect.value) {
                selectedPlayers.push(homePlayerSelect.value);
            }
            if (awayPlayerSelect && awayPlayerSelect.value) {
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

        if (manche1Input.value && manche2Input.value) {
            if (parseInt(manche1Input.value) === 1) homePoints++;
            if (parseInt(manche2Input.value) === 2) awayPoints++;
            if (parseInt(manche1Input.value) === 2) awayPoints++;
            if (parseInt(manche2Input.value) === 1) homePoints++;

            if (homePoints === awayPoints) {
                belleInput.removeAttribute('readonly');
                if (belleInput.value) {
                    if (parseInt(belleInput.value) === 1) homePoints++;
                    if (parseInt(belleInput.value) === 2) awayPoints++;
                }
            } else {
                belleInput.setAttribute('readonly', true);
                belleInput.value = ""; 
            }

            resultInput.value = `${homePoints} - ${awayPoints}`;

            if (homePoints > awayPoints) homeWins++;
            if (awayPoints > homePoints) awayWins++;
        } else {
            resultInput.value = "";
        }
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
            updateAvailableOptions();
            updateTeamNames(); 
        });
    });

    function updateLiveScore(gameId) {
        const homeScore = document.getElementById('home_score').value;
        const awayScore = document.getElementById('away_score').value;
        
        const homeCaptain = document.querySelector('select[name="home_captain"]').value;
        const awayCaptain = document.querySelector('select[name="away_captain"]').value;
        const homeReserve = document.querySelector('select[name="home_reserve"]').value;
        const awayReserve = document.querySelector('select[name="away_reserve"]').value;

        const forfeitTeam = document.getElementById('forfeit_team').value;

        let scores = [];
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
                '1M': firstManche || 'N/A',
                '2M': secondManche || 'N/A',
                Belle: belle || 'N/A'
            });
        });

        console.log('Data sent to server:', {
            home_score: homeScore,
            away_score: awayScore,
            home_captain: homeCaptain,
            away_captain: awayCaptain,
            home_reserve: homeReserve,
            away_reserve: awayReserve,
            scores: scores
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
            console.log('Server response:', data);
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

// Function to populate the form fields with live score data
function populateForm(data) {
    console.log('Populating form with data:', data); // Log the received data

    // Populate standard form fields
    for (const key in data) {
        const element = form.querySelector(`[name="${key}"]`);
        if (element) {
            console.log(`Populating field ${key} with value:`, data[key]); // Log each populated field
            element.value = data[key];
        }
    }

    // Populate the scores array (if exists in the data)
    if (data.scores) {
        data.scores.forEach((score, index) => {
            const homePlayerInput = form.querySelector(`[name="scores[${index}][home_player]"]`);
            const awayPlayerInput = form.querySelector(`[name="scores[${index}][away_player]"]`);
            const firstMancheInput = form.querySelector(`[name="scores[${index}][1M]"]`);
            const secondMancheInput = form.querySelector(`[name="scores[${index}][2M]"]`);
            const belleInput = form.querySelector(`[name="scores[${index}][Belle]"]`);
            // Syncing captains and reserves
            const homeCaptainInput = form.querySelector('select[name="home_captain"]');
            const awayCaptainInput = form.querySelector('select[name="away_captain"]');
            const homeReserveInput = form.querySelector('select[name="home_reserve"]');
            const awayReserveInput = form.querySelector('select[name="away_reserve"]');



            console.log(`Populating score row ${index} with data:`, score);

             // Populate home player
             if (homePlayerInput && score.home_player_name) {
                const homePlayerOption = Array.from(homePlayerInput.options).find(option => option.text.includes(score.home_player_name));
                if (homePlayerOption) {
                    homePlayerInput.value = homePlayerOption.value;
                    console.log(`Setting home player for row ${index}: ${score.home_player_name}`);
                }
            }

            // Populate away player
            if (awayPlayerInput && score.away_player_name) {
                const awayPlayerOption = Array.from(awayPlayerInput.options).find(option => option.text.includes(score.away_player_name));
                if (awayPlayerOption) {
                    awayPlayerInput.value = awayPlayerOption.value;
                    console.log(`Setting away player for row ${index}: ${score.away_player_name}`);
                }
            }

            // Populate home captain
    if (homeCaptainInput && data.home_captain_name) {
        const homeCaptainOption = Array.from(homeCaptainInput.options).find(option => option.text.includes(data.home_captain_name));
        if (homeCaptainOption) {
            homeCaptainInput.value = homeCaptainOption.value;
            console.log(`Setting home captain: ${data.home_captain_name}`);
        }
    }

    // Populate away captain
    if (awayCaptainInput && data.away_captain_name) {
        const awayCaptainOption = Array.from(awayCaptainInput.options).find(option => option.text.includes(data.away_captain_name));
        if (awayCaptainOption) {
            awayCaptainInput.value = awayCaptainOption.value;
            console.log(`Setting away captain: ${data.away_captain_name}`);
        }
    }

    // Populate home reserve
    if (homeReserveInput && data.home_reserve_name) {
        const homeReserveOption = Array.from(homeReserveInput.options).find(option => option.text.includes(data.home_reserve_name));
        if (homeReserveOption) {
            homeReserveInput.value = homeReserveOption.value;
            console.log(`Setting home reserve: ${data.home_reserve_name}`);
        }
    }

    // Populate away reserve
    if (awayReserveInput && data.away_reserve_name) {
        const awayReserveOption = Array.from(awayReserveInput.options).find(option => option.text.includes(data.away_reserve_name));
        if (awayReserveOption) {
            awayReserveInput.value = awayReserveOption.value;
            console.log(`Setting away reserve: ${data.away_reserve_name}`);
        }
    }

            // Populate the first and second manche inputs
            if (firstMancheInput) firstMancheInput.value = score['1M'] || '';
            if (secondMancheInput) secondMancheInput.value = score['2M'] || '';

            // Populate the belle input if available
            if (belleInput) belleInput.value = score.Belle || '';
        });
    }
}



// Fetch saved live score data and populate the form
fetch(`/games/${gameId}/live-score`)
    .then(response => response.json())
    .then(data => {
        console.log('Fetched live score data:', data); // Log the fetched data
        if (data) {
            populateForm(data); // Call the populateForm function with fetched data
            updateTeamNames(); // Update team names after form is populated
            updateAvailableOptions(); // Ensure options are updated after form is populated
        }
    })
    .catch(error => console.error('Error fetching live score data:', error));



// Handle form submission to save live score data
form.addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the form from submitting normally

    const formData = new FormData(form); // Create form data object
    const formDataObject = {};

    // Convert FormData to a plain object
    formData.forEach((value, key) => {
        formDataObject[key] = value;
    });

    // Send form data to the server to save in LiveScore
    fetch(`/games/${gameId}/live-score/update`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}' // Include CSRF token for security
        },
        body: JSON.stringify(formDataObject) // Convert form data to JSON string
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Live score saved successfully!');
        }
    })
    .catch(error => console.error('Error saving live score data:', error));
});



function updateTeamNames() {
    document.querySelectorAll('.player-select').forEach(select => {
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const teamName = selectedOption.text.split(' - ')[1] || 'Geen team gevonden';
            const playerType = select.dataset.playerType;
            const rowIndex = select.dataset.rowIndex;
            const teamCell = document.getElementById(`${playerType}-team-${rowIndex}`);
            if (teamCell) {
                console.log(`Updating team cell for row ${rowIndex}, ${playerType}: ${teamName}`); // Log team name updates
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
