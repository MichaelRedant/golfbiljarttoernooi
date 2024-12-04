@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-12 d-flex justify-content-between align-items-center mb-4">
            <h3>Bekers Overzicht</h3>
            <a href="{{ route('cups.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nieuwe Beker Aanmaken
            </a>
        </div>

        <div class="col-md-12">
            <table class="table table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Beker Naam</th>
                        <th>Seizoen</th>
                        <th>Divisie</th>
                        <th class="text-center">Acties</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cups as $cup)
                        <tr>
                            <td>{{ $cup->name }}</td>
                            <td>{{ $cup->season->name }}</td>
                            <td>{{ $cup->division->name }}</td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton{{ $cup->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                        Acties
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $cup->id }}">
                                        <li><a class="dropdown-item" href="{{ route('cups.show', $cup->id) }}">Bekijk Beker</a></li>
                                        <li><a class="dropdown-item" href="{{ route('cups.edit', $cup->id) }}">Bewerk Beker</a></li>
                                        <li><a class="dropdown-item" href="{{ route('cups.select-games', $cup->id) }}">Maak Wedstrijden</a></li>
                                        <li>
                                            <form action="{{ route('cups.destroy', $cup->id) }}" method="POST" onsubmit="return confirm('Weet je zeker dat je deze beker wilt verwijderen?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">Verwijder Beker</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
