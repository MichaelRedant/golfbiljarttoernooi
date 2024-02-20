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

        <form action="{{ route('games.clear') }}" method="POST" class="d-inline">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger" onclick="return confirm('Weet je zeker dat je de kalender wilt verwijderen? Dit kan niet ongedaan gemaakt worden.');">
        Kalender Verwijderen
    </button>
</form>


    </div>

    <!-- Kalender weergave -->
    @foreach ($gamesByDate as $date => $gamesOnDate)
        <div class="day">
            <h2>{{ $date }}</h2>
            @foreach ($gamesOnDate as $game)
                <div class="game">
                    <p>{{ $game->homeTeam->name }} vs {{ $game->awayTeam->name }} - {{ $game->home_score }} : {{ $game->away_score }} om {{ $game->start_time }} {{ $game->home_forfeit || $game->away_forfeit ? '(Forfait)' : '' }}</p>
                    <a href="{{ route('games.show', $game->id) }}">wedstrijdformulier</a>
                </div>
            @endforeach
        </div>
    @endforeach
</div>
@endsection
