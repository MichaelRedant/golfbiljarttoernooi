<!-- resources/views/news/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4"><i class="fas fa-newspaper"></i> Nieuwsbeheer</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>Titel</th>
                <th>Sticky</th>
                <th>Datum</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($news as $newsItem)
                <tr>
                    <td>{{ $newsItem->title }}</td>
                    <td class="text-center">
                        <form action="{{ route('news.toggleSticky', $newsItem) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <input type="checkbox" onchange="this.form.submit()" {{ $newsItem->is_sticky ? 'checked' : '' }}>
                        </form>
                    </td>
                    <td>{{ $newsItem->created_at->format('d-m-Y') }}</td>
                    <td class="text-center">
                        <a href="{{ route('news.edit', $newsItem) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Bewerken</a>
                        <form action="{{ route('news.destroy', $newsItem) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Weet je zeker dat je dit nieuwsbericht wilt verwijderen?')"><i class="fas fa-trash-alt"></i> Verwijder</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ route('news.create') }}" class="btn btn-primary mb-3"><i class="fas fa-plus"></i> Nieuw Nieuwsbericht</a>
</div>
@endsection
