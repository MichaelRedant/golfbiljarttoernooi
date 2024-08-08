<?php
// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        // Haal alle gebruikers op, behalve de eerste twee
        $users = User::with('team')->whereNotIn('id', [1, 2])->get();
        return view('users.index', compact('users'));
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

        Log::info('Storing user with raw password', ['password' => $request->password]);
 
        User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => $request->password,
            'role' => $validatedData['role'],
            'team_id' => $validatedData['team_id']
        ]);

        return redirect()->route('users.index')->with('success', 'Gebruiker succesvol aangemaakt');
    }

    public function edit(User $user)
    {
        if ($user->id <= 2) {
            return redirect()->route('users.index')->with('error', 'Deze gebruiker kan niet worden bewerkt.');
        }

        $teams = Team::all();
        return view('users.edit', compact('user', 'teams'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->id <= 2) {
            return redirect()->route('users.index')->with('error', 'Deze gebruiker kan niet worden bijgewerkt.');
        }

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
            $validatedData['password'] = $validatedData['password']; // Sla wachtwoord als plain text op
        } else {
            unset($validatedData['password']);
        }

        $user->update($validatedData);

        return redirect()->route('dashboard')->with('success', 'Profiel bijgewerkt');
    }

    public function destroy(User $user)
    {
        if ($user->id <= 2) {
            return redirect()->route('users.index')->with('error', 'Deze gebruiker kan niet worden verwijderd.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Gebruiker succesvol verwijderd');
    }
}

