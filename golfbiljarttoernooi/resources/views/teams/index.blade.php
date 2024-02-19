<!-- resources/views/teams/index.blade.php -->

@extends('layouts.app')

@section('content')
    <h1>Teams</h1>

    <a href="{{ route('teams.create') }}" class="btn btn-primary mb-2">Nieuw Team Toevoegen</a>

    @if ($teams->isEmpty())
        <p>Er zijn geen teams beschikbaar.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Naam</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($teams as $team)
                    <tr>
                        <td>{{ $team->id }}</td>
                        <td>{{ $team->name }}</td>
                        <td>
                            <a href="{{ route('teams.show', $team) }}" class="btn btn-primary btn-sm">Bekijken</a>
                            <a href="{{ route('teams.edit', $team) }}" class="btn btn-secondary btn-sm">Bewerken</a>
                            <a href="{{ route('teams.destroy', $team) }}" class="btn btn-danger btn-sm"
    onclick="event.preventDefault(); document.getElementById('delete-team-{{ $team->id }}').submit();">
    Verwijderen
</a>

<form id="delete-team-{{ $team->id }}" action="{{ route('teams.destroy', $team) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
