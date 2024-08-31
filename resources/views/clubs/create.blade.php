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
            <div class="mb-3">
                <label for="contact_person" class="form-label">Contactpersoon:</label>
                <input type="text" class="form-control" id="contact_person" name="contact_person" placeholder="Enter contact person">
            </div>
            <div class="mb-3">
                <label for="phone_number" class="form-label">Telefoonnummer:</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="Enter phone number">
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Aanmaken</button>
                <a href="{{ route('clubs.index') }}" class="btn btn-secondary mt-2">Terug</a>
            </div>
        </form>
    </div>
</div>
@endsection
