<?php

// app/Http/Controllers/MancheController.php

namespace App\Http\Controllers;

use App\Models\Manche;
use Illuminate\Http\Request;

class MancheController extends Controller
{
    public function index()
    {
        $manches = Manche::all();
        return view('manches.index', compact('manches'));
    }

    // Andere methoden voor MancheController
}

