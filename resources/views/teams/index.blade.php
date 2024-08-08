@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4"><i class="fas fa-users"></i> Teams</h1>

    <!-- Search input and division selection form -->
    <form action="{{ route('teams.index') }}" method="GET">
        <div class="mb-3">
            <label for="searchInput" class="form-label"><i class="fas fa-search"></i> Zoek teams:</label>
            <input type="text" id="searchInput" name="search" class="form-control" placeholder="Voer teamnaam in..." value="{{ request('search') }}">
            <small class="form-text text-muted">Klik op "Toon Teams" om de zoekresultaten te zien.</small>
        </div>
        <div class="mb-3">
            <label for="division" class="form-label"><i class="fas fa-layer-group"></i> Selecteer een reeks:</label>
            <select id="division" name="division" class="form-select form-control">
                <option value="">Alle reeksen</option>
                @foreach($divisions as $division)
                    <option value="{{ $division->id }}" {{ request('division') == $division->id ? 'selected' : '' }}>{{ $division->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Toon Teams</button>
    </form>

    @if ($teams->isNotEmpty())
        <div class="mt-4">
            <h2>{{ $divisionName ?? 'Geselecteerde reeks' }}</h2>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="{{ route('teams.index', array_merge(request()->except('page'), ['sort_field' => 'name', 'sort_order' => request('sort_order', 'asc') == 'asc' ? 'desc' : 'asc'])) }}">
                                            <i class="fas fa-users"></i> Naam
                                            @if (request('sort_field') == 'name')
                                                <i class="fas fa-sort-{{ request('sort_order', 'asc') == 'asc' ? 'down' : 'up' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('teams.index', array_merge(request()->except('page'), ['sort_field' => 'location', 'sort_order' => request('sort_order', 'asc') == 'asc' ? 'desc' : 'asc'])) }}">
                                            <i class="fas fa-map-marker-alt"></i> Locatie
                                            @if (request('sort_field') == 'location')
                                                <i class="fas fa-sort-{{ request('sort_order', 'asc') == 'asc' ? 'down' : 'up' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('teams.index', array_merge(request()->except('page'), ['sort_field' => 'club_name', 'sort_order' => request('sort_order', 'asc') == 'asc' ? 'desc' : 'asc'])) }}">
                                            <i class="fas fa-building"></i> Club
                                            @if (request('sort_field') == 'club_name')
                                                <i class="fas fa-sort-{{ request('sort_order', 'asc') == 'asc' ? 'down' : 'up' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    @if(auth()->user() && auth()->user()->role === 'admin')
                                        <th><i class="fas fa-cogs"></i> Acties</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($teams as $team)
                                    <tr>
                                        <td><a href="{{ route('teams.show', $team) }}">{{ $team->name }}</a></td>
                                        <td>{{ $team->location }}</td>
                                        <td>
                                            @if($team->club)
                                                <a href="{{ route('clubs.show', $team->club->id) }}">{{ $team->club->name }}</a>
                                            @else
                                                Geen club
                                            @endif
                                        </td>
                                        @if(auth()->user() && auth()->user()->role === 'admin')
                                            <td>
                                                <a href="{{ route('teams.edit', $team) }}" class="btn btn-sm btn-info"><i class="fas fa-edit"></i> Bewerken</a>
                                                <form action="{{ route('teams.destroy', $team) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Weet je zeker dat je dit team wilt verwijderen?')">
                                                        <i class="fas fa-trash-alt"></i> Verwijderen
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @else
        <p>Geen teams gevonden.</p>
    @endif
</div>
@endsection
