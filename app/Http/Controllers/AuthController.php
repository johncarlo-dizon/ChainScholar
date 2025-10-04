<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** Views */
    public function showRegister()       { return view('auth.register'); }
    public function showLogin()          { return view('auth.login'); }
    public function showForgotpassword() { return view('auth.passwords.email'); } // normalize path

    /** POST: /register */
    public function register(Request $request)
    {
        // Allowed roles: guests can only be STUDENT or ADVISER. ADMIN only by an existing admin user.
        $allowedRoles = ['STUDENT', 'ADVISER'];
        if (Auth::check() && method_exists(Auth::user(), 'isAdmin') && Auth::user()->isAdmin()) {
            $allowedRoles[] = 'ADMIN';
        }

        $validated = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
            'role'     => ['required', Rule::in($allowedRoles)],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'] ?? 'STUDENT',
        ]);

        // Fire standard event so Laravel’s verification flow behaves consistently
        event(new Registered($user));

        // Send verification email (your custom notification still works via User::sendEmailVerificationNotification)
        $user->sendEmailVerificationNotification();

        return redirect()->route('show.login')
            ->with('status', 'Registration successful! Please verify your email to continue.');
    }

    /** POST: /login */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        // Attempt login
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Sorry, incorrect credentials.',
            ]);
        }

        $request->session()->regenerate();

        // Enforce verification before letting them in
        // If not yet verified, KEEP THEM LOGGED IN and send to the verify notice
        if (! auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('status', 'Please verify your email. You’re signed in—click the link in your inbox or resend below.');
        }


        // Role-based landing
        $role = auth()->user()->role;
        return match ($role) {
            'ADMIN'   => redirect()->intended(route('admin.users.index')),
            'ADVISER' => redirect()->intended(route('adviser.index')),
            'STUDENT' => redirect()->intended(route('dashboard')),
            default   => redirect()->intended(route('dashboard')),
        };
    }

    /** POST: /logout */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('show.login');
    }
}
