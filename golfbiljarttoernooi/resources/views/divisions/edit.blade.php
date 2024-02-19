<!-- resources/views/divisions/edit.blade.php -->

@extends('layouts.app')

@section('content')
    <h1>Divisie Bewerken: {{ $division->name }}</h1>

    <form action="{{ route('divisions.update', $division) }}" method="POST">
        @csrf
        @method('PUT')
        <div>
            <label for="name">Naam:</label>
            <input type="text" id="name" name="name" value="{{ $division->name }}">
        </div>
        <button type="submit">Bijwerken</button>
    </form>
@endsection
