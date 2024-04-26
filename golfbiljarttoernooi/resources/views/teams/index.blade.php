@extends('layouts.app')

@section('content')
<div class="card p-3">
    
<h1>Teams</h1>

<form action="{{ route('teams.index') }}" method="GET">
    <div class="card">
        <div class="card-body">
            <label for="division" class="form-label ">Selecteer een divisie:</label>
            <p>
                 <select id="division" name="division" class="form-select form-control form-select-lg mb-3">
            </p>
           
                <option value="">Alle divisies</option>
                @foreach($divisions as $division)
                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Toon Teams</button>
        </div>
    </div>
</form>

@if ($teams->isNotEmpty())
<div p-2>
    <h2>{{$division->name}}</h2>
    <div class="card">
        <div class="card-body">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Naam</th>
                            <!-- Voeg hier andere kolommen toe zoals gespeelde wedstrijden, gewonnen wedstrijden, enzovoort -->
                            <th>Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($teams as $team)
                            <tr>
                                <td><a href="{{ route('teams.show', $team) }}">{{ $team->name }}</a></td>
                                <!-- Voeg hier andere kolommen toe zoals gespeelde wedstrijden, gewonnen wedstrijden, enzovoort -->
                                <td>
                                                                    
                                    <form action="{{ route('teams.destroy', $team) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Weet je zeker dat je dit team wilt verwijderen?')">Verwijderen</button>
                                    </form>
                                </td>
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

@endsection
