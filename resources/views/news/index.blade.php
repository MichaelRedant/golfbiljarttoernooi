<!-- resources/views/news/index.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1>Nieuwsbeheer</h1>
    <a href="{{ route('news.create') }}" class="btn btn-primary mb-3">Nieuw Nieuwsbericht</a>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Titel</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($news as $newsItem)
                <tr>
                    <td>{{ $newsItem->title }}</td>
                    <td>
                        <a href="{{ route('news.edit', $newsItem) }}" class="btn btn-sm btn-warning">Bewerk</a>
                        <form action="{{ route('news.destroy', $newsItem) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Weet je zeker dat je dit nieuwsbericht wilt verwijderen?')">Verwijder</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
