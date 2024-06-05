{{-- resources/views/users/index.blade.php --}}
@extends('layouts.app')

@section('header')
<h2 class="font-semibold text-xl leading-tight">
    {{ __('Gebruikers') }}
</h2>
@endsection

@section('content')
<div class="container mt-4">
    <h1>Gebruikersbeheer</h1>
    <a href="{{ route('users.create') }}" class="btn btn-primary mb-2">
        <i class="fas fa-user-plus"></i> Nieuwe Gebruiker
    </a>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($users->isEmpty())
        <p>Er zijn geen gebruikers beschikbaar.</p>
    @else
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Naam</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Team</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role }}</td>
                            <td>{{ $user->team ? $user->team->name : '-' }}</td>
                            <td>
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Bewerken
                                </a>
                                <button class="btn btn-danger btn-sm" onclick="event.preventDefault(); if(confirm('Weet je zeker dat je deze gebruiker wilt verwijderen?')) document.getElementById('delete-user-{{ $user->id }}').submit();">
                                    <i class="fas fa-trash"></i> Verwijderen
                                </button>
                                <form id="delete-user-{{ $user->id }}" action="{{ route('users.destroy', $user) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
