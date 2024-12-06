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
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-hover">
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
        $teams = [];
        if ($game->homeTeam) {
            $teams[] = $game->homeTeam->id;
        }
        if ($game->awayTeam) {
            $teams[] = $game->awayTeam->id;
        }
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

        $homeTeam = $firstGame->homeTeam->name ?? 'Vrij';
        $awayTeam = $firstGame->awayTeam->name ?? 'Vrij';

        // Totale scores berekenen, alleen als beide teams aanwezig zijn
        $totalHomeScore = ($firstGame->home_score ?? 0) + ($secondGame->away_score ?? 0);
        $totalAwayScore = ($firstGame->away_score ?? 0) + ($secondGame->home_score ?? 0);

        // Alleen winnaar berekenen als er scores zijn
        if ($firstGame->homeTeam && $firstGame->awayTeam) {
            if ($totalHomeScore > $totalAwayScore) {
                $winner = $homeTeam;
            } elseif ($totalHomeScore < $totalAwayScore) {
                $winner = $awayTeam;
            } else {
                $winner = 'Gelijkspel';
            }
        } else {
            $winner = null;
        }
    @endphp

    <!-- Eerste wedstrijd -->
    <tr>
        <td data-label="Ronde">{{ $firstGame->round->round_name ?? 'Onbekend' }}</td>
        <td data-label="Thuisteam">{{ $homeTeam }}</td>
        <td data-label="Uitteam">{{ $awayTeam }}</td>
        <td data-label="Datum">{{ $firstGame->homeTeam && $firstGame->awayTeam ? ($firstGame->date ? $firstGame->date->format('d-m-Y') : 'Onbekend') : '' }}</td>
        <td data-label="Score">{{ $firstGame->homeTeam && $firstGame->awayTeam ? $firstGame->home_score . ' - ' . $firstGame->away_score : '' }}</td>
        <td data-label="Acties">
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
        <td data-label="Ronde">{{ $secondGame->round->round_name ?? 'Onbekend' }}</td>
        <td data-label="Thuisteam">{{ $secondGame->homeTeam->name ?? 'Vrij' }}</td>
        <td data-label="Uitteam">{{ $secondGame->awayTeam->name ?? 'Vrij' }}</td>
        <td data-label="Datum">{{ $secondGame->homeTeam && $secondGame->awayTeam ? ($secondGame->date ? $secondGame->date->format('d-m-Y') : 'Onbekend') : '' }}</td>
        <td data-label="Score">{{ $secondGame->homeTeam && $secondGame->awayTeam ? $secondGame->home_score . ' - ' . $secondGame->away_score : '' }}</td>
        <td data-label="Acties">
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
    @if ($firstGame->homeTeam && $firstGame->awayTeam)
    <tr class="bg-light">
        <td colspan="4"><strong>Totale Score:</strong></td>
        <td><strong>{{ $totalHomeScore }} - {{ $totalAwayScore }}</strong></td>
        <td><strong>Winnaar: {{ $winner }}</strong></td>
    </tr>
    @endif

    <!-- Spatiëring tussen sets -->
    <tr class="bg-white">
        <td colspan="6">&nbsp;</td>
    </tr>
@endforeach

                                                            </tbody>
                                                        </table>
                                                    </div>
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

@section('styles')
<style>
    .table-responsive {
        overflow-x: auto;
    }
    .table thead {
        display: none;
    }
    .table tbody tr {
        display: block;
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
    }
    .table tbody td {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem;
        text-align: left;
    }
    .table tbody td:before {
        content: attr(data-label);
        font-weight: bold;
    }
</style>
@endsection
