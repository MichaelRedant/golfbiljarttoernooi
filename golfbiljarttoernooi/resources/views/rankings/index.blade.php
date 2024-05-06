@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Rankingen</h1>

    <!-- Seizoenskeuze Dropdown -->
    <div class="mb-3">
        <label for="seasonSelect" class="form-label">Kies een seizoen:</label>
        <select id="seasonSelect" class="form-control">
            @foreach($seasons as $season)
                <option value="{{ $season->id }}">{{ $season->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Divisiekeuze Dropdown -->
    <div class="mb-3">
        <label for="divisionSelect" class="form-label">Kies een divisie:</label>
        <select id="divisionSelect" class="form-control">
            <!-- Divisies worden dynamisch ingeladen -->
        </select>
    </div>

    <!-- Placeholder voor rankings -->
    <div id="rankingsContainer"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Laad divisies bij het laden van de pagina
    fetch('/api/divisions')
        .then(response => response.json())
        .then(divisions => {
            const divisionSelect = document.getElementById('divisionSelect');
            divisionSelect.innerHTML = '<option value="">Selecteer een divisie</option>' + 
                divisions.map(division => `<option value="${division.id}">${division.name}</option>`).join('');
        });

    // Event listener voor veranderingen in seizoenselectie
    document.getElementById('seasonSelect').addEventListener('change', updateRankings);

    // Event listener voor veranderingen in divisieselectie
    document.getElementById('divisionSelect').addEventListener('change', updateRankings);

    function updateRankings() {
        var divisionId = document.getElementById('divisionSelect').value;
        var seasonId = document.getElementById('seasonSelect').value;
        if(divisionId && seasonId) {
            fetch(`/api/rankings/${divisionId}/season/${seasonId}`)
                .then(response => response.json())
                .then(rankings => displayRankings(rankings));
        }
    }

    function displayRankings(rankings) {
        let container = document.getElementById('rankingsContainer');
        if (rankings.length > 0) {
            let html = '<table class="table"><thead><tr><th>Team</th><th>Gewonnen</th><th>Verloren</th><th>Gelijk</th><th>Punten</th></tr></thead><tbody>';
            rankings.forEach(team => {
                html += `<tr><td>${team.team_name}</td><td>${team.games_won}</td><td>${team.games_lost}</td><td>${team.games_draw}</td><td>${team.points}</td></tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p>Geen gegevens beschikbaar voor deze divisie en seizoen.</p>';
        }
    }
});
</script>
@endsection
