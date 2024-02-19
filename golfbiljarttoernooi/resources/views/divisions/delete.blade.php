<!-- resources/views/divisions/delete.blade.php -->

@extends('layouts.app')

@section('content')
    <h1>Weet je zeker dat je de divisie "{{ $division->name }}" wilt verwijderen?</h1>

    <form action="{{ route('divisions.destroy', $division) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit">Verwijderen</button>
    </form>

    <a href="{{ route('divisions.index') }}">Annuleren</a>
@endsection
