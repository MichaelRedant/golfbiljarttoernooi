@extends('layouts.app')

@section('title', 'Wedstrijdkalender')

@section('content')
<div class="container mt-4">
    <h1>Wedstrijdkalender</h1>
    
    <!-- Actieknoppen -->
    <div class="mb-4">
        <form action="{{ route('games.generate') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary">Genereer Wedstrijden</button>
        </form>

        <form action="{{ route('games.clear') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger">Kalender Leegmaken</button>
        </form>



    </div>

    <!-- Kalender weergave -->
    @foreach ($gamesByDate as $date => $gamesOnDate)
        <div class="day">
            <h2>{{ $date }}</h2>
            @foreach ($gamesOnDate as $game)
                <div class="game">
                    <p>{{ $game->homeTeam->name }} vs {{ $game->awayTeam->name }} - {{ $game->home_score }} : {{ $game->away_score }} om {{ $game->start_time }} {{ $game->home_forfeit || $game->away_forfeit ? '(Forfait)' : '' }}</p>
                    <a href="{{ route('games.form', $game->id) }}" class="btn btn-primary">Wedstrijdformulier</a>

                </div>
            @endforeach
        </div>
    @endforeach
</div>
@endsection
