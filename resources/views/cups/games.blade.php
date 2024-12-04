@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h3>Wedstrijden voor Beker: {{ $cup->name }}</h3>

    @if($games->isEmpty())
        <p>Er zijn nog geen wedstrijden aangemaakt voor deze beker.</p>
    @else
        @foreach($games as $round)
            <h4>Ronde: {{ $round->round_name }}</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Thuis Team</th>
                        <th>Uit Team</th>
                        <th>Datum</th>
                        <th>Score</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($round->games as $game)
                        <tr>
                            <td>{{ $game->homeTeam->name ?? 'Vrij' }}</td>
                            <td>{{ $game->awayTeam->name ?? 'Vrij' }}</td>
                            <td>{{ \Carbon\Carbon::parse($game->date)->format('d-m-Y') }}</td>
                            <td>
                                @if($game->home_score !== null && $game->away_score !== null)
                                    {{ $game->home_score }} - {{ $game->away_score }}
                                @else
                                    <em>Nog geen score ingevoerd</em>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('cups.games.update', [$cup->id, $game->id]) }}" method="POST">
                                    @csrf
                                    <div class="form-inline">
                                        <input type="number" name="home_score" class="form-control mr-1" placeholder="Thuis Score" required>
                                        <input type="number" name="away_score" class="form-control mr-1" placeholder="Uit Score" required>
                                        <button type="submit" class="btn btn-sm btn-primary">Score Bijwerken</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endif
</div>
@endsection
