<!-- resources/views/players/show.blade.php -->

@extends('layouts.app')

@section('content')
    <h1>Speler Details</h1>

    <p>ID: {{ $player->id }}</p>
    <p>Naam: {{ $player->name }}</p>
    <!-- Voeg hier andere details van de speler toe -->
@endsection
