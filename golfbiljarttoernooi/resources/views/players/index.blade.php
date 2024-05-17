@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4"><i class="fas fa-users"></i> Spelerslijst</h1>

    <!-- Search input at the top -->
    <div class="mb-3">
        <label for="searchInput" class="form-label"><i class="fas fa-search"></i> Zoek spelers:</label>
        <input type="text" id="searchInput" class="form-control" placeholder="Voer spelernaam in...">
    </div>

    <!-- Division selection -->
    <div class="mb-3">
        <label for="divisionSelect" class="form-label"><i class="fas fa-layer-group"></i> Kies een divisie:</label>
        <select id="divisionSelect" class="form-select form-control">
            <option selected>Kies een divisie</option>
            @foreach ($divisions as $division)
                <option value="{{ $division->id }}">{{ $division->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Team selection -->
    <div class="mb-3">
        <label for="teamSelect" class="form-label"><i class="fas fa-users-cog"></i> Kies een team:</label>
        <select id="teamSelect" class="form-select form-control" disabled>
            <option selected>Kies eerst een divisie</option>
        </select>
    </div>

    <!-- Player list -->
    <div id="playerList" class="table-responsive">
        <!-- Spelerslijst komt hier -->
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const divisionSelect = document.getElementById('divisionSelect');
    const teamSelect = document.getElementById('teamSelect');
    const searchInput = document.getElementById('searchInput');

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

        fetch(`/get-players-by-team?team_id=${selectedTeamId}`)
            .then(response => response.json())
            .then(players => {
                let rows = players.map(player => `
                    <tr>
                        <td><a href="/players/${player.id}">${player.name}</a></td>
                    </tr>
                `).join('');

                document.getElementById('playerList').innerHTML = `
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><i class="fas fa-user"></i> Naam</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rows}
                        </tbody>
                    </table>
                `;
            });
    };

    searchInput.oninput = function() {
        const query = this.value;

        fetch(`/search-players?query=${query}`)
            .then(response => response.json())
            .then(players => {
                let rows = players.map(player => `
                    <tr>
                        <td><a href="/players/${player.id}">${player.name}</a></td>
                        <td><a href="/teams/${player.team_id}">${player.team_name || ''}</a></td>
                    </tr>
                `).join('');

                document.getElementById('playerList').innerHTML = `
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><i class="fas fa-user"></i> Naam</th>
                                <th><i class="fas fa-users"></i> Team</th>
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
