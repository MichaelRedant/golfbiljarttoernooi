@extends('layouts.app')

@section('content')
    <h1>Divisie Details</h1>

    <p><strong>ID:</strong> {{ $division->id }}</p>
    <p><strong>Naam:</strong> {{ $division->name }}</p>

    <h2>Teams in deze divisie:</h2>
    <ul>
        @foreach ($division->teams as $team)
            <li>{{ $team->name }}</li>
        @endforeach
    </ul>
@endsection
