<!-- resources/views/users/create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1><i class="fas fa-user-plus"></i> Nieuwe Gebruiker Aanmaken</h1>
    <form action="{{ route('users.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label"><i class="fas fa-user"></i> Naam:</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label"><i class="fas fa-envelope"></i> E-mail:</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label"><i class="fas fa-lock"></i> Wachtwoord:</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label"><i class="fas fa-lock"></i> Bevestig Wachtwoord:</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label"><i class="fas fa-user-tag"></i> Rol:</label>
            <select id="role" name="role" class="form-select" required onchange="toggleTeamField()">
                <option value="user">Gebruiker</option>
                <option value="team">Team</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="mb-3" id="teamField" style="display: none;">
            <label for="team_id" class="form-label"><i class="fas fa-users"></i> Team:</label>
            <select id="team_id" name="team_id" class="form-select">
                <option value="" disabled selected>Selecteer een team</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Gebruiker Aanmaken</button>
    </form>
</div>
 
<script>
    function toggleTeamField() {
        var role = document.getElementById('role').value;
        var teamField = document.getElementById('teamField');
        var teamSelect = document.getElementById('team_id');

        if (role === 'team') {
            teamField.style.display = 'block';
            teamSelect.required = true;
        } else {
            teamField.style.display = 'none';
            teamSelect.required = false;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleTeamField(); // Call the function on page load in case the default role is 'team'
    });
</script>
@endsection
