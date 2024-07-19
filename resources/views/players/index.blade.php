@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4"><i class="fas fa-users"></i> Spelerslijst</h1>

    <!-- Search form at the top -->
    <form action="{{ route('players.index') }}" method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="query" id="searchInput" class="form-control" placeholder="Voer spelernaam in..." value="{{ request('query') }}">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Zoek</button>
        </div>
        <small class="form-text text-muted">Klik op "Zoek" om de zoekresultaten te zien.</small>
    </form>

    <!-- Division selection -->
    <form action="{{ route('players.index') }}" method="GET" id="divisionForm" class="mb-3">
        <label for="divisionSelect" class="form-label"><i class="fas fa-layer-group"></i> Kies een divisie:</label>
        <select name="division_id" id="divisionSelect" class="form-select form-control" onchange="document.getElementById('divisionForm').submit()">
            <option value="">Kies een divisie</option>
            @foreach ($divisions as $division)
                <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>{{ $division->name }}</option>
            @endforeach
        </select>
    </form>

    <!-- Team selection -->
    @if(request('division_id'))
        <form action="{{ route('players.index') }}" method="GET" id="teamForm" class="mb-3">
            <input type="hidden" name="division_id" value="{{ request('division_id') }}">
            <label for="teamSelect" class="form-label"><i class="fas fa-users-cog"></i> Kies een team:</label>
            <select name="team_id" id="teamSelect" class="form-select form-control" onchange="document.getElementById('teamForm').submit()">
                <option value="">Selecteer een team</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}" {{ request('team_id') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                @endforeach
            </select>
        </form>
    @endif

    <!-- Player list -->
    @if($players->isEmpty())
        <p>Geen spelers gevonden.</p>
    @else
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Plaats Dit Seizoen</th>
                        <th>Naam</th>
                        <th>Team</th>
                        @if(auth()->user() && auth()->user()->role === 'admin')
                        <th>Acties</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($players as $player)
                        <tr>
                            <td>{{ $player->rank }}</td>
                            <td>
                                <a href="{{ route('players.show', $player->id) }}">{{ $player->name }}</a>
                            </td>
                            <td>
                                <a href="{{ route('teams.show', $player->team_id) }}">{{ $player->team_name }}</a>
                            </td>
                            @if(auth()->user() && auth()->user()->role === 'admin')
                            <td>
                                <a href="{{ route('players.edit', $player->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> Bewerken
                                </a>
                            </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
