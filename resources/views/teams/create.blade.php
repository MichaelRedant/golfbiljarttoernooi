@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Nieuw Team Toevoegen</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('teams.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Teamnaam</label>
                    <input type="text" name="name" class="form-control" id="name" placeholder="Teamnaam" required>
                </div>
                <div class="mb-3">
                    <label for="location" class="form-label">Locatie</label>
                    <input type="text" name="location" class="form-control" id="location" placeholder="Locatie">
                </div>
                <div class="mb-3">
                    <label for="club_id" class="form-label">Club</label>
                    <select name="club_id" class="form-select" id="club_id" required>
                        <option value="">Selecteer een club</option>
                        @foreach ($clubs as $club)
                            <option value="{{ $club->id }}" {{ isset($clubId) && $clubId == $club->id ? 'selected' : '' }}>
                                {{ $club->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="division_ids" class="form-label">Divisies</label>
                    <div id="division_ids">
                        @foreach ($divisions as $division)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="division_{{ $division->id }}" name="division_ids[]" value="{{ $division->id }}">
                                <label class="form-check-label" for="division_{{ $division->id }}">{{ $division->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Opslaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .form-check {
        margin-bottom: 10px;
    }
</style>
@endsection
