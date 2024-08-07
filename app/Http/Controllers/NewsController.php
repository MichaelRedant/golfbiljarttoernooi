<?php
// app/Http/Controllers/NewsController.php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::orderBy('is_sticky', 'desc')->orderBy('created_at', 'desc')->get();
        return view('news.index', compact('news'));
    }

    public function create()
    {
        return view('news.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'is_sticky' => 'sometimes|boolean',
        ]);

        News::create([
            'title' => $request->title,
            'content' => $request->content,
            'is_sticky' => $request->is_sticky ?? false,
        ]);

        return redirect()->route('news.index')->with('success', 'News item created successfully.');
    }

    public function edit(News $news)
    {
        return view('news.edit', compact('news'));
    }

    public function update(Request $request, News $news)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
        ]);

        $news->update($request->all());

        return redirect()->route('news.index')->with('success', 'Nieuwsbericht succesvol bijgewerkt.');
    }

    public function destroy(News $news)
    {
        $news->delete();
        return redirect()->route('news.index')->with('success', 'Nieuwsbericht succesvol verwijderd.');
    }

    public function toggleSticky(News $news)
    {
        $news->update(['is_sticky' => !$news->is_sticky]);
        return redirect()->route('news.index')->with('success', 'Sticky status succesvol bijgewerkt.');
    }
}
