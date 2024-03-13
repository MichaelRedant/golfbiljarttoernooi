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
                                        <option value="{{ $player->id }}" {{ (old('home_players.'.$i, optional($game->homePlayers[$i] ?? null)->id) == $player->id) ? 'selected' : '' }}>{{ $player->first_name }} {{ $player->last_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control" name="away_players[{{ $i }}]">
                                        @foreach ($awayTeamPlayers as $player)
                                        <option value="{{ $player->id }}" {{ (old('away_players.'.$i, optional($game->awayPlayers[$i] ?? null)->id) == $player->id) ? 'selected' : '' }}>{{ $player->first_name }} {{ $player->last_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
    <select class="form-control manche" name="scores[{{ $i }}][1M]">
        <option value="0" {{ (old('scores.'.$i.'.1M', optional($game->scores[$i] ?? null)['1M']) == 0) ? 'selected' : '' }}>0</option>
                                <option value="1" {{ (old('scores.'.$i.'.1M', optional($game->scores[$i] ?? null)['1M']) == 1) ? 'selected' : '' }}>1</option>
                                <option value="2" {{ (old('scores.'.$i.'.1M', optional($game->scores[$i] ?? null)['1M']) == 2) ? 'selected' : '' }}>2</option>
    </select>
</td>
<td>
    <select class="form-control manche" name="scores[{{ $i }}][2M]">
        <option value="0" {{ (old('scores.'.$i.'.2M', optional($game->scores[$i] ?? null)['2M']) == 0) ? 'selected' : '' }}>0</option>
                                <option value="1" {{ (old('scores.'.$i.'.2M', optional($game->scores[$i] ?? null)['2M']) == 1) ? 'selected' : '' }}>1</option>
                                <option value="2" {{ (old('scores.'.$i.'.2M', optional($game->scores[$i] ?? null)['2M']) == 2) ? 'selected' : '' }}>2</option>
    </select>
</td>

                                <td><select class="form-control belle" name="scores[{{ $i }}][Belle]" disabled> <option value="" {{ (old('belle.'.$i, optional($game->belles[$i] ?? null)->result) === null) ? 'selected' : '' }}>-</option>
                                    <option value="1" {{ (old('belle.'.$i, optional($game->belles[$i] ?? null)->result) == 1) ? 'selected' : '' }}>1</option>
                                    <option value="2" {{ (old('belle.'.$i, optional($game->belles[$i] ?? null)->result) == 2) ? 'selected' : '' }}>2</option></select></td>
                                <td><input type="text" class="form-control result" name="results[{{ $i }}]" readonly></td>
                                <td>
                                    <!-- Afsluiten en Bewerken knoppen -->
                                    <button type="button" class="btn btn-secondary lockMatch" data-index="{{ $i }}">Afsluiten</button>
                                    <button type="button" class="btn btn-primary unlockMatch" data-index="{{ $i }}" disabled>Bewerken</button>
                                </td>
                            </tr>
                            
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="final-result mt-4">
    <h4>Finale Uitslag</h4>
    <input type="hidden" name="home_score" id="home_score" value="{{ old('home_score', $game->home_score ?? 0) }}">
    <input type="hidden" name="away_score" id="away_score" value="{{ old('away_score', $game->away_score ?? 0) }}">

    <p id="finalScore">{{ $game->homeTeam->name }} : {{ $game->awayTeam->name }} = {{ old('home_score', $game->home_score ?? 0) }} : {{ old('away_score', $game->away_score ?? 0) }}</p>
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
                            <option value="{{ $player->id }}" {{ (old('home_reserve', $game->home_reserve ?? '') == $player->id) ? 'selected' : '' }}>{{ $player->first_name }} {{ $player->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Kapitein Thuis Team --}}
                    <div class="form-group">
                        <label for="home_captain">Kapitein:</label>
                        <select id="home_captain" name="home_captain" class="form-control">
                            @foreach ($homeTeamPlayers as $player)
                            <option value="{{ $player->id }}" {{ (old('home_captain', $game->home_captain ?? '') == $player->id) ? 'selected' : '' }}>{{ $player->first_name }} {{ $player->last_name }}</option>
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
                            <option value="{{ $player->id }}" {{ (old('away_reserve', $game->away_reserve ?? '') == $player->id) ? 'selected' : '' }}>{{ $player->first_name }} {{ $player->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Kapitein Bezoekende Team --}}
                    <div class="form-group">
                        <label for="away_captain">Kapitein:</label>
                        <select id="away_captain" name="away_captain" class="form-control">
                            @foreach ($awayTeamPlayers as $player)
                            <option value="{{ $player->id }}" {{ (old('away_captain', $game->away_captain ?? '') == $player->id) ? 'selected' : '' }}>{{ $player->first_name }} {{ $player->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-4 mb-4">
        <button type="submit" class="btn btn-primary">Wedstrijd opslaan</button>
    </div>
</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {


   // Functie om de rijen te vergrendelen/ontgrendelen
   function toggleRow(index, lock) {
        const row = document.querySelectorAll('tbody tr')[index];
        const inputs = row.querySelectorAll('input, select');
        inputs.forEach(input => input.disabled = lock);

        const lockButton = row.querySelector('.lockMatch');
        const unlockButton = row.querySelector('.unlockMatch');

        lockButton.disabled = lock;
        unlockButton.disabled = !lock;
    }

    // Functie om te controleren of alle matches zijn afgesloten
    function checkAllMatchesLocked() {
        return Array.from(document.querySelectorAll('.lockMatch')).every(button => button.disabled === true);
    }

    // Functie om de staat van de opslaan knop te updaten
    function updateSaveButtonState() {
        document.querySelector('button[type="submit"]').disabled = !checkAllMatchesLocked();
    }

    // Event listeners toevoegen voor de afsluiten en bewerken knoppen
    document.querySelectorAll('.lockMatch, .unlockMatch').forEach(button => {
        button.addEventListener('click', function() {
            const rowIndex = this.getAttribute('data-index');
            const isLocking = this.classList.contains('lockMatch');
            toggleRow(rowIndex, isLocking);
            updateSaveButtonState(); // Update de staat van de opslaan knop elke keer dat een actie wordt uitgevoerd
        });
    });

    // Initialiseren van de staat van de opslaan knop bij het laden van de pagina
    updateSaveButtonState();
    
    const rows = document.querySelectorAll('tbody tr');
    let homeWins = 0;
    let awayWins = 0;

    function updateFinalMatchScore() {
        // Update de tekst van de finale uitslag op basis van de gewonnen matches
        document.getElementById('finalScore').textContent = `Thuis Team : Bezoekende Team = ${homeWins} : ${awayWins}`;
    }

    rows.forEach(row => {
        const manche1Select = row.querySelector('select[name*="[1M]"]');
        const manche2Select = row.querySelector('select[name*="[2M]"]');
        const belleSelect = row.querySelector('select[name*="[Belle]"]');
        const resultInput = row.querySelector('input[type="text"].result');

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
        document.getElementById('home_score').value = homeWins;
        document.getElementById('away_score').value = awayWins;
    });
});



</script>



@endsection