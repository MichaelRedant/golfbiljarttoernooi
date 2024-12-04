@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1>Bewerk Teams en Datums voor {{ $cup->name }} (1/8 Finale)</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('cups.storeSelectedTeams', $cup->id) }}" method="POST">
        @csrf

        <div class="form-group mb-3">
            <label for="division_id"><i class="fas fa-layer-group"></i> Divisie:</label>
            <input type="text" id="division_id" class="form-control" value="{{ $cup->division->name }}" readonly>
        </div>

        <div class="form-group mb-3">
            <label for="season_id"><i class="fas fa-calendar-alt"></i> Seizoen:</label>
            <input type="text" id="season_id" class="form-control" value="{{ $cup->season->name }}" readonly>
        </div>

        <div class="form-group">
            <label for="matchups">Wedstrijdopstellingen:</label>
            <div class="row" id="matchupsContainer">
                @php
                    $selectedTeamIds = []; // Verzamel de gekozen team-ID's voor later gebruik
                @endphp

                @if($cup->rounds->isNotEmpty() && $cup->rounds->last()->games->isNotEmpty())
                    @foreach($cup->rounds->last()->games->take(6) as $i => $game)
                        @php
                            $selectedTeamIds[] = $game->home_team_id;
                            if ($game->away_team_id) {
                                $selectedTeamIds[] = $game->away_team_id;
                            }
                        @endphp
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    Wedstrijd {{ $i + 1 }}
                                </div>
                                <div class="card-body">
                                    <!-- Heenwedstrijd Datumselectie -->
                                    <div class="form-group">
                                        <label for="heen_date_{{ $i }}">Datum Heenwedstrijd:</label>
                                        <input type="date" name="matchups[{{ $i }}][heen_date]" class="form-control" value="{{ \Carbon\Carbon::parse($game->date)->format('Y-m-d') }}" required>
                                    </div>

                                    <!-- Terugwedstrijd Datumselectie -->
                                    <div class="form-group">
                                        <label for="terug_date_{{ $i }}">Datum Terugwedstrijd:</label>
                                        @if($game->returnGame)
                                            <input type="date" name="matchups[{{ $i }}][terug_date]" class="form-control" value="{{ \Carbon\Carbon::parse($game->returnGame->date)->format('Y-m-d') }}">
                                        @else
                                            <input type="date" name="matchups[{{ $i }}][terug_date]" class="form-control">
                                        @endif
                                    </div>

                                    <!-- Thuisteam -->
                                    <div class="form-group">
                                        <label for="home_team_{{ $i }}">Thuisteam</label>
                                        <select name="matchups[{{ $i }}][home_team]" class="form-control team-select" data-index="{{ $i }}" required>
                                            <option value="">Selecteer een thuisteam</option>
                                            @foreach($teams as $team)
                                                <option value="{{ $team->id }}" {{ $game->home_team_id == $team->id ? 'selected' : '' }}>
                                                    {{ $team->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Uitteam -->
                                    <div class="form-group">
                                        <label for="away_team_{{ $i }}">Uitteam</label>
                                        <select name="matchups[{{ $i }}][away_team]" class="form-control team-select" data-index="{{ $i }}">
                                            <option value="">Selecteer een uitteam</option>
                                            @foreach($teams as $team)
                                                <option value="{{ $team->id }}" {{ $game->away_team_id == $team->id ? 'selected' : '' }}>
                                                    {{ $team->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <!-- Nieuwe wedstrijden invoeren voor 6 wedstrijdparen -->
                    @for($i = 0; $i < 6; $i++)
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    Wedstrijd {{ $i + 1 }}
                                </div>
                                <div class="card-body">
                                    <!-- Heenwedstrijd Datumselectie -->
                                    <div class="form-group">
                                        <label for="heen_date_{{ $i }}">Datum Heenwedstrijd:</label>
                                        <input type="date" name="matchups[{{ $i }}][heen_date]" class="form-control" required>
                                    </div>

                                    <!-- Terugwedstrijd Datumselectie -->
                                    <div class="form-group">
                                        <label for="terug_date_{{ $i }}">Datum Terugwedstrijd:</label>
                                        <input type="date" name="matchups[{{ $i }}][terug_date]" class="form-control">
                                    </div>

                                    <!-- Thuisteam -->
                                    <div class="form-group">
                                        <label for="home_team_{{ $i }}">Thuisteam</label>
                                        <select name="matchups[{{ $i }}][home_team]" class="form-control team-select" data-index="{{ $i }}" required>
                                            <option value="">Selecteer een thuisteam</option>
                                            @foreach($teams as $team)
                                                <option value="{{ $team->id }}">
                                                    {{ $team->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Uitteam -->
                                    <div class="form-group">
                                        <label for="away_team_{{ $i }}">Uitteam</label>
                                        <select name="matchups[{{ $i }}][away_team]" class="form-control team-select" data-index="{{ $i }}">
                                            <option value="">Selecteer een uitteam</option>
                                            @foreach($teams as $team)
                                                <option value="{{ $team->id }}">
                                                    {{ $team->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
                @endif
            </div>
        </div>

        <!-- Vrijgestelde Teams -->
        <div class="mt-4">
            <h4>Vrijgestelde Teams</h4>
            @php
                // Filter vrijgestelde teams (teams die niet in de geselecteerde wedstrijdparen zitten)
                $freeTeams = $teams->filter(function ($team) use ($selectedTeamIds) {
                    return !in_array($team->id, $selectedTeamIds);
                });
            @endphp

            <ul id="freeTeamsList">
                @foreach($freeTeams as $team)
                    <li>{{ $team->name }}</li>
                @endforeach
            </ul>
        </div>

        <button type="submit" class="btn btn-primary">Opslaan</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const teams = @json($teams);
        const freeTeamsList = document.getElementById('freeTeamsList'); // De lijst van vrijgestelde teams

        function updateAvailableTeams() {
            const selectedTeamIds = [...new Set(
                Array.from(document.querySelectorAll('.team-select')).map(select => select.value).filter(value => value)
            )];

            document.querySelectorAll('.team-select').forEach(select => {
                const currentValue = select.value;
                select.innerHTML = '<option value="">Selecteer een team</option>';
                teams.forEach(team => {
                    const isDisabled = selectedTeamIds.includes(String(team.id)) && currentValue !== String(team.id);
                    select.insertAdjacentHTML('beforeend', `<option value="${team.id}" ${isDisabled ? 'disabled' : ''} ${currentValue == team.id ? 'selected' : ''}>${team.name}</option>`);
                });
            });

            // Update vrijgestelde teams in de view
            const freeTeams = teams.filter(team => !selectedTeamIds.includes(String(team.id)));
            freeTeamsList.innerHTML = '';
            freeTeams.forEach(team => {
                freeTeamsList.insertAdjacentHTML('beforeend', `<li>${team.name}</li>`);
            });
        }

        document.querySelectorAll('.team-select').forEach(select => {
            select.addEventListener('change', updateAvailableTeams);
        });

        updateAvailableTeams(); // Initialiseer de selectie en vrijgestelde teams
    });
</script>
@endsection
