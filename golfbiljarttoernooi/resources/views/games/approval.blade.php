@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Wedstrijddetails voor 
        <a href="{{ route('teams.show', $game->homeTeam->id) }}">{{ $game->homeTeam->name }}</a> 
        vs 
        <a href="{{ route('teams.show', $game->awayTeam->id) }}">{{ $game->awayTeam->name }}</a>
    </h1>
    <div>
        <a href="{{ route('clubs.show', $game->homeTeam->club->id) }}">{{ $game->homeTeam->club->name }}</a>
        <p>Thuisploeg: 
            <a href="{{ route('teams.show', $game->homeTeam->id) }}">
                {{ $game->homeTeam->name }}
                @if($game->home_score > $game->away_score)
                    <i class="fas fa-trophy" style="color: gold;"></i>
                @endif
            </a>
        </p>
        <a href="{{ route('clubs.show', $game->awayTeam->club->id) }}">{{ $game->awayTeam->club->name }}</a>
        <p>Bezoekers: 
            <a href="{{ route('teams.show', $game->awayTeam->id) }}">
                {{ $game->awayTeam->name }}
                @if($game->away_score > $game->home_score)
                    <i class="fas fa-trophy" style="color: gold;"></i>
                @endif
            </a>
        </p>
        <p>
            <i class="fas fa-map-marker-alt"></i> {{ $game->homeTeam->location }}
        </p>
        <p>Datum: {{ \Carbon\Carbon::parse($game->date)->format('d-m-Y') }}</p>
        <p>Wedstrijdscore: <strong>{{ $game->home_score }} - {{ $game->away_score }}</strong></p>
        @if($game->division)
            <a href="{{ route('divisions.show', ['division' => $game->division->id]) }}" class="btn btn-primary">Terug naar Wedstrijdkalender</a>
        @else
            <a href="#" onclick="history.back()" class="btn btn-primary">Terug</a>
        @endif

        <div class="card">
            <div class="card-header">Kapiteins en reservespelers</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label>Kapitein {{ $game->homeTeam->name }}</label>
                        <input type="text" class="form-control" value="{{ $game->homeCaptain ? $game->homeCaptain->first_name . ' ' . $game->homeCaptain->last_name : '' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label>Kapitein {{ $game->awayTeam->name }}</label>
                        <input type="text" class="form-control" value="{{ $game->awayCaptain ? $game->awayCaptain->first_name . ' ' . $game->awayCaptain->last_name : '' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label>Reservespeler {{ $game->homeTeam->name }}</label>
                        <input type="text" class="form-control" value="{{ $game->homeReserve ? $game->homeReserve->first_name . ' ' . $game->homeReserve->last_name : '' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label>Reservespeler {{ $game->awayTeam->name }}</label>
                        <input type="text" class="form-control" value="{{ $game->awayReserve ? $game->awayReserve->first_name . ' ' . $game->awayReserve->last_name : '' }}" readonly>
                    </div>
                </div>
            </div>
        </div>

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

        <form action="{{ route('games.approve', $game->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">Goedkeuren</button>
        </form>
    </div>
</div>
@endsection
