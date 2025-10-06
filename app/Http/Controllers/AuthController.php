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
    public function showLogin()
    {
        // If auth middleware sent them here from a signed verify link,
        // Laravel stores the target in session('url.intended').
        $intended = session('url.intended');
        $verifyIntent = is_string($intended) && str_contains($intended, '/email/verify/');

        return view('auth.login', [
            'verifyIntent' => $verifyIntent,
        ]);
    }

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

    // Normalize email for lookup
    $email = strtolower($credentials['email']);
    $user  = User::where('email', $email)->first();

    // 1) If account exists but is DISABLED, block (no login)
    if ($user && $user->is_active === false) {
        throw ValidationException::withMessages([
            'email' => 'This account is disabled. Please contact the administrator.',
        ]);
    }

    // 2) If credentials are correct BUT email is NOT verified, block (no login)
    //    Use Auth::validate() to check creds WITHOUT creating a session.
    if ($user && ! $user->hasVerifiedEmail() && Auth::validate($credentials)) {
        // (Optional) pass along the email so your resend page can prefill
        $request->session()->put('verify.intended_email', $email);

        // Redirect to your public "Resend Verification" page (NOT the auth-only notice).
        // ✱ If your route name/path is different, change it here.
        return redirect()->route('resend.verification')
            ->with('status', 'Please verify your email to continue. We can resend the verification link.');
    }

    // 3) Attempt login (only verified + active reach here)
    if (! Auth::attempt($credentials, $request->boolean('remember'))) {
        throw ValidationException::withMessages([
            'email' => 'Sorry, incorrect credentials.',
        ]);
    }

    $request->session()->regenerate();

    // 4) Role-based landing
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
