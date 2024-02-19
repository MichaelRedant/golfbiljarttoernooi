<!-- resources/views/teams/create.blade.php -->

@extends('layouts.app')

@section('content')
    <h1>Maak een Nieuw Team</h1>

    <!-- Formulier voor het maken van een nieuw team -->
    <form action="{{ route('teams.store') }}" method="POST">
        @csrf <!-- Cross-site request forgery bescherming -->
        <div class="form-group">
            <label for="name">Team Name:</label>
            <input type="text" name="name" class="form-control" id="name" required>
        </div>
        <div class="form-group">
            <label for="division_id">Division:</label>
            <select name="division_id" class="form-control" id="division_id" required>
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
@endsection
