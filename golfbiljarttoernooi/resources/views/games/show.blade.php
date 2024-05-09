@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Wedstrijddetails voor <a href="{{ route('teams.show', $game->homeTeam->id) }}" style="color: {{ $game->home_score > $game->away_score ? 'green' : 'red' }};">{{ $game->homeTeam->name }}</a> vs <a href="{{ route('teams.show', $game->awayTeam->id) }}" style="color: {{ $game->away_score > $game->home_score ? 'green' : 'red' }};">{{ $game->awayTeam->name }}</a></h1>
    <div>
        <p>Thuisploeg: <a href="{{ route('teams.show', $game->homeTeam->id) }}" style="color: {{ $game->home_score > $game->away_score ? 'green' : 'red' }};">{{ $game->homeTeam->name }}</a></p>
        <p>Bezoekers: <a href="{{ route('teams.show', $game->awayTeam->id) }}" style="color: {{ $game->away_score > $game->home_score ? 'green' : 'red' }};">{{ $game->awayTeam->name }}</a></p>
        <p>Datum: {{ $game->date->format('d-m-Y') }}</p>
        <p>Wedstrijdscore: <strong>{{ $game->home_score }} - {{ $game->away_score }}</strong></p>
        <a href="{{ route('games.index') }}" class="btn btn-primary">Terug naar Wedstrijdkalender</a>

        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Thuis Speler</th>
                    <th>Uit Speler</th>
                    <th>1M</th>
                    <th>2M</th>
                    <th>Belle</th>
                </tr>
            </thead>
            <tbody>
                @foreach($game->manches as $manche)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td><a href="{{ route('players.show', $manche->player1->id) }}">{{ $manche->player1->first_name }} {{ $manche->player1->last_name }}</a></td>
                    <td><a href="{{ route('players.show', $manche->player2->id) }}">{{ $manche->player2->first_name }} {{ $manche->player2->last_name }}</a></td>
                    <td>{{ $manche->score1 }}</td>
                    <td>{{ $manche->score2 }}</td>
                    <td>{{ $manche->belle_score ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
    </div>
</div>
@endsection
