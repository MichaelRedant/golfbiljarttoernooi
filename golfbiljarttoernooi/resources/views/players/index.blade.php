@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Spelerslijst</h1>

    @php
        // Groepeer de spelers per team
        $playersByTeam = $players->sortBy('last_name')->groupBy('team.name');
    @endphp

    @foreach ($playersByTeam as $teamName => $players)
        <h2>{{ $teamName ?? 'Niet toegewezen' }}</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Divisie</th>
                    <th>Gespeelde Wedstrijden</th>
                    <th>Gewonnen Matchpunten</th>
                    <th>Gewonnen Belles</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($players as $player)
                    <tr>
                        <td>{{ $player->first_name }} {{ $player->last_name }}</td>
                        <td>{{ $player->division->name ?? 'Niet toegewezen' }}</td>
                        <td>{{ $player->games->count() }}</td>
                        <td>{{ $player->games->sum('pivot.is_winner') }}</td> <!-- Aannemende dat is_winner een veld is in de pivot tabel -->
                        <td>{{ $player->games->sum('pivot.is_belle_winner') }}</td>
                        <td>
                            <a href="{{ route('players.show', $player->id) }}" class="btn btn-primary">Bekijk</a>
                            <a href="{{ route('players.edit', $player->id) }}" class="btn btn-warning">Bewerk</a>
                            <form action="{{ route('players.destroy', $player->id) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Weet je zeker dat je deze speler wilt verwijderen?')">Verwijder</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <a href="{{ route('players.create') }}" class="btn btn-primary">Nieuwe Speler Toevoegen</a>
</div>
@endsection
