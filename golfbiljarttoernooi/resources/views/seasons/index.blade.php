@extends('layouts.app')

@section('title', 'Seizoenbeheer')

@section('content')
<div class="container">
    <h1>Seizoenbeheer</h1>

    <!-- Knop om nieuw seizoen toe te voegen -->
    <a href="{{ route('seasons.create') }}" class="btn btn-primary mb-3">Nieuw Seizoen Toevoegen</a>

    <!-- Seizoenen lijst -->
    <table class="table">
        <thead>
            <tr>
                <th>Naam</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($seasons as $season)
            <tr>
                <td>{{ $season->name }}</td>
                <td>
                    <a href="{{ route('seasons.edit', $season) }}" class="btn btn-info">Bewerken</a>
                   
                    <form action="{{ route('seasons.destroy', $season) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirmDelete()">Verwijderen</button>
                    </form>
                    
                </td>
            </tr>
            @endforeach
            
        </tbody>
    </table>
</div>
@section('scripts')
<script>
    function confirmDelete() {
        return confirm('Weet je zeker dat je dit seizoen wilt verwijderen? Dit zal alle bijbehorende wedstrijden en data verwijderen.');
    }
</script>
@endsection

@endsection
