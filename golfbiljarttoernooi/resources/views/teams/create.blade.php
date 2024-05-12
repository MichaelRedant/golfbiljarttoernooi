@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create a New Team</h1>

    <!-- Form for creating a new team -->
    <form action="{{ route('teams.store') }}" method="POST">
        @csrf <!-- Cross-Site Request Forgery Protection -->
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
        <div class="form-group">
            <label for="club_id">Club:</label>
            <select name="club_id" class="form-control" id="club_id">
                <option value="">Select Club</option> <!-- Option for no club selected -->
                @foreach ($clubs as $club)
                    <option value="{{ $club->id }}">{{ $club->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="location">Locatie:</label>
            <input type="text" name="location" class="form-control" id="location">
        </div>
        
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
