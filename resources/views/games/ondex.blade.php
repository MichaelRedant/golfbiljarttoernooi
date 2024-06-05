{{-- @extends('layouts.app')

@section('title', 'Wedstrijdkalender')

@section('content')
<div class="container mt-4">
    <h1>Wedstrijdkalender</h1>
    @if(auth()->check() && auth()->user()->role === 'admin')
    <!-- Actieknoppen voor admins -->
    <div class="action-buttons mb-4">
        <a href="{{ route('seasons.index') }}" class="btn btn-success">Seizoenen</a>
    </div>
@endif
    <!-- Seizoen kiezen -->
    @if ($seasons->isNotEmpty())
        <form action="{{ route('games.index') }}" method="GET">
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
    @else
        <p>Er zijn momenteel geen seizoenen beschikbaar. Voeg eerst een seizoen toe.</p>
    @endif

    <!-- Kalender weergave -->
    @if ($gamesByDate->isNotEmpty())
        @foreach ($gamesByDate as $date => $gamesOnDate)
            <div class="day">
                <h2>{{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</h2>
                @foreach ($gamesOnDate as $game)
                    <div class="game card">
                        <div class="card-body">
                            <p>
                                <a href="{{ route('teams.show', $game->homeTeam->id) }}" class="font-weight-bold">{{ $game->homeTeam->name }}</a>
                                tegen
                                <a href="{{ route('teams.show', $game->awayTeam->id) }}" class="font-weight-bold">{{ $game->awayTeam->name }}</a>
                            </p>
                            <p><span class="font-weight-bold">Uitslag:</span> {{ $game->home_score ?? '' }} : {{ $game->away_score ?? '' }}</p>
                            @if (!is_null($game->home_score) && !is_null($game->away_score))
                                <!-- Match is al gespeeld, toon bekijk en bewerk knoppen voor admins -->
                                <a href="{{ route('games.show', $game->id) }}" class="btn btn-sm btn-outline-secondary">Bekijk Wedstrijd</a>
                                @if (auth()->check() && auth()->user()->role === 'admin')
                                    <a href="{{ route('games.edit', $game->id) }}" class="btn btn-sm btn-primary">Bewerk Wedstrijd</a>
                                @endif
                            @else
                                <!-- Match moet nog gespeeld worden, toon speel knop alleen voor admins -->
                                @if(auth()->user() && auth()->user()->role === 'admin')
                                    <a href="{{ route('games.form', $game->id) }}" class="btn btn-sm btn-primary">Speel Wedstrijd</a>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    @else
        <p>Geen wedstrijden gepland voor dit seizoen.</p>
    @endif

    
</div>
@endsection
 --}}