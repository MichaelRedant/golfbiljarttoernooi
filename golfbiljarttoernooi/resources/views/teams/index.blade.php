@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4"><i class="fas fa-users"></i> Teams</h1>

    <!-- Search input at the top -->
    <div class="mb-3">
        <label for="searchInput" class="form-label"><i class="fas fa-search"></i> Zoek teams:</label>
        <input type="text" id="searchInput" class="form-control" placeholder="Voer teamnaam in...">
    </div>

    <!-- Division selection -->
    <form action="{{ route('teams.index') }}" method="GET">
        <div class="mb-3">
            <label for="division" class="form-label"><i class="fas fa-layer-group"></i> Selecteer een divisie:</label>
            <select id="division" name="division" class="form-select form-control">
                <option value="">Alle divisies</option>
                @foreach($divisions as $division)
                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Toon Teams</button>
    </form>

    @if ($teams->isNotEmpty())
        <div class="mt-4">
            <h2>{{ $division->name ?? 'Geselecteerde divisie' }}</h2>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-users"></i> Naam</th>
                                    <th><i class="fas fa-map-marker-alt"></i> Locatie</th>
                                    @if(auth()->user() && auth()->user()->role === 'admin')
                                    <th><i class="fas fa-cogs"></i> Acties</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="teams-list">
                                @foreach ($teams as $team)
                                    <tr>
                                        <td><a href="{{ route('teams.show', $team) }}">{{ $team->name }}</a></td>
                                        <td>{{ $team->location }}</td>
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
    @endif

</div>    

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const teamsList = document.getElementById('teams-list');

    searchInput.oninput = function() {
        const query = this.value.toLowerCase();
        const rows = teamsList.querySelectorAll('tr');

        rows.forEach(row => {
            const teamName = row.querySelector('td a').textContent.toLowerCase();
            row.style.display = teamName.includes(query) ? '' : 'none';
        });
    };
});
</script>
@endsection
