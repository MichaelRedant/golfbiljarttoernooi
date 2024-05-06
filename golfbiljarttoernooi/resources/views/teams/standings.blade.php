@extends('layouts.app')
@php
    $hasGames = $hasGames ?? false;
@endphp

@section('content')
<div class="container card">
    <h1>Team Klassement</h1>
    <a href="{{ route('rankings.index') }}" class="btn btn-secondary mb-3">Terug naar Rankings</a>
    <table class="table">
        <thead>
            <tr>
                <th>Team</th>
                <th>Gewonnen</th>
                <th>Verloren</th>
                <th>Gelijk</th>
                <th>Punten</th>
            </tr>
        </thead>
        <tbody>
            @if ($hasGames)
            @foreach ($teams as $team)
            <tr>
                <td colspan="5">Geen wedstrijden gevonden voor dit seizoen.</td>
            </tr>
            @endforeach
            @else
                <tr><td colspan="5">Geen wedstrijden gevonden voor dit seizoen.</td></tr>
            @endif
        
        </tbody>
    </table>
</div>
@endsection
