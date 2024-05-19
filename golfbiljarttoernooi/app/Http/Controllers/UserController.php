<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('team')->get();
        return view('users.index', compact('users'));
    }

    public function edit()
    {
        $user = auth()->user();
        return view('users.edit', compact('user'));
    }
    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($request->file('profile_photo')->isValid()) {
                $imageName = time() . '.' . $request->file('profile_photo')->getClientOriginalExtension();
                $path = $request->file('profile_photo')->storeAs('profile_photos', $imageName, 'public');
                $validatedData['profile_photo_path'] = $path;
            }
        }

        if (!empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }

        $user->update($validatedData);

        return redirect()->route('dashboard')->with('success', 'Profiel bijgewerkt');
    }


    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Gebruiker succesvol verwijderd');
    }

    public function create()
    {
        $teams = Team::all();
        return view('users.create', compact('teams'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|max:255',
            'team_id' => 'nullable|exists:teams,id',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);
        User::create($validatedData);

        return redirect()->route('users.index')->with('success', 'Gebruiker succesvol aangemaakt');
    }
}
