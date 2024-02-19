<?php

// app/Http/Controllers/BelleController.php

namespace App\Http\Controllers;

use App\Models\Belle;
use Illuminate\Http\Request;

class BelleController extends Controller
{
    public function index()
    {
        $belles = Belle::all();
        return view('belles.index', compact('belles'));
    }

    // Andere methoden voor BelleController
}

