<!-- resources/views/divisions/create.blade.php -->

@extends('layouts.app')

@section('content')
    <h1>Nieuwe Divisie Toevoegen</h1>

    <form action="{{ route('divisions.store') }}" method="POST">
        @csrf
        <div>
            <label for="name">Naam:</label>
            <input type="text" id="name" name="name">
        </div>
        <button type="submit">Toevoegen</button>
    </form>
@endsection
