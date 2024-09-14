@extends('layouts.app')

@section('title', 'Edit News')

@section('content')
<div class="container mt-4">
    <h1>Nieuwsbericht Bewerken</h1>

    <form action="{{ route('news.update', $news) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label for="title"><i class="fas fa-heading"></i> Titel:</label>
            <input type="text" name="title" class="form-control" id="title" value="{{ $news->title }}" required>
        </div>

        <div class="form-group mb-3">
            <label for="content"><i class="fas fa-edit"></i> Inhoud:</label>
            <!-- Pell Editor container -->
            <div id="editor"></div>
            <!-- Hidden textarea to store content -->
            <textarea name="content" id="hidden-content" class="form-control" rows="10" style="display: none;" required>{{ $news->content }}</textarea>
        </div>

        <div class="form-group mb-3 d-flex align-items-center">
            <input type="checkbox" name="is_sticky" id="is_sticky" class="form-check-input mr-2" {{ $news->is_sticky ? 'checked' : '' }}>
            <label for="is_sticky" class="form-check-label"><i class="fas fa-thumbtack"></i> Sticky</label>
        </div>

        <button type="submit" class="btn btn-primary">Opslaan</button>
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
            document.getElementById('hidden-content').value = html; // Sync content to hidden textarea
        },
        defaultParagraphSeparator: 'p',
        styleWithCSS: false,
        actions: [
            'bold',
            'italic',
            'underline',
            'olist',
            'ulist',
            'link',
            'image' // You can customize more actions as needed
        ],
        classes: {
            actionbar: 'pell-actionbar',
            button: 'pell-button',
            content: 'pell-content',
            selected: 'pell-button-selected'
        }
    });

    // Set existing content in Pell editor
    document.querySelector('.pell-content').innerHTML = `{!! addslashes($news->content) !!}`;
</script>
@endsection
