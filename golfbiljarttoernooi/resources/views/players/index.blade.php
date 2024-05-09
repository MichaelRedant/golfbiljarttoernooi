@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Spelerslijst</h1>

    <div class="mb-3">
        <label for="divisionSelect" class="form-label">Kies een divisie:</label>
        <select id="divisionSelect" class="form-select form-control" aria-label="Division select">
            <option selected>Kies een divisie</option>
            @foreach ($divisions as $division)
                <option value="{{ $division->id }}">{{ $division->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label for="teamSelect" class="form-label">Kies een team:</label>
        <select id="teamSelect" class="form-select form-control" aria-label="Team select" disabled>
            <option selected>Kies eerst een divisie</option>
            <!-- Teams worden hier dynamisch ingeladen -->
        </select>
    </div>

    <div id="playerList">
        <!-- Spelerslijst komt hier -->
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const divisionSelect = document.getElementById('divisionSelect');
    const teamSelect = document.getElementById('teamSelect');
    const addPlayerButton = document.getElementById('addPlayerButton');

    divisionSelect.onchange = function() {
        fetch(`/get-teams?division_id=${this.value}`)
            .then(response => response.json())
            .then(data => {
                teamSelect.disabled = false;
                teamSelect.innerHTML = '<option value="">Selecteer een team</option>' + 
                    data.map(team => `<option value="${team.id}">${team.name}</option>`).join('');
            });
    };

    teamSelect.onchange = function () {
        const selectedTeamId = this.value;
        const selectedTeamName = teamSelect.options[teamSelect.selectedIndex].text;

        fetch(`/get-players-by-team?team_id=${selectedTeamId}`)
            .then(response => response.json())
            .then(players => {
                let rows = players.map(player => `
                    <tr>
                        <td><a href="/players/${player.id}">${player.name}</a></td>
                    </tr>
                `).join('');

                document.getElementById('playerList').innerHTML = `
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Naam</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rows}
                        </tbody>
                    </table>
                `;
            });
    };
});
</script>
@endsection
