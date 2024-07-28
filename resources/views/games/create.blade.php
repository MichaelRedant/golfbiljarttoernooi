@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1>Nieuwe Wedstrijd Aanmaken</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('games.store') }}" method="POST">
        @csrf

        <div class="form-group mb-3">
            <label for="division_id"><i class="fas fa-layer-group"></i> Kies een divisie:</label>
            <select id="division_id" name="division_id" class="form-control">
                <option value="">Selecteer een divisie</option>
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}" {{ $division->id == $selectedDivisionId ? 'selected' : '' }}>
                        {{ $division->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="season_id"><i class="fas fa-calendar-alt"></i> Kies een seizoen:</label>
            <select id="season_id" name="season_id" class="form-control">
                @foreach ($seasons as $season)
                    <option value="{{ $season->id }}" {{ $season->id == $latestSeason->id ? 'selected' : '' }}>
                        {{ $season->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="date"><i class="fas fa-calendar"></i> Datum:</label>
            <input type="date" id="date" name="date" class="form-control" required>
        </div>

        <div class="form-group mb-3" id="homeTeamGroup">
            <label for="home_team_id"><i class="fas fa-home"></i> Thuis Team:</label>
            <select id="home_team_id" name="home_team_id" class="form-control">
                @foreach($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3" id="awayTeamGroup">
            <label for="away_team_id"><i class="fas fa-plane"></i> Uit Team:</label>
            <select id="away_team_id" name="away_team_id" class="form-control">
                @foreach($teams as $team)
                <option value="{{ $team->id }}">{{ $team->name }}</option>
            @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="bye_team_id"><i class="fas fa-user-slash"></i> Bye (geen spel voor):</label>
            <select name="bye_team_id" id="bye_team_id" class="form-control" onchange="toggleTeamSelectVisibility(this)">
                <option value="">Geen Bye</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Wedstrijd Aanmaken</button>
    </form>
</div>

<script>
    document.getElementById('division_id').addEventListener('change', function() {
        const divisionId = this.value;
        const homeTeamSelect = document.getElementById('home_team_id');
        const awayTeamSelect = document.getElementById('away_team_id');

        homeTeamSelect.innerHTML = '<option value="">Selecteer een team</option>';
        awayTeamSelect.innerHTML = '<option value="">Selecteer een team</option>';

        if (divisionId) {
            fetch(`/api/divisions/${divisionId}/teams`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(team => {
                        const option = document.createElement('option');
                        option.value = team.id;
                        option.textContent = team.name;
                        homeTeamSelect.appendChild(option.cloneNode(true));
                        awayTeamSelect.appendChild(option);
                    });
                });
        }
    });

    function toggleTeamSelectVisibility(select) {
        const homeTeamGroup = document.getElementById('homeTeamGroup');
        const awayTeamGroup = document.getElementById('awayTeamGroup');
        const isByeSelected = select.value !== "";

        homeTeamGroup.style.display = isByeSelected ? 'none' : 'block';
        awayTeamGroup.style.display = isByeSelected ? 'none' : 'block';
    }
</script>
@endsection
