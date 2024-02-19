@extends('layouts.app')

@section('content')
    <h1>Team Details</h1>

    <p><strong>ID:</strong> {{ $team->id }}</p>
    <p><strong>Naam:</strong> {{ $team->name }}</p>
    <p><strong>Divisie:</strong> {{ $team->division->name }}</p>

    <!-- Andere details van het team hieronder, afhankelijk van wat je nog wilt weergeven -->

    <a href="{{ route('teams.edit', $team) }}" class="btn btn-secondary">Bewerken</a>
@endsection
