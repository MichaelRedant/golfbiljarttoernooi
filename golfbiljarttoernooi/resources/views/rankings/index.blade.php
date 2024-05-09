@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Kies een Divisie</h1>
    <div class="row">
        @forelse($divisions as $division)
            <div class="col-md-4 mb-4">
                <div class="card division-card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $division->name }}</h5>
                        @if($currentSeasonId)
                            <a href="{{ route('teams.standings', ['divisionId' => $division->id, ]) }}" class="btn btn-primary btn-block rounded-pill mt-2">Team Rankings</a>
                            <a href="{{ route('players.standings', ['divisionId' => $division->id, ]) }}" class="btn btn-secondary btn-block rounded-pill mt-2">Player Rankings</a>
                        @else
                            <p>Geen gespeelde wedstrijden gevonden.</p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p>Geen divisies gevonden.</p>
        @endforelse
    </div>
</div>
@endsection
