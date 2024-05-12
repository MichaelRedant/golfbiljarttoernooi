@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1>Divisie: {{ $division->name }}</h1>

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
                                <a href="{{ route('teams.show', $game->homeTeam->id) }}">{{ $game->homeTeam->name }}</a>
                                tegen
                                <a href="{{ route('teams.show', $game->awayTeam->id) }}">{{ $game->awayTeam->name }}</a>
                            @elseif ($game->bye_team_id)
                                <i class="fas fa-user-slash"></i>
                                <strong>{{ optional($game->byeTeam)->name }} heeft een Bye</strong>
                            @else
                                Ongeplande tijd
                            @endif
                            <span class="float-right">{{ $game->home_score ?? 'TBA' }} : {{ $game->away_score ?? 'TBA' }}</span>
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
    <table class="table table-bordered">
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
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Thuis Team</th>
                    <th>Uit Team</th>
                    <th>Uitslag</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($gamesByDate as $date => $gamesOnDate)
                    @if (\Carbon\Carbon::parse($date) < \Carbon\Carbon::today())
                        @foreach ($gamesOnDate as $game)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</td>
                                <td>{{ $game->homeTeam ? $game->homeTeam->name : 'Bye' }}</td>
                                <td>{{ $game->awayTeam ? $game->awayTeam->name : 'Bye' }}</td>
                                <td>{{ $game->home_score ?? '' }} : {{ $game->away_score ?? '' }}</td>
                            </tr>
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
