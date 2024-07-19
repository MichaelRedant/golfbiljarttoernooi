@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1>Edit Club: {{ $club->name }}</h1>
    <form method="POST" action="{{ route('clubs.update', $club->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Club Name:</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $club->name }}" required>
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">Location:</label>
            <input type="text" class="form-control" id="location" name="location" value="{{ $club->location }}" placeholder="Enter club location">
        </div>

        <div class="mb-3">
            <label for="teams" class="form-label">Select Teams:</label>
            @foreach ($allTeams as $team)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="teams[]" value="{{ $team->id }}" id="team_{{ $team->id }}" {{ $club->teams->contains($team) ? 'checked' : '' }}>
                <label class="form-check-label" for="team_{{ $team->id }}">
                    {{ $team->name }}
                </label>
            </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('teams.create', ['club_id' => $club->id]) }}" class="btn btn-success">Create team voor {{ $club->name }}</a>
    </form>
</div>
@endsection
