@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Live Wedstrijden</h1>

    <form action="{{ route('live-scores') }}" method="GET">
        <button type="submit" class="btn btn-primary mb-3 w-100">Refresh Scores</button>
    </form>

    @if(isset($message))
        <p>{{ $message }}</p>
    @else
        @php
            $divisions = [];
            foreach($liveData as $data) {
                // Geen filter meer voor alleen de huidige datum
                $divisionName = $data['division_name'] ?? 'Onbekende Reeks';
                $divisions[$divisionName][] = $data;
            }
        @endphp

        @if(empty($divisions))
            <p>Er zijn geen live wedstrijden beschikbaar.</p>
        @else
            <div class="accordion" id="accordionDivisions">
                @foreach($divisions as $divisionName => $games)
                    <div class="card mb-3">
                        <div class="card-header" id="heading-{{ Str::slug($divisionName) }}">
                            <h2 class="mb-0">
                                <button class="btn btn-link d-flex justify-content-between align-items-center w-100" type="button" data-toggle="collapse" data-target="#collapse-{{ Str::slug($divisionName) }}" aria-expanded="true" aria-controls="collapse-{{ Str::slug($divisionName) }}">
                                    <span>{{ $divisionName }}</span>
                                    <i class="fas fa-chevron-down"></i>
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
                                                    <button class="btn btn-link d-flex justify-content-between align-items-center w-100" type="button" data-toggle="collapse" data-target="#collapseGame-{{ Str::slug($divisionName) }}-{{ $index }}" aria-expanded="false" aria-controls="collapseGame-{{ Str::slug($divisionName) }}-{{ $index }}">
                                                        <span>
                                                            {{ $data['home_team_name'] ?? 'Nog niet gestart' }} vs {{ $data['away_team_name'] ?? 'Nog niet gestart' }}
                                                            @if(isset($data['forfeit_team']))
                                                                | Forfait door {{ $data['forfeit_team'] == 'home' ? $data['home_team_name'] : $data['away_team_name'] }} (Score: {{ $data['forfeit_team'] == 'home' ? '0 - 6' : '6 - 0' }})
                                                            @else
                                                                | Score: {{ $data['home_score'] ?? '' }} - {{ $data['away_score'] ?? '' }}
                                                            @endif
                                                        </span>
                                                        <i class="fas fa-chevron-down"></i>
                                                    </button>
                                                </h5>
                                            </div>

                                            <div id="collapseGame-{{ Str::slug($divisionName) }}-{{ $index }}" class="collapse" aria-labelledby="headingGame-{{ Str::slug($divisionName) }}-{{ $index }}" data-parent="#accordionGames-{{ Str::slug($divisionName) }}">
                                                <div class="card-body">
                                                    <p><strong>Kapitein {{ $data['home_team_name'] }}:</strong> {{ $data['home_captain_name'] ?? '' }}</p>
                                                    <p><strong>Kapitein {{ $data['away_team_name'] }}:</strong> {{ $data['away_captain_name'] ?? '' }}</p>
                                                    <p><strong>Reservespeler {{ $data['home_team_name'] }}:</strong> {{ $data['home_reserve_name'] ?? '' }}</p>
                                                    <p><strong>Reservespeler {{ $data['away_team_name'] }}:</strong> {{ $data['away_reserve_name'] ?? '' }}</p>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
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
                                                                @if(isset($data['forfeit_team']))
                                                                    <tr>
                                                                        <td colspan="7" class="text-center">
                                                                            {{ $data['forfeit_team'] == 'home' ? $data['home_team_name'] : $data['away_team_name'] }} heeft forfait gegeven. De eindscore is {{ $data['forfeit_team'] == 'home' ? '0 - 6' : '6 - 0' }}.
                                                                        </td>
                                                                    </tr>
                                                                @else
                                                                    @foreach($data['scores'] ?? [] as $score)
                                                                        <tr>
                                                                            <td>{{ $score['home_player_name'] ?? 'Nog niet gestart' }}</td>
                                                                            <td>{{ $score['home_player_team'] ?? 'Nog niet gestart' }}</td>
                                                                            <td>{{ $score['away_player_name'] ?? 'Nog niet gestart' }}</td>
                                                                            <td>{{ $score['away_player_team'] ?? 'Nog niet gestart' }}</td>
                                                                            <td>{{ $score['1M'] ?? '' }}</td>
                                                                            <td>{{ $score['2M'] ?? '' }}</td>
                                                                            <td>{{ $score['Belle'] ?? '' }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    </div>
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
    @endif
</div>
@endsection
