<?php

// app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use App\Models\News;

class HomeController extends Controller
{
    public function index()
    {
        $news = News::latest()->take(3)->get();
        return view('home', compact('news'));
    }

    public function reglement()
{
    return view('reglement');
}
}
