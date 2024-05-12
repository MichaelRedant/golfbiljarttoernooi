@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nieuwe Club Aanmaken</h1>
    <form method="POST" action="{{ route('clubs.store') }}">
        @csrf
        <div class="form-group">
            <label for="name">Naam van de club:</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="location">Location:</label>
            <input type="text" class="form-control" id="location" name="location" placeholder="Enter location">
        </div>
        
        <button type="submit" class="btn btn-primary">Aanmaken</button>
    </form>
</div>
@endsection
