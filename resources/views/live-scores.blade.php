@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Live Wedstrijden</h1>

    <!-- Formulier om scores te verversen -->
    <form action="{{ route('live-scores') }}" method="GET">
        <button type="submit" class="btn btn-primary mb-3 w-100">Refresh Scores</button>
    </form>

    @if(isset($message))
        <p>{{ $message }}</p>
    @else
        @php
            // Groepeer wedstrijden per divisie of ronde (voor cup games)
            $groupedGames = [];
            foreach($liveData as $data) {
                $groupName = $data['division_name'] ?? ($data['round_name'] ?? 'Onbekende Reeks/Ronde');
                $groupedGames[$groupName][] = $data;
            }
        @endphp

        @if(empty($groupedGames))
            <p>Er zijn geen live wedstrijden beschikbaar.</p>
        @else
            <div class="accordion" id="accordionDivisions">
                @foreach($groupedGames as $groupName => $games)
                    <div class="card mb-3">
                        <div class="card-header" id="heading-{{ Str::slug($groupName) }}">
                            <h2 class="mb-0">
                                <button class="btn btn-link d-flex justify-content-between align-items-center w-100" type="button" data-toggle="collapse" data-target="#collapse-{{ Str::slug($groupName) }}" aria-expanded="true" aria-controls="collapse-{{ Str::slug($groupName) }}">
                                    <span>{{ $groupName }}</span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h2>
                        </div>

                        <div id="collapse-{{ Str::slug($groupName) }}" class="collapse show" aria-labelledby="heading-{{ Str::slug($groupName) }}" data-parent="#accordionDivisions">
                            <div class="card-body">
                                <div class="accordion" id="accordionGames-{{ Str::slug($groupName) }}">
                                    @foreach($games as $index => $data)
                                        <div class="card mb-3">
                                            <div class="card-header" id="headingGame-{{ Str::slug($groupName) }}-{{ $index }}">
                                                <h5 class="mb-0 d-flex justify-content-between align-items-center">
                                                    <button class="btn btn-link d-flex justify-content-between align-items-center w-100" type="button" data-toggle="collapse" data-target="#collapseGame-{{ Str::slug($groupName) }}-{{ $index }}" aria-expanded="false" aria-controls="collapseGame-{{ Str::slug($groupName) }}-{{ $index }}">
                                                        <span>
                                                            {{ $data['home_team_name'] ?? 'Nog niet gestart' }} vs {{ $data['away_team_name'] ?? 'Nog niet gestart' }}
                                                            @if(isset($data['forfeit_team']))
                                                                | Forfait door {{ $data['forfeit_team'] == 'home' ? $data['home_team_name'] : $data['away_team_name'] }} (Score: {{ $data['forfeit_team'] == 'home' ? '0 - 6' : '6 - 0' }})
                                                            @else
                                                                | Score: {{ $data['home_score'] ?? '' }} - {{ $data['away_score'] ?? '' }}
                                                            @endif
                                                            @if(isset($data['round_name']))
                                                                <span class="badge badge-danger ml-2">Beker</span>
                                                            @endif
                                                        </span>
                                                        <i class="fas fa-chevron-down"></i>
                                                    </button>
                                                </h5>
                                            </div>

                                            <div id="collapseGame-{{ Str::slug($groupName) }}-{{ $index }}" class="collapse" aria-labelledby="headingGame-{{ Str::slug($groupName) }}-{{ $index }}" data-parent="#accordionGames-{{ Str::slug($groupName) }}">
                                                <div class="card-body">
                                                    <!-- Team en spelers details -->
                                                    <p><strong>Kapitein {{ $data['home_team_name'] }}:</strong> {{ $data['home_captain_name'] ?? '' }}</p>
                                                    <p><strong>Kapitein {{ $data['away_team_name'] }}:</strong> {{ $data['away_captain_name'] ?? '' }}</p>
                                                    <p><strong>Reservespeler {{ $data['home_team_name'] }}:</strong> {{ $data['home_reserve_name'] ?? '' }}</p>
                                                    <p><strong>Reservespeler {{ $data['away_team_name'] }}:</strong> {{ $data['away_reserve_name'] ?? '' }}</p>
                                                    
                                                    <!-- Scores tabel -->
                                                    <div class="table-responsive">
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
                                                                @if(isset($data['forfeit_team']))
                                                                    <tr>
                                                                        <td colspan="5" class="text-center">
                                                                            {{ $data['forfeit_team'] == 'home' ? $data['home_team_name'] : $data['away_team_name'] }} heeft forfait gegeven. De eindscore is {{ $data['forfeit_team'] == 'home' ? '0 - 6' : '6 - 0' }}.
                                                                        </td>
                                                                    </tr>
                                                                @else
                                                                    @foreach($data['scores'] ?? [] as $score)
                                                                        <tr>
                                                                            <td>{{ $score['home_player_name'] ?? 'Nog niet gestart' }}</td>
                                                                            <td>{{ $score['away_player_name'] ?? 'Nog niet gestart' }}</td>
                                                                            <td>{{ $score['1M'] ?? '' }}</td>
                                                                            <td>{{ $score['2M'] ?? '' }}</td>
                                                                            <td>{{ $score['Belle'] ?? '' }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <!-- Testmatch scores -->
                                                    @if(!empty($data['testmatch_scores']))
                                                        <h5>Testmatch Scores</h5>
                                                        <div class="table-responsive">
                                                            <table class="table table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Speler Thuis</th>
                                                                        <th>Speler Uit</th>
                                                                        <th>1M</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($data['testmatch_scores'] as $testmatch)
                                                                        <tr>
                                                                            <td>{{ $testmatch['home_player_name'] ?? 'Nog niet gestart' }}</td>
                                                                            <td>{{ $testmatch['away_player_name'] ?? 'Nog niet gestart' }}</td>
                                                                            <td>{{ $testmatch['1M'] ?? '' }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
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
