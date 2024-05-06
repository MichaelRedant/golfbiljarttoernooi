@extends('layouts.app')

@section('title', 'Seizoen Bewerken')

@section('content')
<div class="container">
    <h1>Seizoen Bewerken</h1>
    <form method="POST" action="{{ route('seasons.update', $season) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Seizoensnaam</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $season->name }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Bijwerken</button>
        <a href="{{ route('seasons.index') }}" class="btn btn-secondary">Terug</a>
    </form>
</div>
@endsection
