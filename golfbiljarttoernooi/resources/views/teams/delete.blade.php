<!-- resources/views/teams/delete.blade.php -->

@extends('layouts.app')

@section('content')
    <h1>Team Verwijderen</h1>

    <p>Weet je zeker dat je het volgende team wilt verwijderen?</p>
    <p><strong>Naam:</strong> {{ $team->name }}</p>

    <form action="{{ route('teams.destroy', $team) }}" method="post">
        @csrf
        @method('DELETE')

        <button type="submit" class="btn btn-danger">Verwijderen</button>
    </form>

    <a href="{{ route('teams.index') }}">Annuleren</a>
@endsection
