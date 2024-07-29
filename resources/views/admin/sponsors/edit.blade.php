@extends('layouts.app')

@section('header')
<h2 class="font-semibold text-xl leading-tight">
    {{ __('Sponsor Bewerken') }}
</h2>
@endsection

@section('content')
<div class="container mt-4">
    <h1>Sponsor Bewerken</h1>
    <form action="{{ route('sponsors.update', $sponsor->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Naam</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $sponsor->name }}" required>
        </div>
        <div class="form-group">
            <label for="image">Afbeelding</label>
            <input type="file" class="form-control" id="image" name="image">
        </div>
        <div class="form-group">
            <label for="url">URL</label>
            <input type="url" class="form-control" id="url" name="url" value="{{ $sponsor->url }}">
        </div>
        <button type="submit" class="btn btn-primary">Opslaan</button>
    </form>
</div>
@endsection
