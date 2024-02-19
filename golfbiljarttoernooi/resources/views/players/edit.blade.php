<!-- resources/views/players/edit.blade.php -->

@extends('layouts.app')

@section('content')
    <h1>Bewerk Speler</h1>

    <form action="{{ route('players.update', $player) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Speler Naam:</label>
            <input type="text" name="name" class="form-control" id="name" value="{{ $player->name }}" required>
        </div>
        <!-- Voeg hier andere velden toe indien nodig -->
        <button type="submit" class="btn btn-primary">Opslaan</button>
    </form>
@endsection
