@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Wedstrijddetails voor 
        <a href="{{ route('teams.show', $game->homeTeam->id) }}">{{ $game->homeTeam->name }}</a> 
        vs 
        <a href="{{ route('teams.show', $game->awayTeam->id) }}">{{ $game->awayTeam->name }}</a>
    </h3>

    <table class="table table-bordered mt-4">
        <tbody>
            <tr>
                <th>Club Thuisploeg</th>
                <td>
                    <a href="{{ route('clubs.show', $game->homeTeam->club->id) }}">{{ $game->homeTeam->club->name }}</a>
                </td>
            </tr>
            <tr>
                <th>Thuisploeg</th>
                <td>
                    <a href="{{ route('teams.show', $game->homeTeam->id) }}">
                        {{ $game->homeTeam->name }}
                        @if($game->home_score > $game->away_score)
                            <i class="fas fa-trophy" style="color: gold;"></i>
                        @endif
                    </a>
                </td>
            </tr>
            <tr>
                <th>Club Bezoekers</th>
                <td>
                    <a href="{{ route('clubs.show', $game->awayTeam->club->id) }}">{{ $game->awayTeam->club->name }}</a>
                </td>
            </tr>
            <tr>
                <th>Bezoekers</th>
                <td>
                    <a href="{{ route('teams.show', $game->awayTeam->id) }}">
                        {{ $game->awayTeam->name }}
                        @if($game->away_score > $game->home_score)
                            <i class="fas fa-trophy" style="color: gold;"></i>
                        @endif
                    </a>
                </td>
            </tr>
            <tr>
                <th>Locatie</th>
                <td>
                    <i class="fas fa-map-marker-alt"></i> {{ $game->homeTeam->location }}
                </td>
            </tr>
            <tr>
                <th>Datum</th>
                <td>{{ \Carbon\Carbon::parse($game->date)->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <th>Wedstrijdscore</th>
                <td>
                    <span class="badge bg-primary">{{ $game->home_score }}</span> - 
                    <span class="badge bg-danger">{{ $game->away_score }}</span>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="mt-4">
        @if($game->division)
            <a href="{{ route('divisions.show', ['division' => $game->division->id]) }}" class="btn btn-primary">Terug naar Wedstrijdkalender</a>
        @else
            <a href="#" onclick="history.back()" class="btn btn-primary">Terug</a>
        @endif
    </div>

    <form action="{{ route('games.approve', $game->id) }}" method="POST" class="mt-4">
        @csrf
        <button type="submit" class="btn btn-success">Goedkeuren</button>
    </form>
</div>
@endsection
