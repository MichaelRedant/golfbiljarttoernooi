@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1>Divisie: {{ $division->name }}</h1>

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-4">
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

        <h2>Volgende Wedstrijddag</h2>
        @if ($gamesByDate->isNotEmpty())
            @php
                $upcomingDates = $gamesByDate->keys()->filter(function ($date) {
                    return \Carbon\Carbon::parse($date) >= \Carbon\Carbon::today();
                });
                $nextDate = $upcomingDates->first();
            @endphp
            @if ($nextDate)
                <div class="card" style="max-width: 600px;">
                    <div class="card-header">{{ \Carbon\Carbon::parse($nextDate)->format('d-m-Y') }}</div>
                    <ul class="list-group list-group-flush">
                        @foreach ($gamesByDate[$nextDate] as $game)
                            <li class="list-group-item">
                                @if ($game->home_team_id && $game->away_team_id)
                                    <a href="{{ route('teams.show', $game->homeTeam->id ?? '#') }}">{{ $game->homeTeam->name ?? 'N/A' }}</a>
                                    tegen
                                    <a href="{{ route('teams.show', $game->awayTeam->id ?? '#') }}">{{ $game->awayTeam->name ?? 'N/A' }}</a>
                                    <span class="float-end">{{ $game->home_score ?? '' }} : {{ $game->away_score ?? '' }}</span>
                                    <i class="fas fa-home ml-4"></i><span class="ml-2"><small>{{ $game->homeTeam->location ?? 'N/A' }}</small></span>
                                    @if(auth()->check() && (auth()->user()->isAdmin() || (auth()->user()->isTeam() && auth()->user()->team_id == $game->home_team_id)))
                                        <a href="{{ route('games.form', $game->id) }}" class="btn btn-sm btn-success float-end ms-2" style="background-color: #28a745; border-color: #28a745;"><i class="fas fa-play"></i> Start Wedstrijd</a>
                                    @endif
                                @elseif ($game->bye_team_id)
                                    <i class="fas fa-user-slash"></i>
                                    <strong>{{ optional($game->byeTeam)->name }} heeft een Bye</strong>
                                @else
                                    Ongeplande tijd
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <p>Geen aankomende wedstrijden gepland voor dit seizoen.</p>
            @endif
        @else
            <p>Geen aankomende wedstrijden gepland voor dit seizoen.</p>
        @endif

        <h2>Standen</h2>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Team</th>
                    <th>Gewonnen</th>
                    <th>Verloren</th>
                    <th>Gelijk</th>
                    <th>Punten</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($standings as $standing)
                    <tr>
                        <td><a href="{{ route('teams.show', ['team' => $standing['team_id']]) }}">{{ $standing['team_name'] }}</a></td>
                        <td>{{ $standing['games_won'] }}</td>
                        <td>{{ $standing['games_lost'] }}</td>
                        <td>{{ $standing['games_draw'] }}</td>
                        <td>{{ $standing['points'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2>Voorbije Wedstrijden</h2>
        @if ($gamesByDate->isNotEmpty())
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Thuis Team</th>
                        <th>Uit Team</th>
                        <th>Uitslag</th>
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
                                        <td><a href="{{ route('teams.show', $game->awayTeam->id ?? '#') }}">{{ $game->awayTeam ? $game->awayTeam->name : 'Bye' }}</a></td>
                                        <td>{{ $game->home_score ?? '' }} : {{ $game->away_score ?? '' }}</td>
                                        <td><a href="{{ route('games.show', $game->id) }}" class="btn btn-primary"><i class="fas fa-eye"></i> Wedstrijd bekijken</a></td>
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
@endsection
