<!-- resources/views/games/index.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1>Wedstrijden</h1>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Thuis Team</th>
                        <th>Uit Team</th>
                        <th>Locatie</th>
                        <th>Actie</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($upcomingGames as $game)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($game->date)->format('d-m-Y') }}</td>
                            <td>
                                @if($game->homeTeam)
                                    <a href="{{ route('teams.show', $game->homeTeam->id) }}">{{ $game->homeTeam->name }}</a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($game->awayTeam)
                                    <a href="{{ route('teams.show', $game->awayTeam->id) }}">{{ $game->awayTeam->name }}</a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($game->homeTeam)
                                    {{ $game->homeTeam->location }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if(auth()->check() && (auth()->user()->role === 'admin' || (auth()->user()->isTeam() && auth()->user()->team_id == $game->home_team_id)))
                                    <a href="{{ route('games.play', $game->id) }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-play"></i> Speel Wedstrijd
                                    </a>
                                @endif
                                <a href="{{ route('games.show', $game->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> Bekijk
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
