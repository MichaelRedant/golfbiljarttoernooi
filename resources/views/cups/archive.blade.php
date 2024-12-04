@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="text-center mb-5">Beker Archief</h1>

    @if(isset($message))
        <p>{{ $message }}</p>
    @else
        <div class="accordion" id="seasonAccordion">
            @foreach ($cupsGroupedBySeason as $seasonName => $cups)
                <div class="card mb-3">
                    <div class="card-header" id="heading-{{ $loop->index }}">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $loop->index }}" aria-expanded="true" aria-controls="collapse-{{ $loop->index }}">
                                Seizoen: {{ $seasonName }}
                            </button>
                        </h2>
                    </div>
                    <div id="collapse-{{ $loop->index }}" class="collapse @if($loop->first) show @endif" aria-labelledby="heading-{{ $loop->index }}" data-parent="#seasonAccordion">
                        <div class="card-body">
                            @foreach ($cups as $cup)
                                <div class="accordion mb-3" id="cupAccordion-{{ $loop->index }}">
                                    <div class="card">
                                        <div class="card-header" id="cup-heading-{{ $loop->index }}">
                                            <h3 class="mb-0">
                                                <button class="btn btn-link btn-block text-left" type="button" data-bs-toggle="collapse" data-bs-target="#cup-collapse-{{ $loop->index }}" aria-expanded="true" aria-controls="cup-collapse-{{ $loop->index }}">
                                                    {{ $cup->name }} ({{ $cup->division->name ?? 'Onbekende Divisie' }})
                                                </button>
                                            </h3>
                                        </div>
                                        <div id="cup-collapse-{{ $loop->index }}" class="collapse" aria-labelledby="cup-heading-{{ $loop->index }}" data-parent="#cupAccordion-{{ $loop->index }}">
                                            <div class="card-body">
                                                @if ($cup->games->isEmpty())
                                                    <p>Geen wedstrijden beschikbaar.</p>
                                                @else
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Ronde</th>
                                                                <th>Thuisteam</th>
                                                                <th>Uitteam</th>
                                                                <th>Datum</th>
                                                                <th>Score</th>
                                                                <th>Acties</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                // Groepeer wedstrijden als heen-en-terug door een unieke sleutel te maken
                                                                $groupedGames = collect($cup->games)->groupBy(function($game) {
                                                                    $teams = [$game->homeTeam->id, $game->awayTeam->id];
                                                                    sort($teams); // Zorg ervoor dat de volgorde altijd hetzelfde is
                                                                    return implode('-', $teams); // Maak een unieke sleutel op basis van team IDs
                                                                });
                                                            @endphp
                                                        
                                                            @foreach ($groupedGames as $games)
                                                                @php
                                                                    // Sorteren van heen- en terugwedstrijden op datum
                                                                    $games = $games->sortBy('date')->values();
                                                        
                                                                    $firstGame = $games[0];
                                                                    $secondGame = $games->count() > 1 ? $games[1] : null;
                                                        
                                                                    $homeTeam = $firstGame->homeTeam->name ?? 'Onbekend';
                                                                    $awayTeam = $firstGame->awayTeam->name ?? 'Onbekend';
                                                        
                                                                    // Totale scores berekenen
                                                                    $totalHomeScore = ($firstGame->home_score ?? 0) + ($secondGame->away_score ?? 0);
                                                                    $totalAwayScore = ($firstGame->away_score ?? 0) + ($secondGame->home_score ?? 0);
                                                        
                                                                    // Winnaar bepalen
                                                                    if ($totalHomeScore > $totalAwayScore) {
                                                                        $winner = $homeTeam;
                                                                    } elseif ($totalHomeScore < $totalAwayScore) {
                                                                        $winner = $awayTeam;
                                                                    } else {
                                                                        $winner = 'Gelijkspel';
                                                                    }
                                                                @endphp
                                                        
                                                                <!-- Eerste wedstrijd -->
                                                                <tr>
                                                                    <td>{{ $firstGame->round->round_name ?? 'Onbekend' }}</td>
                                                                    <td>{{ $homeTeam }}</td>
                                                                    <td>{{ $awayTeam }}</td>
                                                                    <td>{{ $firstGame->date ? $firstGame->date->format('d-m-Y') : 'Onbekend' }}</td>
                                                                    <td>{{ $firstGame->home_score ?? 0 }} - {{ $firstGame->away_score ?? 0 }}</td>
                                                                    <td>
                                                                        @if($firstGame->id && $firstGame->date && $firstGame->date <= now())
                                                                            <a href="{{ route('cup_game.show', ['game' => $firstGame->id]) }}" class="btn btn-info btn-sm">
                                                                                <i class="fas fa-eye"></i> Bekijken
                                                                            </a>
                                                                        @else
                                                                            <button class="btn btn-secondary btn-sm" disabled>
                                                                                <i class="fas fa-eye"></i> Bekijken
                                                                            </button>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                        
                                                                <!-- Tweede wedstrijd (indien aanwezig) -->
                                                                @if ($secondGame)
                                                                <tr>
                                                                    <td>{{ $secondGame->round->round_name ?? 'Onbekend' }}</td>
                                                                    <td>{{ $secondGame->homeTeam->name }}</td>
                                                                    <td>{{ $secondGame->awayTeam->name }}</td>
                                                                    <td>{{ $secondGame->date ? $secondGame->date->format('d-m-Y') : 'Onbekend' }}</td>
                                                                    <td>{{ $secondGame->home_score ?? 0 }} - {{ $secondGame->away_score ?? 0 }}</td>
                                                                    <td>
                                                                        @if($secondGame->id && $secondGame->date && $secondGame->date <= now())
                                                                            <a href="{{ route('cup_game.show', ['game' => $secondGame->id]) }}" class="btn btn-info btn-sm">
                                                                                <i class="fas fa-eye"></i> Bekijken
                                                                            </a>
                                                                        @else
                                                                            <button class="btn btn-secondary btn-sm" disabled>
                                                                                <i class="fas fa-eye"></i> Bekijken
                                                                            </button>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                                @endif
                                                        
                                                                <!-- Totale score -->
                                                                <tr class="bg-light">
                                                                    <td colspan="4"><strong>Totale Score:</strong></td>
                                                                    <td><strong>{{ $totalHomeScore }} - {{ $totalAwayScore }}</strong></td>
                                                                    <td><strong>Winnaar: {{ $winner }}</strong></td>
                                                                </tr>
                                                        
                                                                <!-- Spatiëring tussen sets -->
                                                                <tr class="bg-white">
                                                                    <td colspan="6">&nbsp;</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        
                                                    </table>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
