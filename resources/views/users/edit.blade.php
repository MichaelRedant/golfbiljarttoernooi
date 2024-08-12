{{-- resources/views/users/edit.blade.php --}}
@extends('layouts.app')

@section('header')
<h2 class="font-semibold text-xl leading-tight">
    {{ __('Gebruiker Bewerken') }}
</h2>
@endsection

@section('content')
<div class="container mt-4">
    <h1>{{ $user->name }} bewerken</h1>
    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Naam:</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ $user->name }}" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ $user->email }}" required>
        </div>

        <div class="form-group">
            <label for="role">Rol:</label>
            <select id="role" name="role" class="form-control" required>
                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="team" {{ $user->role == 'team' ? 'selected' : '' }}>Team</option>
                <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>User</option>
            </select>
        </div>

        <div class="form-group">
            <label for="team_id">Team:</label>
            <select id="team_id" name="team_id" class="form-control">
                <option value="">Geen Team</option>
                @if ($teams->isNotEmpty())
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}" {{ $user->team_id == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="form-group">
            <label for="password">Nieuw Wachtwoord:</label>
            <input type="password" id="password" name="password" class="form-control">
            <small class="form-text text-muted">Laat dit veld leeg als je het wachtwoord niet wilt wijzigen.</small>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Bevestig Nieuw Wachtwoord:</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Bijwerken</button>
    </form>
</div>
@endsection
