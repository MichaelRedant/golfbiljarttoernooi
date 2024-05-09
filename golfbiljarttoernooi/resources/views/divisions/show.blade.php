@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1>Divisie Details</h1>
    
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><strong>ID:</strong> {{ $division->id }}</h5>
            <h5 class="card-title"><strong>Naam:</strong> {{ $division->name }}</h5>
        </div>
    </div>

    <h2 class="mt-4">Teams in deze divisie:</h2>
    @if(auth()->check() && auth()->user()->role === 'admin')
        <a href="{{ route('teams.create') }}" class="btn btn-primary mb-2">Team Toevoegen</a>
    @endif

    @if($division->teams->isEmpty())
        <p>Er zijn momenteel geen teams in deze divisie.</p>
    @else
        <ul class="list-group">
            @foreach ($division->teams as $team)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <a href="{{ route('teams.show', $team) }}">{{ $team->name }}</a>
                @if(auth()->check() && auth()->user()->role === 'admin')
                <div class="btn-group">
                    <button class="btn btn-warning btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Verplaatsen
                    </button>
                    <ul class="dropdown-menu">
                        @if(isset($noOtherDivisions))
                        <li><span class="dropdown-item">{{ $noOtherDivisions }}</span></li>
                        @else
                        @foreach($divisions as $other_division)
                        <li>
                            <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('move-team-{{ $team->id }}-to-{{ $other_division->id }}').submit();">
                                {{ $other_division->name }}
                            </a>
                            <form id="move-team-{{ $team->id }}-to-{{ $other_division->id }}" action="{{ route('teams.move', ['team' => $team->id, 'new_division' => $other_division->id]) }}" method="POST" style="display: none;">
                                @csrf
                                @method('POST')
                            </form>
                        </li>
                        @endforeach
                        @endif
                    </ul>
                    <button class="btn btn-danger btn-sm" onclick="event.preventDefault(); document.getElementById('remove-team-{{ $team->id }}').submit();">Verwijderen</button>
                    <form id="remove-team-{{ $team->id }}" action="{{ route('teams.remove', $team) }}" method="POST" style="display: none;">
                        @csrf
                        @method('POST') <!-- Aangepast naar POST als je een aparte methode hebt voor het verwijderen -->
                    </form>
                </div>
                @endif
            </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
