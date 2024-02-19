@extends('layouts.app')

@section('content')
    <h1>Divisies</h1>

    <a href="{{ route('divisions.create') }}" class="btn btn-primary mb-2">Nieuwe Divisie Toevoegen</a>

    @if ($divisions->isEmpty())
        <p>Er zijn geen divisies beschikbaar.</p>
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
                @foreach ($divisions as $division)
                    <tr>
                        <td>{{ $division->id }}</td>
                        <td>{{ $division->name }}</td>
                        <td>
                            <a href="{{ route('divisions.show', $division) }}" class="btn btn-primary btn-sm">Bekijken</a>
                            <a href="{{ route('divisions.edit', $division) }}" class="btn btn-secondary btn-sm">Bewerken</a>
                            <a href="{{ route('divisions.destroy', $division) }}" class="btn btn-danger btn-sm"
                                onclick="event.preventDefault(); document.getElementById('delete-division-{{ $division->id }}').submit();">
                                Verwijderen
                            </a>
                            

                            <form id="delete-division-{{ $division->id }}" action="{{ route('divisions.destroy', $division) }}" method="POST" style="display: none;">
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
