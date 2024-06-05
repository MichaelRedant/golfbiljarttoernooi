@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4"><i class="fas fa-layer-group"></i> Kies een Divisie</h1>
    <div class="row">
        @forelse($divisions as $division)
            <div class="col-md-4 mb-4">
                <div class="card division-card h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ $division->name }}</h5>
                        @if($currentSeasonId)
                            <a href="{{ route('rankings.teams', ['division' => $division->id, 'season_id' => $currentSeasonId]) }}" class="btn btn-primary btn-block  mt-2">
                                <i class="fas fa-users"></i> Team Rankings
                            </a>
                            <a href="{{ route('rankings.players', ['division' => $division->id, 'season_id' => $currentSeasonId]) }}" class="btn btn-secondary btn-block  mt-2">
                                <i class="fas fa-user"></i> Player Rankings
                            </a>
                        @else
                            <p class="text-muted">Geen gespeelde wedstrijden gevonden.</p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-center">Geen divisies gevonden.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
