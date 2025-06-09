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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user = User::create($validated);
        $user->sendEmailVerificationNotification();

        return redirect()->route('show.login')
            ->with('registered', 'Registration successful! Please login after verifying your email.')
            ->with('status', 'Verification email sent! Please check your Gmail.');
    }

    public function login(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string'
    ]);

    if (Auth::attempt($validated)) {
        $request->session()->regenerate();
        
        // Redirect based on user role
        if (auth()->user()->position === 'admin') {
            return redirect()->route('admin.home');  
        }
        
        return redirect()->route('documents.index');  
    }

    throw ValidationException::withMessages([
        'credentials' => 'Sorry, incorrect credentials'
    ]);
    }

    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('show.login');
    }
}
