@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="container">
    <h1>Welkom bij onze Applicatie</h1>
    <nav>
        <ul>
            <li><a href="{{ route('games.index') }}">Wedstrijdkalender</a></li>
            <li><a href="{{ route('divisions.index') }}">Divisies</a></li>
            <li><a href="{{ route('teams.index') }}">Teams</a></li>
            <li><a href="{{ route('players.index') }}">Spelers</a></li>
            <!-- Voeg hier meer links toe zoals nodig -->
        </ul>
    </nav>
</div>
@endsection
