@extends('layouts.app')

@section('content')
<div class="container mt-4">
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <h1>Wedstrijden voor: {{ $division->name }} - {{ $season->name }}</h1>

    <!-- Dropdown for season selection -->
    <div class="mb-4">
        <label for="season-select" class="form-label">Kies een seizoen:</label>
        <select id="season-select" class="form-select" onchange="location = this.value;">
            @foreach ($seasons as $seasonOption)
                <option value="{{ route('games.for-division-season', ['division_id' => $division->id, 'season_id' => $seasonOption->id]) }}" {{ $season->id == $seasonOption->id ? 'selected' : '' }}>
                    {{ $seasonOption->name }}
                </option>
            @endforeach
        </select>
    </div>

    @if (isset($todayGames) && $todayGames->isNotEmpty())
    <h2>Wedstrijden van Vandaag ({{ \Carbon\Carbon::today()->format('d-m-Y') }})</h2>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Datum</th>
                <th>Thuis Team</th>
                <th>Uit Team</th>
                <th>Actie</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($todayGames as $game)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($game->date)->format('d-m-Y') }}</td>
                    <td>
                        @if ($game->bye_team_id)
                            {{ $game->byeTeam->name }} heeft een bye
                        @else
                            @if ($game->homeTeam)
                                <a href="{{ route('teams.show', $game->homeTeam->id) }}">{{ $game->homeTeam->name }}</a>
                            @else
                                Bye
                            @endif
                        @endif
                    </td>
                    <td>
                        @if ($game->awayTeam)
                            <a href="{{ route('teams.show', $game->awayTeam->id) }}">{{ $game->awayTeam->name }}</a>
                        @else
                            @if (!$game->bye_team_id)
                                Bye
                            @endif
                        @endif
                    </td>
                    <td>
                        @if (!$game->bye_team_id)
                            @if (!$game->played)
                                <a href="{{ route('games.form', $game->id) }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-play-circle"></i> Wedstrijd Spelen
                                </a>
                            @endif
                            
                        @endif
                        <a href="{{ $game->played ? route('games.show', $game->id) : '#' }}" class="btn btn-sm btn-primary {{ !$game->played ? 'disabled' : '' }}">
                            <i class="fas fa-eye"></i> Bekijk
                        </a>
                        <form action="{{ route('games.destroy', $game->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Weet je zeker dat je deze wedstrijd wilt verwijderen?');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash-alt"></i> Verwijder
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

    <h2>
        <a class="btn btn-link" data-bs-toggle="collapse" href="#upcomingGamesCollapse" role="button" aria-expanded="false" aria-controls="upcomingGamesCollapse">
            Aankomende Wedstrijden
        </a>
    </h2>
    <div class="collapse show" id="upcomingGamesCollapse">
        @if ($upcomingGames->isNotEmpty())
            @php
                $matchDays = $upcomingGames->keys()->sort();
            @endphp
            @foreach ($matchDays as $matchDay)
                <div class="card mb-4">
                    <div class="card-header">
                        {{ \Carbon\Carbon::parse($matchDay)->format('d-m-Y') }}
                    </div>
                    <ul class="list-group list-group-flush">
                        @foreach ($upcomingGames[$matchDay] as $game)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        @if ($game->bye_team_id)
                                            {{ $game->byeTeam->name }} heeft een bye
                                        @else
                                            @if ($game->homeTeam)
                                                <a href="{{ route('teams.show', $game->homeTeam->id) }}">{{ $game->homeTeam->name }}</a>
                                            @endif
                                            vs
                                            @if ($game->awayTeam)
                                                <a href="{{ route('teams.show', $game->awayTeam->id) }}">{{ $game->awayTeam->name }}</a>
                                            @endif
                                        @endif
                                    </div>
                                    <div>
                                        @if (!$game->bye_team_id)
                                            @if (!$game->played)
                                                <a href="{{ route('games.form', $game->id) }}" class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-play-circle"></i> Wedstrijd Spelen
                                                </a>
                                            @endif
                                            <a href="{{ route('games.edit', $game->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-pencil-alt"></i> Wedstrijd Bewerken
                                            </a>
                                        @endif
                                        <a href="{{ $game->played ? route('games.show', $game->id) : '#' }}" class="btn btn-sm btn-primary {{ !$game->played ? 'disabled' : '' }}">
                                            <i class="fas fa-eye"></i> Bekijk
                                        </a>
                                        <form action="{{ route('games.destroy', $game->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Weet je zeker dat je deze wedstrijd wilt verwijderen?');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash-alt"></i> Verwijder
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        @else
            <p>Geen geplande wedstrijden gevonden voor het geselecteerde seizoen.</p>
        @endif
    </div>

    <h2>
        <a class="btn btn-link" data-bs-toggle="collapse" href="#pastGamesCollapse" role="button" aria-expanded="false" aria-controls="pastGamesCollapse">
            Voorbije Wedstrijden
        </a>
    </h2>
    <div class="collapse" id="pastGamesCollapse">
        @if ($pastGames->isNotEmpty())
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
                    @foreach ($pastGames as $game)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($game->date)->format('d-m-Y') }}</td>
                            <td>
                                @if ($game->bye_team_id)
                                    {{ $game->byeTeam->name }} heeft een bye
                                @else
                                    @if ($game->homeTeam)
                                        <a href="{{ route('teams.show', $game->homeTeam->id) }}">{{ $game->homeTeam->name }}</a>
                                    @else
                                        Bye
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if ($game->awayTeam)
                                    <a href="{{ route('teams.show', $game->awayTeam->id) }}">{{ $game->awayTeam->name }}</a>
                                @else
                                    @if (!$game->bye_team_id)
                                        Bye
                                    @endif
                                @endif
                            </td>
                            <td>{{ $game->home_score ?? '' }} : {{ $game->away_score ?? '' }}</td>
                            <td>
                                @if (!$game->bye_team_id)
                                    <a href="{{ route('games.show', $game->id) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> Bekijk
                                    </a>
                                    <a href="{{ route('games.edit', $game->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-pencil-alt"></i> Aanpassen
                                    </a>
                                    <form action="{{ route('games.destroy', $game->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Weet je zeker dat je deze wedstrijd wilt verwijderen?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash-alt"></i> Verwijder
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-primary btn-sm" disabled>
                                        <i class="fas fa-eye"></i> Bekijk
                                    </button>
                                    <button class="btn btn-info btn-sm" disabled>
                                        <i class="fas fa-pencil-alt"></i> Aanpassen
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Geen voorbije wedstrijden dit seizoen.</p>
        @endif
    </div>
</div>
@endsection
