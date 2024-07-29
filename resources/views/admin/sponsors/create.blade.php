@extends('layouts.app')

@section('header')
<h2 class="font-semibold text-xl leading-tight">
    {{ __('Nieuwe Sponsor Toevoegen') }}
</h2>
@endsection

@section('content')
<div class="container mt-4">
    <h1>Nieuwe Sponsor Toevoegen</h1>
    <form action="{{ route('sponsors.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="name">Naam</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="image">Afbeelding</label>
            <input type="file" class="form-control" id="image" name="image" required>
        </div>
        <div class="form-group">
            <label for="url">URL</label>
            <input type="url" class="form-control" id="url" name="url">
        </div>
        <button type="submit" class="btn btn-primary">Opslaan</button>
    </form>
</div>
@endsection
