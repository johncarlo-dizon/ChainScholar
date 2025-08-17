<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    //
    
    public function showRegister(){
        return view('auth.register');
    }

    public function showLogin(){
           return view('auth.login');
    }

     public function showForgotpassword(){
           return view('auth.password.email');
    }
    
    

    public function register(Request $request)
    {
        // Allow only STUDENT/ADVISER by default; ADMIN only if current user is admin.
        $allowedRoles = ['STUDENT', 'ADVISER'];
        if (Auth::check() && method_exists(Auth::user(), 'isAdmin') && Auth::user()->isAdmin()) {
            $allowedRoles[] = 'ADMIN';
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:' . implode(',', $allowedRoles),
        ]);

        // 'password' will be auto-hashed by your User::$casts
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'] ?? 'STUDENT',
        ]);

        // Send verification email
        $user->sendEmailVerificationNotification();

        return redirect()->route('show.login')
            ->with('registered', 'Registration successful! Please login after verifying your email.')
            ->with('status', 'Verification email sent! Please check your Gmail.');
    }

    public function login(Request $request)
    {
    $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

     if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

        $role = auth()->user()->role;

        // If you require verified emails, uncomment this block:
        // if (! auth()->user()->hasVerifiedEmail()) {
        //     Auth::logout();
        //     return back()->withErrors(['email' => 'Please verify your email first.']);
        // }

        return match ($role) {
            'ADMIN'   => redirect()->intended(route('admin.users.index')),
            'ADVISER' => redirect()->intended(route('adviser.index')),  // ⬅️ Adviser dashboard
            'STUDENT' => redirect()->intended(route('dashboard')),
            default   => redirect()->intended(route('dashboard')),
        };
    }

    throw ValidationException::withMessages([
        'email' => 'Sorry, incorrect credentials.',
    ]);
    }

    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('show.login');
    }
}
