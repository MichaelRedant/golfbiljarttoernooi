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

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if ($users->isEmpty())
        <p>Er zijn geen gebruikers beschikbaar.</p>
    @else
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>
                            <a href="{{ route('users.index', ['sort' => 'name', 'direction' => $sortColumn == 'name' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">
                                Naam
                                @if ($sortColumn == 'name')
                                    <i class="fas fa-sort-{{ $sortDirection == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>Email</th>
                        <th>
                            <a href="{{ route('users.index', ['sort' => 'team_id', 'direction' => $sortColumn == 'team_id' && $sortDirection == 'asc' ? 'desc' : 'asc']) }}">
                                Team
                                @if ($sortColumn == 'team_id')
                                    <i class="fas fa-sort-{{ $sortDirection == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
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
