<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function edit()
    {
        $user = auth()->user();  // Haal de ingelogde gebruiker op
        return view('profile.partials.update-profile-information-form', compact('user'));
    }
    



    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        if ($request->hasFile('profile_photo')) {
            if ($request->file('profile_photo')->isValid()) {
                // Genereer een unieke bestandsnaam
                $imageName = time() . '.' . $request->file('profile_photo')->getClientOriginalExtension();
                $path = $request->file('profile_photo')->storeAs('profile_photos', $imageName, 'public');
                $validatedData['profile_photo_path'] = $path;
            }
        }
    
        $user->update($validatedData);
    
        return redirect()->route('dashboard')->with('success', 'Profiel bijgewerkt');
    }
    





public function destroy(Request $request)
{
    $request->user()->delete();
    return redirect('/')->with('success', 'Account deleted successfully.');
}


}
