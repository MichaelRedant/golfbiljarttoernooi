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
                <option value="home">{{ $game->homeTeam->name }}</option>
                <option value="away">{{ $game->awayTeam->name }}</option>
            </select>
        </div>

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
                    <div class="col-md-6">
                        <label>Kapitein {{ $game->homeTeam->name }}</label>
                        <select class="form-control player-select" name="home_captain">
                            <option value="">Kies Kapitein</option>
                            @foreach ($homeTeamPlayers as $player)
                                <option value="{{ $player->id }}" {{ $player->id == optional($game->homeTeam->captain)->id ? 'selected' : '' }}>
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
                                <option value="{{ $player->id }}" {{ $player->id == optional($game->awayTeam->captain)->id ? 'selected' : '' }}>
                                    {{ $player->first_name }} {{ $player->last_name }} - {{ $player->team->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Reservespeler {{ $game->homeTeam->name }}</label>
                        <select class="form-control player-select" name="home_reserve">
                            <option value="">Kies Reservespeler</option>
                            @foreach ($homeTeamPlayers as $player)
                                <option value="{{ $player->id }}" {{ $player->id == optional($game->homeTeam->reserve)->id ? 'selected' : '' }}>
                                    {{ $player->first_name }} {{ $player->last_name }} - {{ $player->team->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Reservespeler {{ $game->awayTeam->name }}</label>
                        <select class="form-control player-select" name="away_reserve">
                            <option value="">Kies Reservespeler</option>
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
                            @for ($i = 0; $i < 6; $i++)
                            <tr>
                                <td>
                                    <select class="form-control player-select wide-select" data-player-type="home" data-row-index="{{ $i }}" name="scores[{{ $i }}][home_player]">
                                        <option value="">-- Selecteer een teamlid --</option>
                                        @foreach ($homeTeamPlayers as $player)
                                        <option value="{{ $player->id }}">{{ $player->first_name }} {{ $player->last_name }} - {{ $player->team->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="small" id="home-team-{{ $i }}"></td>
                                <td>
                                    <select class="form-control player-select wide-select" data-player-type="away" data-row-index="{{ $i }}" name="scores[{{ $i }}][away_player]">
                                        <option value="">-- Selecteer een teamlid --</option>
                                        @foreach ($awayTeamPlayers as $player)
                                        <option value="{{ $player->id }}">{{ $player->first_name }} {{ $player->last_name }} - {{ $player->team->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="small" id="away-team-{{ $i }}"></td>
                                <td>
                                    <input type="number" class="form-control manche" name="scores[{{ $i }}][1M]" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control manche" name="scores[{{ $i }}][2M]" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control belle" name="scores[{{ $i }}][Belle]" readonly>
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

        <div class="text-center mt-4 mb-4">
            @if(auth()->user()->team_id == $game->away_team_id || auth()->user()->role == 'admin')
                <form action="{{ route('games.approve', $game) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success">Goedkeuren</button>
                </form>
            @endif
            <div class="text-center mt-4 mb-4">
                @if(auth()->user()->team_id == $game->away_team_id || auth()->user()->role == 'admin')
                    <a href="{{ route('games.requestApproval', $game->id) }}" class="btn btn-warning">Goedkeuring aanvragen</a>
                @endif
                <button type="submit" class="btn btn-primary" id="saveButton">Wedstrijd laten goedkeuren</button>
            </div>
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
        
    
        const EXPIRY_TIME = 18000000; // 5 uur in milliseconden
    
        // Load saved form data from localStorage
        loadFormData();
    
        // Save form data on input change
        form.addEventListener('input', function() {
            saveFormData();
            updateLiveScore({{ $game->id }});
        });
    
        // Clear localStorage on form submit
        form.addEventListener('submit', function() {
            localStorage.removeItem('matchFormData');
            localStorage.removeItem('matchFormDataExpiry');
            updateLiveScore({{ $game->id }});
        });
    
        forfeitTeamSelect.addEventListener('change', function() {
            if (this.value === 'home') {
                homeScoreInput.value = 0;
                awayScoreInput.value = 3;
            } else if (this.value === 'away') {
                homeScoreInput.value = 3;
                awayScoreInput.value = 0;
            }
            saveButton.disabled = false;
            updateLiveScore({{ $game->id }});
        });
    
        function updateAvailableOptions() {
            let selectedPlayers = [];
    
            // Verzamel geselecteerde spelers alleen uit de sectie "Spelers en Scores"
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
    
            // Update alleen de opties in de sectie "Spelers en Scores"
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
                updateAvailableOptions();
            });
        });
    
        function updateLiveScore(gameId) {
    const homeScore = document.getElementById('home_score').value;
    const awayScore = document.getElementById('away_score').value;
    
    const homeCaptain = document.querySelector('select[name="home_captain"]').value;
    const awayCaptain = document.querySelector('select[name="away_captain"]').value;
    const homeReserve = document.querySelector('select[name="home_reserve"]').value;
    const awayReserve = document.querySelector('select[name="away_reserve"]').value;

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

    // Voeg een console.log toe om de verzonden data te controleren
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
            scores: scores
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


    
        // Save form data to localStorage with expiry
        function saveFormData() {
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            localStorage.setItem('matchFormData', JSON.stringify(data));
            localStorage.setItem('matchFormDataExpiry', Date.now() + EXPIRY_TIME);
        }
    
        // Load form data from localStorage with expiry check
        function loadFormData() {
            const savedData = localStorage.getItem('matchFormData');
            const expiry = localStorage.getItem('matchFormDataExpiry');
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
            } else {
                localStorage.removeItem('matchFormData');
                localStorage.removeItem('matchFormDataExpiry');
            }
        }
    
        updateResults();
        updateAvailableOptions();
    });
    
    </script>
    

@endsection
