@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Live Wedstrijden</h1>

    <form action="{{ route('live-scores') }}" method="GET">
        <button type="submit" class="btn btn-primary mb-3">Refresh Scores</button>
    </form>

    @if(isset($message))
        <p>{{ $message }}</p>
    @else
        @php
            $divisions = [];
            foreach($liveData as $data) {
                $divisionName = $data['division_name'] ?? 'Onbekende Reeks';
                $divisions[$divisionName][] = $data;
            }
        @endphp

        <div class="accordion" id="accordionDivisions">
            @foreach($divisions as $divisionName => $games)
                <div class="card mb-3">
                    <div class="card-header" id="heading-{{ Str::slug($divisionName) }}">
                        <h2 class="mb-0">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse-{{ Str::slug($divisionName) }}" aria-expanded="true" aria-controls="collapse-{{ Str::slug($divisionName) }}">
                                {{ $divisionName }}
                            </button>
                        </h2>
                    </div>

                    <div id="collapse-{{ Str::slug($divisionName) }}" class="collapse show" aria-labelledby="heading-{{ Str::slug($divisionName) }}" data-parent="#accordionDivisions">
                        <div class="card-body">
                            <div class="accordion" id="accordionGames-{{ Str::slug($divisionName) }}">
                                @foreach($games as $index => $data)
                                    <div class="card mb-3">
                                        <div class="card-header" id="headingGame-{{ Str::slug($divisionName) }}-{{ $index }}">
                                            <h5 class="mb-0 d-flex justify-content-between align-items-center">
                                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseGame-{{ Str::slug($divisionName) }}-{{ $index }}" aria-expanded="false" aria-controls="collapseGame-{{ Str::slug($divisionName) }}-{{ $index }}">
                                                    {{ $data['home_team_name'] ?? 'Thuisteam onbekend' }} vs {{ $data['away_team_name'] ?? 'Uitteam onbekend' }}
                                                </button>
                                                <i class="fas fa-chevron-down"></i>
                                            </h5>
                                        </div>

                                        <div id="collapseGame-{{ Str::slug($divisionName) }}-{{ $index }}" class="collapse" aria-labelledby="headingGame-{{ Str::slug($divisionName) }}-{{ $index }}" data-parent="#accordionGames-{{ Str::slug($divisionName) }}">
                                            <div class="card-body">
                                                <p><strong>Score:</strong> {{ $data['home_score'] ?? 'N/A' }} - {{ $data['away_score'] ?? 'N/A' }}</p>
                                                <p><strong>Kapitein Thuis:</strong> {{ $data['home_captain_name'] ?? 'N/A' }}</p>
                                                <p><strong>Kapitein Uit:</strong> {{ $data['away_captain_name'] ?? 'N/A' }}</p>
                                                <p><strong>Reservespeler Thuis:</strong> {{ $data['home_reserve_name'] ?? 'N/A' }}</p>
                                                <p><strong>Reservespeler Uit:</strong> {{ $data['away_reserve_name'] ?? 'N/A' }}</p>
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>Speler Thuis</th>
                                                            <th>Team Thuis</th>
                                                            <th>Speler Uit</th>
                                                            <th>Team Uit</th>
                                                            <th>1M</th>
                                                            <th>2M</th>
                                                            <th>Belle</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($data['scores'] ?? [] as $score)
                                                            <tr>
                                                                <td>{{ $score['home_player_name'] ?? 'Onbekend' }}</td>
                                                                <td>{{ $score['home_player_team'] ?? 'Onbekend Team' }}</td>
                                                                <td>{{ $score['away_player_name'] ?? 'Onbekend' }}</td>
                                                                <td>{{ $score['away_player_team'] ?? 'Onbekend Team' }}</td>
                                                                <td>{{ $score['1M'] ?? 'N/A' }}</td>
                                                                <td>{{ $score['2M'] ?? 'N/A' }}</td>
                                                                <td>{{ $score['Belle'] ?? 'N/A' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
