@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1>Maak Wedstrijd voor Beker: {{ $cup->name }}</h1>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="form-group mb-3">
        <label for="round_id"><i class="fas fa-trophy"></i> Kies een ronde:</label>
        <select id="round_id" name="round_id" class="form-control" required>
            <option value="" selected>Selecteer een ronde</option>
            @foreach($cup->rounds as $round)
                @if (str_contains($round->round_name, '1/8') || str_contains($round->round_name, '1/4') || str_contains($round->round_name, 'Halve Finale') || str_contains($round->round_name, 'Finale'))
                    @if (str_contains($round->round_name, 'Heenwedstrijd'))
                        <option value="{{ $round->id }}">{{ $round->round_name }}</option>
                    @elseif (!str_contains($round->round_name, 'Terugwedstrijd'))
                        <option value="{{ $round->id }}">{{ $round->round_name }}</option>
                    @endif
                @endif
            @endforeach
        </select>
    </div>

    <div class="form-group mb-3">
        <label for="division_id"><i class="fas fa-layer-group"></i> Kies een divisie:</label>
        <select id="division_id" name="division_id" class="form-control" disabled>
            <option value="{{ $cup->division_id }}" selected>{{ $cup->division->name }}</option>
        </select>
    </div>

    <div class="form-group mb-3">
        <label for="season_id"><i class="fas fa-calendar-alt"></i> Kies een seizoen:</label>
        <select id="season_id" name="season_id" class="form-control" disabled>
            <option value="{{ $cup->season_id }}" selected>{{ $cup->season->name }}</option>
        </select>
    </div>

    <div class="form-group mb-3" id="homeTeamGroup">
        <label for="home_team_id"><i class="fas fa-home"></i> Thuis Team:</label>
        <select id="home_team_id" name="home_team_id" class="form-control">
            <option value="" selected>Selecteer een team</option>
        </select>
    </div>

    <div class="form-group mb-3" id="awayTeamGroup">
        <label for="away_team_id"><i class="fas fa-plane"></i> Uit Team:</label>
        <select id="away_team_id" name="away_team_id" class="form-control">
            <option value="" selected>Selecteer een team</option>
        </select>
    </div>

    <div class="form-group mb-3">
        <label for="bye_team_id"><i class="fas fa-user-slash"></i> Bye (Vrij team):</label>
        <select name="bye_team_id" id="bye_team_id" class="form-control" onchange="toggleTeamAndDateVisibility(this)">
            <option value="" selected>Geen Vrij</option>
        </select>
    </div>

    <div class="form-group mb-3" id="gameDateGroup">
        <label for="game_date"><i class="fas fa-calendar-day"></i> Datum van de wedstrijd:</label>
        <input type="date" id="game_date" name="game_date" class="form-control" required>
    </div>
    
    <div class="form-group mb-3" id="returnGameDateWrapper">
        <label for="return_date"><i class="fas fa-calendar-day"></i> Datum van de terugwedstrijd:</label>
        <input type="date" id="return_date" name="return_date" class="form-control">
    </div>

    <div class="form-group mb-3">
        <button type="button" class="btn btn-primary" id="create-game-btn"><i class="fas fa-plus"></i> Maak wedstrijd</button>
        <button type="button" class="btn btn-secondary" id="create-game-and-next-btn"><i class="fas fa-plus-circle"></i> Maak volgende wedstrijd</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const divisionId = "{{ $cup->division_id }}";
        const seasonId = "{{ $cup->season_id }}";
        fetchTeams(divisionId, seasonId);

        const byeTeamSelect = document.getElementById('bye_team_id');
        const roundSelect = document.getElementById('round_id');
        const returnGameDateWrapper = document.getElementById('returnGameDateWrapper');

        // Verberg terugwedstrijd-veld standaard
        returnGameDateWrapper.style.display = 'none';
        
        // Luister naar veranderingen in het select-veld van de ronde
        roundSelect.addEventListener('change', function() {
            const selectedRound = roundSelect.options[roundSelect.selectedIndex].text;
            
            // Toon terugwedstrijd-veld alleen voor 1/8 en 1/4 finales
            if (selectedRound.includes('1/8') || selectedRound.includes('1/4')) {
                returnGameDateWrapper.style.display = 'block';
            } else {
                returnGameDateWrapper.style.display = 'none';
            }
        });

        document.getElementById('home_team_id').addEventListener('change', filterAwayTeams);
        document.getElementById('away_team_id').addEventListener('change', filterHomeTeams);
    });

    function fetchTeams(divisionId, seasonId) {
        const homeTeamSelect = document.getElementById('home_team_id');
        const awayTeamSelect = document.getElementById('away_team_id');
        const byeTeamSelect = document.getElementById('bye_team_id');

        homeTeamSelect.innerHTML = '<option value="" selected>Selecteer een team</option>';
        awayTeamSelect.innerHTML = '<option value="" selected>Selecteer een team</option>';
        byeTeamSelect.innerHTML = '<option value="" selected>Geen Vrij</option>';

        if (divisionId && seasonId) {
            fetch(`/api/divisions/${divisionId}/teams`)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        alert('Geen teams gevonden voor de geselecteerde divisie en seizoen.');
                    } else {
                        data.forEach(team => {
                            const option = document.createElement('option');
                            option.value = team.id;
                            option.textContent = team.name;
                            homeTeamSelect.appendChild(option.cloneNode(true));
                            awayTeamSelect.appendChild(option.cloneNode(true));
                            byeTeamSelect.appendChild(option.cloneNode(true));
                        });
                    }
                })
                .catch(error => console.error('Fout bij het ophalen van teams:', error));
        }
    }

    function filterAwayTeams() {
        const homeTeamId = document.getElementById('home_team_id').value;
        const awayTeamSelect = document.getElementById('away_team_id');

        Array.from(awayTeamSelect.options).forEach(option => {
            option.disabled = (option.value === homeTeamId);
        });
    }

    function filterHomeTeams() {
        const awayTeamId = document.getElementById('away_team_id').value;
        const homeTeamSelect = document.getElementById('home_team_id');

        Array.from(homeTeamSelect.options).forEach(option => {
            option.disabled = (option.value === awayTeamId);
        });
    }

    // Verberg team- en datumvelden als een "Vrij" team geselecteerd is
    function toggleTeamAndDateVisibility(select) {
        const isByeSelected = select.value !== "";
        const homeTeamGroup = document.getElementById('homeTeamGroup');
        const awayTeamGroup = document.getElementById('awayTeamGroup');
        const gameDateGroup = document.getElementById('gameDateGroup');
        const returnGameDateWrapper = document.getElementById('returnGameDateWrapper');

        homeTeamGroup.style.display = isByeSelected ? 'none' : 'block';
        awayTeamGroup.style.display = isByeSelected ? 'none' : 'block';
        gameDateGroup.style.display = isByeSelected ? 'none' : 'block';
        returnGameDateWrapper.style.display = isByeSelected ? 'none' : 'block';
    }

    document.getElementById('create-game-btn').addEventListener('click', function() {
        createGame(false);
    });

    document.getElementById('create-game-and-next-btn').addEventListener('click', function() {
        createGame(true);
    });

    function createGame(andNext) {
        const cupId = {{ $cup->id }};
        const roundId = document.getElementById('round_id').value;
        const homeTeamId = document.getElementById('home_team_id').value;
        const awayTeamId = document.getElementById('away_team_id').value;
        const gameDate = document.getElementById('game_date').value;
        const returnDate = document.getElementById('return_date').value;
        const byeTeamId = document.getElementById('bye_team_id').value;

        // Als "Vrij" team is geselecteerd, zijn alleen het bye_team_id en round_id nodig
        if (byeTeamId) {
            const data = {
                round_id: roundId,
                bye_team_id: byeTeamId,
                _token: '{{ csrf_token() }}'
            };

            sendGameRequest(cupId, data, andNext);
            return;
        }

        // Voor een reguliere wedstrijd zijn alle teams en data nodig
        if (!roundId || !homeTeamId || !awayTeamId || !gameDate) {
            alert('Selecteer een ronde, een thuis- en uitteam en vul de datum van de wedstrijd in.');
            return;
        }

        if (homeTeamId === awayTeamId) {
            alert('Thuis- en uitteam mogen niet hetzelfde zijn.');
            return;
        }

        const data = {
            round_id: roundId,
            home_team_id: homeTeamId,
            away_team_id: awayTeamId,
            date: gameDate,
            return_date: returnDate,
            _token: '{{ csrf_token() }}'
        };

        sendGameRequest(cupId, data, andNext);
    }

    function sendGameRequest(cupId, data, andNext) {
        fetch(`/cups/${cupId}/games`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Wedstrijd succesvol toegevoegd.');
                if (andNext) {
                    document.getElementById('home_team_id').value = '';
                    document.getElementById('away_team_id').value = '';
                    document.getElementById('game_date').value = ''; 
                    document.getElementById('return_date').value = '';
                    filterAwayTeams();
                    filterHomeTeams();
                } else {
                    window.location.href = `/cups/${cupId}/show`;
                }
            } else {
                alert('Er is een fout opgetreden: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Fout bij het aanmaken van de wedstrijd:', error);
            alert('Er is een fout opgetreden bij het toevoegen van de wedstrijd.');
        });
    }
</script>
@endsection
