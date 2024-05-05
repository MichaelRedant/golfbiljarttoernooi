@extends('layouts.app')

@section('title', 'Wedstrijdkalender')

@section('content')
<div class="container mt-4">
    <h1>Wedstrijdkalender</h1>

    <!-- Actieknoppen -->
    <div class="action-buttons mb-4">
        <form action="{{ route('games.generate') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">Genereer Nieuwe Wedstrijden</button>
        </form>
        <form action="{{ route('games.clear') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger">Kalender Opschonen</button>
        </form>
    </div>
    

    <!-- Kalender weergave -->
    @foreach ($gamesByDate as $date => $gamesOnDate)
    <div class="day">
        <h2>{{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</h2> <!-- Datum geformatteerd zonder tijd -->
        @foreach ($gamesOnDate as $game)
            <div class="game card">
                <div class="card-body">
                    <p>
                        <a href="{{ route('teams.show', $game->homeTeam->id) }}" class="font-weight-bold">{{ $game->homeTeam->name }}</a>
                        tegen
                        <a href="{{ route('teams.show', $game->awayTeam->id) }}" class="font-weight-bold">{{ $game->awayTeam->name }}</a>
                    </p>
                    <p>
                        <span class="font-weight-bold">Uitslag:</span> 
                        @if(isset($game->home_score) && isset($game->away_score))
                            {{ $game->home_score }} : {{ $game->away_score }}
                        @endif
                    </p>

                    <!-- Conditionele weergave van knoppen afhankelijk van of de wedstrijd gespeeld is -->
                    @if (is_null($game->home_score) || is_null($game->away_score))
                        <a href="{{ route('games.form', $game->id) }}" class="btn btn-sm btn-primary">Speel Wedstrijd</a>
                    @else
                        <a href="{{ route('games.show', $game->id) }}" class="btn btn-sm btn-outline-secondary">Bekijk Wedstrijd</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @endforeach

</div>
@endsection
