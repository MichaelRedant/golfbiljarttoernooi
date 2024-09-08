@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="text-center">Wedstrijddetails</h3>

    <div class="row">
        <div class="col-md-6">
            <h4 class="text-center">{{ $game->homeTeam->name }}</h4>
            <p class="text-center">
                <a href="{{ route('clubs.show', $game->homeTeam->club->id) }}">
                    {{ $game->homeTeam->club->name }}
                </a>
            </p>
        </div>
        <div class="col-md-6">
            <h4 class="text-center">{{ $game->awayTeam->name }}</h4>
            <p class="text-center">
                <a href="{{ route('clubs.show', $game->awayTeam->club->id) }}">
                    {{ $game->awayTeam->club->name }}
                </a>
            </p>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col-md-6 text-center">
            <strong>Locatie:</strong>
            <p>{{ $game->homeTeam->location }}</p>
        </div>
        <div class="col-md-6 text-center">
            <strong>Datum:</strong>
            <p>{{ \Carbon\Carbon::parse($game->date)->format('d-m-Y') }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 text-center">
            <h5 class="mb-3">
                <span class="badge">{{ $liveData['home_score'] ?? $game->home_score }}</span> - 
                <span class="badge">{{ $liveData['away_score'] ?? $game->away_score }}</span>
            </h5>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 text-center">
            <strong>Kapitein:</strong>
            <p><a href="#">{{ $liveData['home_captain_name'] ?? $game->homeTeam->captain->full_name ?? 'Onbekend' }}</a></p>
        </div>
        <div class="col-md-6 text-center">
            <strong>Kapitein:</strong>
            <p><a href="#">{{ $liveData['away_captain_name'] ?? $game->awayTeam->captain->full_name ?? 'Onbekend' }}</a></p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 text-center">
            <strong>Reservespeler:</strong>
            <p><a href="#">{{ $liveData['home_reserve_name'] ?? $game->homeTeam->reserve->full_name ?? 'Onbekend' }}</a></p>
        </div>
        <div class="col-md-6 text-center">
            <strong>Reservespeler:</strong>
            <p><a href="#">{{ $liveData['away_reserve_name'] ?? $game->awayTeam->reserve->full_name ?? 'Onbekend' }}</a></p>
        </div>
    </div>

    @if(!empty($scores))
    <div class="mt-4">
        <h4 class="text-center">Scores</h4>
        <table class="table table-striped">
            <thead>
                <tr class="text-center">
                    <th>Speler {{ $game->homeTeam->name }}</th>
                    <th style="font-size: 0.9em;">Team Thuis</th>
                    <th>Speler {{ $game->awayTeam->name }}</th>
                    <th style="font-size: 0.9em;">Team Uit</th>
                    <th>1M</th>
                    <th>2M</th>
                    <th>Belle</th>
                </tr>
            </thead>
            <tbody>
                @foreach($scores as $score)
                    <tr class="text-center">
                        <td>{{ $score['home_player_name'] }}</td>
                        <td style="font-size: 0.9em;">{{ $score['home_player_team'] }}</td>
                        <td>{{ $score['away_player_name'] }}</td>
                        <td style="font-size: 0.9em;">{{ $score['away_player_team'] }}</td>
                        <td>{{ $score['1M'] }}</td>
                        <td>{{ $score['2M'] }}</td>
                        <td>{{ $score['Belle'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="text-center mt-4">
        @if($game->division)
            <a href="{{ route('divisions.show', ['division' => $game->division->id]) }}" class="btn btn-primary">Terug naar Wedstrijdkalender</a>
        @else
            <a href="#" onclick="history.back()" class="btn btn-primary">Terug</a>
        @endif
    </div>

    <!-- Controleer of de gebruiker ingelogd is en admin-rechten heeft voordat de goedkeuringsknop wordt weergegeven -->
    @if(auth()->check() && auth()->user()->isAdmin() && !$game->away_team_approved)
    <form action="{{ route('games.approve', $game->id) }}" method="POST" class="text-center mt-4">
        @csrf
        <button type="submit" class="btn btn-success">Goedkeuren</button>
    </form>
    @endif
</div>
@endsection
