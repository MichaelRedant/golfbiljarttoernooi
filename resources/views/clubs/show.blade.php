@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <h1 class="display-4">{{ $club->name }}</h1>
            <p class="lead">Locatie: {{ $club->location }}</p>
            <p>Contactpersoon: {{ $club->contact_person }}</p>
            <p>Telefoonnummer: {{ $club->phone_number }}</p>
            
            <h2 class="mt-4">Teams</h2>
            <ul class="list-group">
                @forelse ($club->teams as $team)
                    <li class="list-group-item">
                        <a href="{{ route('teams.show', $team->id) }}">{{ $team->name }}</a>
                    </li>
                @empty
                    <li class="list-group-item">Geen teams verbonden aan deze club.</li>
                @endforelse
            </ul>
        </div>
        <div class="col-md-4">
            @if(auth()->user() && auth()->user()->role === 'admin')
                <div class="mb-3">
                    <a href="{{ route('clubs.edit', $club->id) }}" class="btn btn-primary btn-block">Club Bewerken</a>
                </div>
                <form action="{{ route('clubs.destroy', $club->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">Club Verwijderen</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
