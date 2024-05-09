@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Edit Match Details for <a href="{{ route('teams.show', $game->homeTeam->id) }}" style="color: {{ $game->home_score > $game->away_score ? 'green' : 'red' }};">{{ $game->homeTeam->name }}</a> vs <a href="{{ route('teams.show', $game->awayTeam->id) }}" style="color: {{ $game->away_score > $game->home_score ? 'green' : 'red' }};">{{ $game->awayTeam->name }}</a></h1>
    
    <form action="{{ route('games.update', $game->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><a href="{{ route('teams.show', $game->homeTeam->id) }}" style="color: {{ $game->home_score > $game->away_score ? 'green' : 'red' }};">{{ $game->homeTeam->name }}</a></th>
                        <th><a href="{{ route('teams.show', $game->awayTeam->id) }}" style="color: {{ $game->away_score > $game->home_score ? 'green' : 'red' }};">{{ $game->awayTeam->name }}</a></th>
                        <th>1M</th>
                        <th>2M</th>
                        <th>Belle</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($game->manches as $index => $manche)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><a href="{{ route('players.show', $manche->player1->id) }}">{{ $manche->player1->first_name }} {{ $manche->player1->last_name }}</a></td>
                        <td><a href="{{ route('players.show', $manche->player2->id) }}">{{ $manche->player2->first_name }} {{ $manche->player2->last_name }}</a></td>
                        <td><input type="text" name="scores[{{$index}}][1M]" value="{{ $manche->score1 }}" class="form-control"></td>
                        <td><input type="text" name="scores[{{$index}}][2M]" value="{{ $manche->score2 }}" class="form-control"></td>
                        <td><input type="text" name="scores[{{$index}}][Belle]" value="{{ $manche->belle_score }}" class="form-control"></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <button type="submit" class="btn btn-success">Update Scores</button>
    </form>
</div>
@endsection
