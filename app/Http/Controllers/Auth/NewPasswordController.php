<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request)
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        Log::info('Resetting password for email: '.$request->email);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => $password,  // Store as plain text
                ])->save();

                // Optionally, you can log the user in immediately after resetting the password
                Auth::login($user);
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            Log::info('Password reset successfully for email: '.$request->email);
            return redirect()->route('login')->with('status', __($status));
        } else {
            Log::warning('Password reset failed for email: '.$request->email.' with status: '.$status);
            return back()->withInput($request->only('email'))
                         ->withErrors(['email' => __($status)]);
        }
    }
}
