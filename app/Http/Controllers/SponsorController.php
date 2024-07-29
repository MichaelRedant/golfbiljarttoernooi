<?php

namespace App\Http\Controllers;

use App\Models\Sponsor;
use Illuminate\Http\Request;

class SponsorController extends Controller
{
    public function index()
    {
        $sponsors = Sponsor::all();
        return view('admin.sponsors.index', compact('sponsors'));
    }

    public function create()
    {
        return view('admin.sponsors.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        'url' => 'nullable|url',
    ]);

    $imageName = time().'.'.$request->image->extension();
    $request->image->move(public_path('images/sponsors'), $imageName);

    Sponsor::create([
        'name' => $request->name,
        'image_path' => 'images/sponsors/'.$imageName,
        'url' => $request->url,
    ]);

    return redirect()->route('sponsors.index')->with('success', 'Sponsor toegevoegd.');
}


    public function edit(Sponsor $sponsor)
    {
        return view('admin.sponsors.edit', compact('sponsor'));
    }

    public function update(Request $request, Sponsor $sponsor)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'url' => 'nullable|url',
    ]);

    if ($request->hasFile('image')) {
        $imageName = time().'.'.$request->image->extension();
        $request->image->move(public_path('images/sponsors'), $imageName);
        $sponsor->image_path = 'images/sponsors/'.$imageName;
    }

    $sponsor->name = $request->name;
    $sponsor->url = $request->url; // Voeg deze regel toe
    $sponsor->save();

    return redirect()->route('sponsors.index')->with('success', 'Sponsor updated successfully.');
}

    public function destroy(Sponsor $sponsor)
    {
        $sponsor->delete();
        return redirect()->route('sponsors.index')->with('success', 'Sponsor verwijderd.');
    }
}


