{{-- resources/views/home.blade.php --}}
@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="container">
    <h1 class="text-center my-4">Welkom bij onze Biljart Applicatie</h1>

    <nav class="nav-cards">
        <div class="card">
            <a href="{{ route('games.index') }}" class="nav-card-link">Wedstrijdkalender</a>
        </div>
        <div class="card">
            <a href="{{ route('divisions.index') }}" class="nav-card-link">Divisies</a>
        </div>
        <div class="card">
            <a href="{{ route('teams.index') }}" class="nav-card-link">Teams</a>
        </div>
        <div class="card">
            <a href="{{ route('players.index') }}" class="nav-card-link">Spelers</a>
        </div>
        <!-- More cards as needed -->
    </nav>
</div>
@endsection
