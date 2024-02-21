@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Wedstrijdformulier voor {{ $game->homeTeam->name }} vs {{ $game->awayTeam->name }}</h1>

    <div class="card my-4">
        <div class="card-body">
            <h5 class="card-title">Wedstrijdinformatie</h5>
            <p><strong>Thuisploeg:</strong> {{ $game->homeTeam->name }}</p>
            <p><strong>Bezoekers:</strong> {{ $game->awayTeam->name }}</p>
            <p><strong>Datum:</strong> {{ $game->date }}</p>
            <p><strong>Forfait:</strong> {{ $game->forfeit ? 'Ja' : 'Nee' }}</p>
        </div>
    </div>

    <form action="{{ route('games.update', $game->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">Spelers en Scores</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Spelers Thuisploeg</th>
                                <th>Spelers Bezoekers</th>
                                <th>1M</th>
                                <th>2M</th>
                                <th>Belle</th>
                                <th>Uitslag</th>
                                <th>Acties</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < 6; $i++)
                            <tr>
                                <td>
                                    <select class="form-control" name="home_players[{{ $i }}]">
                                        @foreach ($homeTeamPlayers as $player)
                                        <option value="{{ $player->id }}">{{ $player->first_name }} {{ $player->last_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control" name="away_players[{{ $i }}]">
                                        @foreach ($awayTeamPlayers as $player)
                                        <option value="{{ $player->id }}">{{ $player->first_name }} {{ $player->last_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
    <select class="form-control manche" name="scores[{{ $i }}][1M]">
        <option value="0" selected>0</option>
        <option value="1">1</option>
        <option value="2">2</option>
    </select>
</td>
<td>
    <select class="form-control manche" name="scores[{{ $i }}][2M]">
        <option value="0" selected>0</option>
        <option value="1">1</option>
        <option value="2">2</option>
    </select>
</td>

                                <td><select class="form-control belle" name="scores[{{ $i }}][Belle]" disabled><option value="">-</option><option value="1">1</option><option value="2">2</option></select></td>
                                <td><input type="text" class="form-control result" name="results[{{ $i }}]" readonly></td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="final-result mt-4">
    <h4>Finale Uitslag</h4>
    <p id="finalScore">Thuis Team : Bezoekende Team = 0 : 0</p>
</div>


       {{-- Reserve en Kapitein Selectie met Dynamische Teamnamen --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card mt-3">
                <div class="card-header">{{ $game->homeTeam->name }} - Extra</div>
                <div class="card-body">
                    {{-- Reservespeler Thuis Team --}}
                    <div class="form-group">
                        <label for="home_reserve">Reservespeler:</label>
                        <select id="home_reserve" name="home_reserve" class="form-control">
                            @foreach ($homeTeamPlayers as $player)
                                <option value="{{ $player->id }}">{{ $player->first_name }} {{ $player->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Kapitein Thuis Team --}}
                    <div class="form-group">
                        <label for="home_captain">Kapitein:</label>
                        <select id="home_captain" name="home_captain" class="form-control">
                            @foreach ($homeTeamPlayers as $player)
                                <option value="{{ $player->id }}">{{ $player->first_name }} {{ $player->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mt-3">
                <div class="card-header">{{ $game->awayTeam->name }} - Extra</div>
                <div class="card-body">
                    {{-- Reservespeler Bezoekende Team --}}
                    <div class="form-group">
                        <label for="away_reserve">Reservespeler:</label>
                        <select id="away_reserve" name="away_reserve" class="form-control">
                            @foreach ($awayTeamPlayers as $player)
                                <option value="{{ $player->id }}">{{ $player->first_name }} {{ $player->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Kapitein Bezoekende Team --}}
                    <div class="form-group">
                        <label for="away_captain">Kapitein:</label>
                        <select id="away_captain" name="away_captain" class="form-control">
                            @foreach ($awayTeamPlayers as $player)
                                <option value="{{ $player->id }}">{{ $player->first_name }} {{ $player->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <button type="submit" class="btn btn-primary">Wedstrijd opslaan</button>
    </div>
    <input type="hidden" name="home_score" id="home_score" value="{{ old('home_score', $game->home_score ?? 0) }}">
<input type="hidden" name="away_score" id="away_score" value="{{ old('away_score', $game->away_score ?? 0) }}">

</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('tbody tr');
    let homeWins = 0;
    let awayWins = 0;

    function updateFinalMatchScore() {
        // Update de tekst van de finale uitslag op basis van de gewonnen matches
        document.getElementById('finalScore').textContent = `Thuis Team : Bezoekende Team = ${homeWins} : ${awayWins}`;
        // Stuur de geüpdatete scores terug naar de server om op te slaan
        document.getElementById('home_score').value = homeWins;
        document.getElementById('away_score').value = awayWins;
    }

    rows.forEach(row => {
        const manche1Select = row.querySelector('select[name*="[1M]"]');
        const manche2Select = row.querySelector('select[name*="[2M]"]');
        const belleSelect = row.querySelector('select[name*="[Belle]"]');
        const resultInput = row.querySelector('input[type="text"].result');

        const actionCell = row.insertCell(-1); // Voegt een nieuwe cel toe aan het einde van de rij
        actionCell.innerHTML = 
        '<button type="button" class="btn btn-primary mr-1 lockButton">Afsluiten</button><button type="button" class="btn btn-secondary editButton" disabled>Bewerken</button>';

        const lockButton = actionCell.querySelector('.lockButton');
        const editButton = actionCell.querySelector('.editButton');

        lockButton.addEventListener('click', function() {
            toggleEdit(row, false);
            this.disabled = true;
            editButton.disabled = false;
        });

        editButton.addEventListener('click', function() {
            toggleEdit(row, true);
            this.disabled = true;
            lockButton.disabled = false;
        });

        function toggleEdit(row, enable) {
            Array.from(row.querySelectorAll('select, input:not(.result)')).forEach(element => {
                element.disabled = !enable;
            });
        }

        function calculateResult() {
            const manche1 = parseInt(manche1Select.value);
            const manche2 = parseInt(manche2Select.value);
            let belle = belleSelect.value ? parseInt(belleSelect.value) : 0;
            let homePoints = 0;
            let awayPoints = 0;

            if (manche1 === 1) homePoints++;
            if (manche2 === 2) awayPoints++;
            if (manche1 === 2) awayPoints++;
            if (manche2 === 1) homePoints++;

            if (homePoints === awayPoints && homePoints !== 0) {
                belleSelect.disabled = false;
                if (belle === 1) homePoints++;
                if (belle === 2) awayPoints++;
            } else {
                belleSelect.disabled = true;
                belleSelect.value = "0";
            }

            resultInput.value = `${homePoints}-${awayPoints}`;

            // Update de winst per match in plaats van totale punten
            if (homePoints > awayPoints) homeWins++;
            if (awayPoints > homePoints) awayWins++;

            updateFinalMatchScore();
        }

        manche1Select.addEventListener('change', calculateResult);
        manche2Select.addEventListener('change', calculateResult);
        belleSelect.addEventListener('change', calculateResult);
    });
});
</script>




@endsection