<?php
// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $sortColumn = $request->input('sort', 'name'); // Standaard sorteren op 'name'
        $sortDirection = $request->input('direction', 'asc'); // Standaard sorteer richting is 'asc'

        // Haal alleen gebruikers met de rol 'team' op, gesorteerd op de geselecteerde kolom en richting
        $users = User::with('team')
            ->where('role', 'team')
            ->orderBy($sortColumn, $sortDirection)
            ->get();

        return view('users.index', compact('users', 'sortColumn', 'sortDirection'));
    }

    public function create()
    {
        $teams = Team::all();
        return view('users.create', compact('teams'));
    }

    public function store(Request $request)
    {
        Log::info('Entering store method');

    // Probeer te valideren en log of de validatie slaagt of faalt
    try {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|max:255',
            'team_id' => 'nullable|exists:teams,id',
        ]);

        Log::info('Validated data', $validatedData);
    } catch (\Exception $e) {
        Log::error('Validation failed', ['exception' => $e->getMessage()]);
        return redirect()->back()->with('error', 'Er is een fout opgetreden bij de validatie.');
    }

    try {
        // Log de gebruiker aanmaak informatie
        Log::info('Attempting to create user', [
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'role' => $validatedData['role'],
            'team_id' => $validatedData['team_id'],
        ]);

        // Maak de gebruiker aan zonder wachtwoord hashing
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => $validatedData['password'], // Sla het wachtwoord op als platte tekst
            'role' => $validatedData['role'],
            'team_id' => $validatedData['team_id'],
            'username' => $validatedData['username'] ?? $validatedData['name'], // Voeg de username toe
        ]);

        Log::info('User created successfully', ['user_id' => $user->id]);

        return redirect()->route('users.index')->with('success', 'Gebruiker succesvol aangemaakt');

    } catch (\Exception $e) {
        Log::error('Error creating user', ['exception' => $e->getMessage()]);
        return redirect()->back()->with('error', 'Er is een fout opgetreden bij het aanmaken van de gebruiker.');
    }
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
        Log::info('Entering update method');
    
    try {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|max:255',
            'team_id' => 'nullable|exists:teams,id',
        ]);
        Log::info('Validation passed', $validatedData);
    } catch (\Exception $e) {
        Log::error('Validation failed', ['exception' => $e->getMessage()]);
        return back()->withErrors($e->getMessage());
    }

    $user->update([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'role' => $validatedData['role'],
        'team_id' => $validatedData['team_id'],
    ]);

    if (!empty($validatedData['password'])) {
        Log::info('Updating password for user', ['user_id' => $user->id]);
        $user->update(['password' => $validatedData['password']]);
    } else {
        Log::info('No password provided, skipping update', ['user_id' => $user->id]);
    }

    Log::info('User updated successfully', ['user_id' => $user->id]);

    return redirect()->route('users.index')->with('success', 'Gebruiker succesvol bijgewerkt');
    
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

