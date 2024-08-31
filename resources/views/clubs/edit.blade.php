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
            <input type="text" class="form-control" id="location" name="location" value="{{ $club->location }}" placeholder="Vul club locatie in">
        </div>

        <div class="mb-3">
            <label for="contact_person" class="form-label">Contactpersoon:</label>
            <input type="text" class="form-control" id="contact_person" name="contact_person" value="{{ $club->contact_person }}" placeholder="Vul contactpersoon in">
        </div>
        <div class="mb-3">
            <label for="phone_number" class="form-label">Telefoonnummer:</label>
            <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ $club->phone_number }}" placeholder="Vul telefoonnummer in">
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
