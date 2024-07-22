<!-- resources/views/news/edit.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1>Nieuwsbericht Bewerken</h1>

    <form action="{{ route('news.update', $news) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label for="title">Titel:</label>
            <input type="text" name="title" class="form-control" id="title" value="{{ $news->title }}" required>
        </div>

        <div class="form-group mb-3">
            <label for="content">Inhoud:</label>
            <textarea name="content" class="form-control" id="content" rows="10" required>{{ $news->content }}</textarea>
        </div>

        <div class="form-group mb-3">
            <label for="is_sticky"><i class="fas fa-thumbtack"></i> Sticky:</label>
            <input type="checkbox" name="is_sticky" id="is_sticky" class="form-check-input" {{ $news->is_sticky ? 'checked' : '' }}>
        </div>

        <button type="submit" class="btn btn-primary">Opslaan</button>
    </form>
</div>
@endsection
