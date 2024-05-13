@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Wedstrijden Overzicht</h1>
    
    <!-- Dropdowns for selecting season and division -->
    <div class="row mb-4">
        <div class="col-md-6">
            <select id="season-select" class="form-control" onchange="updateGamesList()">
                @foreach ($seasons as $season)
                    <option value="{{ $season->id }}">{{ $season->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <select id="division-select" class="form-control" onchange="updateGamesList()">
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Table for displaying games -->
    <table class="table">
        <thead>
            <tr>
                <th>Datum</th>
                <th>Thuis Team</th>
                <th>Uit Team</th>
                <th>Uitslag</th>
            </tr>
        </thead>
        <tbody id="games-list">
            <!-- Games will be loaded here using JavaScript -->
        </tbody>
    </table>
</div>

<script>
function updateGamesList() {
    const seasonId = document.getElementById('season-select').value;
    const divisionId = document.getElementById('division-select').value;
    
    // Assuming there's a route named 'games.list' that accepts seasonId and divisionId as query parameters
    fetch(`/games/list?season_id=${seasonId}&division_id=${divisionId}`)
        .then(response => response.json())
        .then(data => {
            const gamesList = document.getElementById('games-list');
            gamesList.innerHTML = '';
            data.forEach(game => {
                gamesList.innerHTML += `
                    <tr>
                        <td>${game.date}</td>
                        <td>${game.homeTeam ? game.homeTeam.name : 'Bye'}</td>
                        <td>${game.awayTeam ? game.awayTeam.name : 'Bye'}</td>
                        <td>${game.played ? `${game.home_score} : ${game.away_score}` : 'Nog te spelen'}</td>
                    </tr>
                `;
            });
        })
        .catch(error => console.error('Error loading games:', error));
}
</script>
@endsection
