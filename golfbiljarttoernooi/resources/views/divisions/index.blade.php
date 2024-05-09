@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1>Divisies</h1>
        
        @if(auth()->user() && auth()->user()->role === 'admin')
            <a href="{{ route('divisions.create') }}" class="btn btn-primary mb-2">Nieuwe Divisie Toevoegen</a>
        @endif

        @if ($divisions->isEmpty())
            <p>Er zijn geen divisies beschikbaar.</p>
        @else
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="text-left">ID</th>
                            <th class="text-left">Naam</th>
                            <th class="text-left">Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($divisions as $division)
                            <tr>
                                <td>{{ $division->id }}</td>
                                <td>{{ $division->name }}</td>
                                <td>
                                    <a href="{{ route('divisions.show', $division) }}" class="btn btn-info btn-sm">Bekijken</a>
                                    @if(auth()->user() && auth()->user()->role === 'admin')
                                        <a href="{{ route('divisions.edit', $division) }}" class="btn btn-warning btn-sm">Bewerken</a>
                                        <button class="btn btn-danger btn-sm" onclick="event.preventDefault(); if(confirm('Weet je zeker dat je deze divisie wilt verwijderen?')) document.getElementById('delete-division-{{ $division->id }}').submit();">
                                            Verwijderen
                                        </button>
                                        <form id="delete-division-{{ $division->id }}" action="{{ route('divisions.destroy', $division) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
