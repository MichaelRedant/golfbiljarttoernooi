<!-- resources/views/news/create.blade.php -->
@extends('layouts.app')

@section('title', 'Create News')

@section('content')
<div class="container mt-4">
    <h1 class="mb-3"><i class="fas fa-newspaper"></i> Voeg Nieuws Toe</h1>

    <form action="{{ route('news.store') }}" method="POST">
        @csrf
        <div class="form-group mb-3">
            <label for="title"><i class="fas fa-heading"></i> Titel:</label>
            <input type="text" name="title" class="form-control" id="title" required>
        </div>

        <div class="form-group mb-3">
            <label for="content"><i class="fas fa-edit"></i> Inhoud:</label>
            <div id="editor"></div>
            <textarea name="content" id="hidden-content" class="form-control" rows="10" style="display: none;" required></textarea>
        </div>

        <div class="form-group mb-3 form-check form-check-inline">
            <input type="checkbox" name="is_sticky" id="is_sticky" class="form-check-input">
            <label class="form-check-label" for="is_sticky"><i class="fas fa-thumbtack"></i> Sticky</label>
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Opslaan</button>
            <a href="{{ route('news.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Terug naar Nieuws</a>
        </div>
    </form>
</div>

<!-- Include Pell Editor CSS and JS from CDN -->
<link rel="stylesheet" href="https://unpkg.com/pell/dist/pell.min.css">
<script src="https://unpkg.com/pell"></script>

<script>
    // Initialize Pell Editor
    var editor = pell.init({
        element: document.getElementById('editor'),
        onChange: function (html) {
            document.getElementById('hidden-content').value = html;
        },
        defaultParagraphSeparator: 'p',
        styleWithCSS: false
    });
</script>
@endsection
