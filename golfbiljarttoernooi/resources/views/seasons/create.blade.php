@extends('layouts.app')

@section('title', 'Nieuw Seizoen Toevoegen')

@section('content')
<div class="container">
    <h1>Nieuw Seizoen Toevoegen</h1>
    <form method="POST" action="{{ route('seasons.store') }}">
        @csrf
        
        <div class="form-group">
            <label for="name">Seizoensnaam</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <!-- Datumkiezer voor de startdatum van het seizoen -->
        <div class="form-group">
            <label for="start_date">Startdatum</label>
            <input type="date" class="form-control" id="start_date" name="start_date" required>
        </div>

        <div class="form-group">
            <label for="end_date">Einddatum</label>
            <input type="date" class="form-control" id="end_date" name="end_date" required>
        </div>
        

        <button type="submit" class="btn btn-primary">Opslaan</button>
        <a href="{{ route('seasons.index') }}" class="btn btn-secondary">Terug</a>
    </form>
</div>
@endsection

