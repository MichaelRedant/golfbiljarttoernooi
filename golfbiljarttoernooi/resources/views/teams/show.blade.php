@extends('layouts.app')

@section('content')
    <h1>Team Details</h1>

    <p><strong>ID:</strong> {{ $team->id }}</p>
    <p><strong>Naam:</strong> {{ $team->name }}</p>
    <p><strong>Divisie:</strong> {{ $team->division->name }}</p>

    <h2>Spelers</h2>
    @if($team->players->isNotEmpty())
        <ul>
            @foreach($team->players as $player)
                <li>{{ $player->first_name }} {{ $player->last_name }}</li>
            @endforeach
        </ul>
    @else
        <p>Er zijn momenteel geen spelers in dit team.</p>
    @endif

    <a href="{{ route('teams.edit', $team) }}" class="btn btn-secondary">Bewerken</a>
@endsection


