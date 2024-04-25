@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Kies een Divisie</h1>
    <div class="row">
        @foreach($divisions as $division)
            <div class="col-md-4 mb-4">
                <div class="card division-card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $division->name }}</h5>
                        <a href="{{ route('team.standings', $division) }}" class="btn btn-primary btn-block rounded-pill mt-2">Team Rankings</a>
                        <a href="{{ route('players.standings', $division) }}" class="btn btn-secondary btn-block rounded-pill mt-2">Player Rankings</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

