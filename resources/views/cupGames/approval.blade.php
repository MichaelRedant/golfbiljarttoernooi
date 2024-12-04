@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Wedstrijddetails voor 
        @if(optional($game->homeTeam)->exists)
            <a href="{{ route('teams.show', optional($game->homeTeam)->id) }}">{{ optional($game->homeTeam)->name }}</a> 
        @else
            <span>Team niet beschikbaar</span>
        @endif
        vs 
        @if(optional($game->awayTeam)->exists)
            <a href="{{ route('teams.show', optional($game->awayTeam)->id) }}">{{ optional($game->awayTeam)->name }}</a>
        @else
            <span>Team niet beschikbaar</span>
        @endif
    </h3>

    <table class="table table-bordered mt-4">
        <tbody>
            <tr>
                <th>Club Thuisploeg</th>
                <td>
                    @if(optional($game->homeTeam)->exists && optional($game->homeTeam->club)->exists)
                        <a href="{{ route('clubs.show', optional($game->homeTeam->club)->id) }}">{{ optional($game->homeTeam->club)->name }}</a>
                    @else
                        <span>Club niet beschikbaar</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Thuisploeg</th>
                <td>
                    @if(optional($game->homeTeam)->exists)
                        <a href="{{ route('teams.show', optional($game->homeTeam)->id) }}">{{ optional($game->homeTeam)->name }}</a>
                    @else
                        <span>Team niet beschikbaar</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Club Bezoekers</th>
                <td>
                    @if(optional($game->awayTeam)->exists && optional($game->awayTeam->club)->exists)
                        <a href="{{ route('clubs.show', optional($game->awayTeam->club)->id) }}">{{ optional($game->awayTeam->club)->name }}</a>
                    @else
                        <span>Club niet beschikbaar</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Bezoekers</th>
                <td>
                    @if(optional($game->awayTeam)->exists)
                        <a href="{{ route('teams.show', optional($game->awayTeam)->id) }}">{{ optional($game->awayTeam)->name }}</a>
                    @else
                        <span>Team niet beschikbaar</span>
                    @endif
                </td>
            </tr>
            <tr> 
                <th>Locatie</th>
                <td>
                    @if(optional($game->homeTeam)->exists)
                        <i class="fas fa-map-marker-alt"></i> {{ optional($game->homeTeam)->location }}
                    @else
                        <span>Locatie niet beschikbaar</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Datum</th>
                <td>{{ \Carbon\Carbon::parse($game->date)->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <th>Wedstrijdscore</th>
                <td>
                    @php
                        $liveData = $game->liveScore ? json_decode($game->liveScore->data, true) : null;
                    @endphp
                    @if($liveData)
                        <span class="badge">{{ $liveData['home_score'] ?? 'N/A' }}</span> - 
                        <span class="badge">{{ $liveData['away_score'] ?? 'N/A' }}</span>
                    @else
                        <span class="badge">{{ $game->home_score ?? 'N/A' }}</span> - 
                        <span class="badge">{{ $game->away_score ?? 'N/A' }}</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Kapitein Thuis</th>
                <td>{{ $liveData['home_captain_name'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Kapitein Uit</th>
                <td>{{ $liveData['away_captain_name'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Reservespeler Thuis</th>
                <td>{{ $liveData['home_reserve_name'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Reservespeler Uit</th>
                <td>{{ $liveData['away_reserve_name'] ?? 'N/A' }}</td>
            </tr>
        </tbody>
    </table>

    @if(!empty($liveData['scores']))
    <div class="mt-4">
        <h4>Individuele Manches</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Speler Thuis</th>
                    <th>Speler Uit</th>
                    <th>1M</th>
                    <th>2M</th>
                    <th>Belle</th>
                </tr>
            </thead>
            <tbody>
                @foreach($liveData['scores'] as $score)
                    <tr>
                        <td>{{ $score['home_player_name'] ?? 'Onbekend' }}</td>
                        <td>{{ $score['away_player_name'] ?? 'Onbekend' }}</td>
                        <td>{{ $score['1M'] ?? 'N/A' }}</td>
                        <td>{{ $score['2M'] ?? 'N/A' }}</td>
                        <td>{{ $score['Belle'] ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(!empty($liveData['testmatch_scores']))
    <div class="mt-4">
        <h4>Testmatch Scores</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Speler Thuis</th>
                    <th>Speler Uit</th>
                    <th>1M</th>
                </tr>
            </thead>
            <tbody>
                @foreach($liveData['testmatch_scores'] as $testmatch)
                    <tr>
                        <td>{{ $testmatch['home_player_name'] ?? 'Onbekend' }}</td>
                        <td>{{ $testmatch['away_player_name'] ?? 'Onbekend' }}</td>
                        <td>{{ $testmatch['1M'] ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="mt-4">
        @if($game->division)
            <a href="{{ route('divisions.show', ['division' => $game->division->id]) }}" class="btn btn-primary">Terug naar Wedstrijdkalender</a>
        @else
            <a href="#" onclick="history.back()" class="btn btn-primary">Terug</a>
        @endif
    </div>

    <form action="{{ route('cupGames.approve', ['cup' => $game->cup_id, 'game' => $game->id]) }}" method="POST" class="mt-4">
        @csrf
        @if(auth()->check() && (auth()->user()->team_id === $game->away_team_id || auth()->user()->role === 'admin'))
            <button type="submit" class="btn btn-success">Bevestig Goedkeuring</button>
        @endif
    </form>
    
</div>
@endsection
