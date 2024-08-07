@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1>Clubs</h1>
    @if(auth()->user() && auth()->user()->role === 'admin')
        <a href="{{ route('clubs.create') }}" class="btn btn-primary">Nieuwe Club</a>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>
                    <a href="{{ route('clubs.index', ['sort' => 'name', 'order' => request('order', 'asc') === 'asc' ? 'desc' : 'asc']) }}">
                        Naam
                        @if(request('sort') == 'name')
                            @if(request('order', 'asc') == 'asc')
                                &#9650; {{-- Up arrow for ascending --}}
                            @else
                                &#9660; {{-- Down arrow for descending --}}
                            @endif
                        @endif
                    </a>
                </th>
                <th>
                    <a href="{{ route('clubs.index', ['sort' => 'location', 'order' => request('order', 'asc') === 'asc' ? 'desc' : 'asc']) }}">
                        Locatie
                        @if(request('sort') == 'location')
                            @if(request('order', 'asc') == 'asc')
                                &#9650; {{-- Up arrow for ascending --}}
                            @else
                                &#9660; {{-- Down arrow for descending --}}
                            @endif
                        @endif
                    </a>
                </th>
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
                @if(auth()->user() && auth()->user()->role === 'admin')
                <td>
                    <a href="{{ route('clubs.edit', $club->id) }}" class="btn btn-sm btn-info">Bewerken</a>
                    <form action="{{ route('clubs.destroy', $club->id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Verwijderen</button>
                    </form>
                </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
