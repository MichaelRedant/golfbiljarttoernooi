@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1>Reeks: {{ $division->name }}</h1>
    <div class="mt-4">
        <a href="{{ route('games.kalender') }}" class="btn btn-primary">Bekijk Volledige Wedstrijdkalender</a>
    </div>
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4 mt-4">
        <form action="{{ route('divisions.show', $division->id) }}" method="GET">
            <div class="form-group">
                <label for="season_id">Kies een seizoen:</label>
                <select id="season_id" name="season_id" class="form-control" onchange="this.form.submit()">
                    @foreach ($seasons as $season)
                        <option value="{{ $season->id }}" {{ $season->id == $currentSeasonId ? 'selected' : '' }}>
                            {{ $season->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <h2>
        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#nextMatchDayCollapse" aria-expanded="true" aria-controls="nextMatchDayCollapse">
            Volgende Wedstrijddag <i class="fas fa-chevron-down"></i>
        </button>
    </h2>
    <div class="collapse show" id="nextMatchDayCollapse">
        @if ($gamesByDate->isNotEmpty())
            @php
                $upcomingDates = $gamesByDate->keys()->filter(function ($date) {
                    return \Carbon\Carbon::parse($date) >= \Carbon\Carbon::today();
                });
                $nextDate = $upcomingDates->first();
            @endphp
            @if ($nextDate)
                <div class="card" style="max-width: 1000px;">
                    <div class="card-header">{{ \Carbon\Carbon::parse($nextDate)->format('d-m-Y') }}</div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tbody>
                                @foreach ($gamesByDate[$nextDate] as $game)
                                    <tr>
                                        @if ($game->home_team_id && $game->away_team_id)
                                            <td>
                                                <a href="{{ route('teams.show', $game->homeTeam->id ?? '#') }}">{{ $game->homeTeam->name ?? 'N/A' }}</a>
                                                tegen
                                                <a href="{{ route('teams.show', $game->awayTeam->id ?? '#') }}">{{ $game->awayTeam->name ?? 'N/A' }}</a>
                                            </td>
                                            <td>
                                                @if($game->home_score !== null && $game->away_score !== null)
                                                    {{ $game->home_score }} : {{ $game->away_score }}
                                                @else
                                                    <span></span>
                                                @endif
                                            </td>
                                            <td><i class="fas fa-home"></i></td>
                                            <td>{{ $game->homeTeam->location ?? 'N/A' }}</td>
                                            @if($game->home_score !== null && $game->away_score !== null)
                                                <td>
                                                    <a href="{{ route('games.show', $game->id) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i> Wedstrijd bekijken
                                                    </a>
                                                </td>
                                            @elseif(auth()->check() && (auth()->user()->isAdmin() || (auth()->user()->isTeam() && auth()->user()->team_id == $game->home_team_id)) && \Carbon\Carbon::parse($game->date)->isToday())
                                                <td>
                                                    <a href="{{ route('games.form', $game->id) }}" class="btn btn-sm btn-success">
                                                        <i class="fas fa-play"></i> Start Wedstrijd
                                                    </a>
                                                </td>
                                            @endif
                                        @elseif ($game->bye_team_id)
                                            <td colspan="5">
                                                <i class="fas fa-user-slash"></i>
                                                <strong>{{ optional($game->byeTeam)->name }} heeft een Bye</strong>
                                            </td>
                                        @else
                                            <td colspan="5">Ongeplande tijd</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <p>Geen aankomende wedstrijden gepland voor dit seizoen.</p>
            @endif
        @else
            <p>Geen aankomende wedstrijden gepland voor dit seizoen.</p>
        @endif
    </div>

    <h2>
        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#standingsCollapse" aria-expanded="true" aria-controls="standingsCollapse">
            Standen <i class="fas fa-chevron-down"></i>
        </button>
    </h2>
    <div class="collapse show" id="standingsCollapse">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Team</th>
                    <th>Gewonnen</th>
                    <th>Verloren</th>
                    <th>Gelijk</th>
                    <th>Punten</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($standings as $index => $standing)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><a href="{{ route('teams.show', ['team' => $standing['team_id']]) }}">{{ $standing['team_name'] }}</a></td>
                        <td>{{ $standing['games_won'] }}</td>
                        <td>{{ $standing['games_lost'] }}</td>
                        <td>{{ $standing['games_draw'] }}</td>
                        <td>{{ $standing['points'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <h2>
        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#pastGamesCollapse" aria-expanded="true" aria-controls="pastGamesCollapse">
            Voorbije Wedstrijden <i class="fas fa-chevron-down"></i>
        </button>
    </h2>
    <div class="collapse show" id="pastGamesCollapse">
        @if ($gamesByDate->isNotEmpty())
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Thuis Team</th>
                        <th>Uitslag</th>
                        <th>Uit Team</th>
                        <th>Actie</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($gamesByDate->sortKeysDesc() as $date => $gamesOnDate)
                        @if (\Carbon\Carbon::parse($date) < \Carbon\Carbon::today())
                            @foreach ($gamesOnDate as $game)
                                @if (!$game->bye_team_id)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</td>
                                        <td><a href="{{ route('teams.show', $game->homeTeam->id ?? '#') }}">{{ $game->homeTeam ? $game->homeTeam->name : 'Bye' }}</a></td>
                                        <td>{{ $game->home_score ?? '' }} : {{ $game->away_score ?? '' }}</td>
                                        <td><a href="{{ route('teams.show', $game->awayTeam->id ?? '#') }}">{{ $game->awayTeam ? $game->awayTeam->name : 'Bye' }}</a></td>                                      
                                        <td><a href="{{ route('games.show', $game->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> Wedstrijd bekijken</a></td>
                                    </tr>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Geen voorbije wedstrijden dit seizoen.</p>
        @endif
    </div>
</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-toggle="collapse"]').forEach(function(element) {
            element.addEventListener('click', function() {
                const icon = this.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-chevron-down');
                    icon.classList.toggle('fa-chevron-up');
                }
            });
        });
    });
</script>
