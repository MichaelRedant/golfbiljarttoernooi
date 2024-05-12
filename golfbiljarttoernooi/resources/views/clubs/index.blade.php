@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Clubs</h1>
    @if(auth()->user() && auth()->user()->role === 'admin')
        <a href="{{ route('clubs.create') }}" class="btn btn-primary">Nieuwe Club</a>
    @endif
    <table class="table">
        <thead>
            <tr>
                <th>Naam</th>
                <th>Locatie</th>
                @if(auth()->user() && auth()->user()->role === 'admin')
                <th>Acties</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($clubs as $club)
            <tr>
                <td><a href="{{ route('clubs.show', $club->id) }}">{{ $club->name }}</a></td>
                <td>{{ $club->location }}</td>
                <td>
                    @if(auth()->user() && auth()->user()->role === 'admin')
                        <a href="{{ route('clubs.edit', $club->id) }}" class="btn btn-sm btn-info">Bewerken</a>
                        <form action="{{ route('clubs.destroy', $club->id) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Verwijderen</button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
