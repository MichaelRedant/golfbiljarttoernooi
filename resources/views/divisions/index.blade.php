@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1><i class="fas fa-layer-group"></i> Divisies</h1>
        
        @if(auth()->user() && auth()->user()->role === 'admin')
            <a href="{{ route('divisions.create') }}" class="btn btn-primary mb-2">
                <i class="fas fa-plus"></i> Nieuwe Divisie Toevoegen
            </a>
        @endif

        @if ($divisions->isEmpty())
            <p>Er zijn geen divisies beschikbaar.</p>
        @else
            <div class="accordion" id="divisionAccordion">
                @foreach ($divisions as $division)
                    <div class="card">
                        <div class="card-header" id="heading{{ $division->id }}">
                            <h2 class="mb-0 d-flex justify-content-between align-items-center">
                                <button class="btn btn-link text-dark custom-link" type="button" data-toggle="collapse" data-target="#collapse{{ $division->id }}" aria-expanded="false" aria-controls="collapse{{ $division->id }}">
                                    {{ $division->name }} <i class="fas fa-chevron-down"></i>
                                </button>
                                <div>
                                    <a href="{{ route('divisions.show', $division) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Bekijken
                                    </a>
                                    @if(auth()->user() && auth()->user()->role === 'admin')
                                        <a href="{{ route('divisions.edit', $division) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Bewerken
                                        </a>
                                        <button class="btn btn-danger btn-sm" onclick="event.preventDefault(); if(confirm('Weet je zeker dat je deze divisie wilt verwijderen?')) document.getElementById('delete-division-{{ $division->id }}').submit();">
                                            <i class="fas fa-trash-alt"></i> Verwijderen
                                        </button>
                                        <form id="delete-division-{{ $division->id }}" action="{{ route('divisions.destroy', $division) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </div>
                            </h2>
                        </div>

                        <div id="collapse{{ $division->id }}" class="collapse" aria-labelledby="heading{{ $division->id }}" data-parent="#divisionAccordion">
                            <div class="card-body">
                                <h5><i class="fas fa-users"></i> Teams in deze Divisie</h5>
                                <ul class="list-group">
                                    @foreach ($division->teams as $team)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <a href="{{ route('teams.show', $team) }}">{{ $team->name }}</a>
                                            @if(auth()->user() && auth()->user()->role === 'admin')
                                                <div>
                                                    <button class="btn btn-danger btn-sm" onclick="event.preventDefault(); if(confirm('Weet je zeker dat je dit team wilt verwijderen?')) document.getElementById('delete-team-{{ $team->id }}').submit();">
                                                        <i class="fas fa-trash-alt"></i> Verwijderen
                                                    </button>
                                                    <form id="delete-team-{{ $team->id }}" action="{{ route('teams.destroy', $team) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@section('styles')
<style>
    .custom-link {
        color: inherit;
        text-decoration: none;
    }
    .custom-link:hover {
        color: inherit;
        text-decoration: none;
    }
</style>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize all collapse elements
    $('.collapse').collapse({ toggle: false });
});
</script>
