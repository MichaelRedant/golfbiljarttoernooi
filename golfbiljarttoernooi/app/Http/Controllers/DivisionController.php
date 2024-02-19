<?php

// app/Http/Controllers/DivisionController.php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index()
    {
        $divisions = Division::all();
        return view('divisions.index', ['divisions' => $divisions]);
    }

    public function show(Division $division)
    {
        // Laad de teams die bij deze divisie horen
        $division->load('teams');

        return view('divisions.show', compact('division'));
    }

    public function create()
    {
        return view('divisions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Division::create($request->all());
        return redirect()->route('divisions.index');
    }

    public function edit(Division $division)
    {
        return view('divisions.edit', ['division' => $division]);
    }

    public function update(Request $request, Division $division)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $division->update($request->all());
        return redirect()->route('divisions.index');
    }

    public function delete(Division $division)
    {
        return view('divisions.delete', ['division' => $division]);
    }

    public function destroy(Division $division)
    {
        $division->delete();
        return redirect()->route('divisions.index');
    }
}

