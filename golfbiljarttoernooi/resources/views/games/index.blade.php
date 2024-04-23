@extends('layouts.app')

@section('title', 'Wedstrijdkalender')

@section('content')
<div class="container mt-4">
    <h1>Wedstrijdkalender</h1>

    <!-- Actieknoppen -->
    <div class="action-buttons mb-4">
        <!-- ... -->
    </div>

    <!-- Kalender weergave -->
    @foreach ($gamesByDate as $date => $gamesOnDate)
        <div class="day">
            <h2>{{ $date }}</h2>
            @foreach ($gamesOnDate as $game)
                <div class="game card">
                    <div class="card-body">
                        <p> 
                            <a href="{{ route('teams.show', $game->homeTeam->id) }}" class="font-weight-bold">{{ $game->homeTeam->name }}</a> 
                            tegen 
                            <a href="{{ route('teams.show', $game->awayTeam->id) }}" class="font-weight-bold">{{ $game->awayTeam->name }}</a>
                            <p><span class="font-weight-bold">Uitslag:</span> {{ $game->home_score }} : {{ $game->away_score }}</p>  
                            
                        </p>
                        <a href="{{ route('games.form', $game->id) }}" class="btn btn-sm btn-outline-primary">Wedstrijdformulier</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</div>
@endsection

