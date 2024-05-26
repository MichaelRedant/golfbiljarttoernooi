@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1>Nieuwe Club Aanmaken</h1>
    <div class="card p-4 shadow-sm">
        <form method="POST" action="{{ route('clubs.store') }}">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Naam van de club:</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Locatie:</label>
                <input type="text" class="form-control" id="location" name="location" placeholder="Enter location">
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Aanmaken</button>
                <a href="{{ route('clubs.index') }}" class="btn btn-secondary mt-2">Terug</a>
            </div>
        </form>
    </div>
</div>
@endsection
