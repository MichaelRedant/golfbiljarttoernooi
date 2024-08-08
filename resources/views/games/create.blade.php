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
            <label for="division_id"><i class="fas fa-layer-group"></i> Kies een reeks:</label>
            <select id="division_id" name="division_id" class="form-control">
                <option value="" {{ old('division_id', $selectedDivisionId) == '' ? 'selected' : '' }}>Selecteer een reeks</option>
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}" {{ old('division_id', $selectedDivisionId) == $division->id ? 'selected' : '' }}>
                        {{ $division->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="season_id"><i class="fas fa-calendar-alt"></i> Kies een seizoen:</label>
            <select id="season_id" name="season_id" class="form-control">
                <option value="" {{ old('season_id', $selectedSeasonId) == '' ? 'selected' : '' }}>Selecteer een seizoen</option>
                @foreach ($seasons as $season)
                    <option value="{{ $season->id }}" {{ old('season_id', $selectedSeasonId) == $season->id ? 'selected' : '' }}>
                        {{ $season->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="date"><i class="fas fa-calendar"></i> Datum:</label>
            <input type="date" id="date" name="date" class="form-control" value="{{ old('date') }}" required>
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
            <label for="bye_team_id"><i class="fas fa-user-slash"></i> Bye (geen spel voor):</label>
            <select name="bye_team_id" id="bye_team_id" class="form-control" onchange="toggleTeamSelectVisibility(this)">
                <option value="" selected>Geen Bye</option>
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
        const byeTeamSelect = document.getElementById('bye_team_id');

        homeTeamSelect.innerHTML = '<option value="" selected>Selecteer een team</option>';
        awayTeamSelect.innerHTML = '<option value="" selected>Selecteer een team</option>';
        byeTeamSelect.innerHTML = '<option value="" selected>Geen Bye</option>';

        if (divisionId) {
            fetch(`/api/divisions/${divisionId}/teams`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(team => {
                        const option = document.createElement('option');
                        option.value = team.id;
                        option.textContent = team.name;
                        homeTeamSelect.appendChild(option.cloneNode(true));
                        awayTeamSelect.appendChild(option.cloneNode(true));
                        byeTeamSelect.appendChild(option.cloneNode(true));
                    });
                })
                .catch(error => console.error('Error fetching teams:', error));
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
