<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show()
    {
        return view('admin.users.profile', [
            'user' => Auth::user()
        ]);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = $request->user();
        $disk = Storage::disk('public');

        // Delete old avatar if exists
        if ($user->avatar) {
            $oldPath = "avatars/{$user->avatar}";
            if ($disk->exists($oldPath)) {
                $disk->delete($oldPath);
            }
        }

        // Generate unique filename
        $extension = $request->file('avatar')->extension();
        $filename = "{$user->id}_avatar_" . time() . ".{$extension}";

        // Store new avatar
        $path = $request->file('avatar')->storeAs('avatars', $filename, 'public');
        
        // Update user record
        $user->avatar = $filename;
        $user->save();

        return back()->with('success', 'Avatar updated successfully!');
    }

    public function updateUsername(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,name,' . Auth::id(),
        ]);

        $request->user()->update([
            'name' => $request->username
        ]);

        return back()->with('success', 'Username updated successfully!');
    }

    public function updatePassword(Request $request)
    {
   
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed'
        ]);


        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'Password updated successfully!');
    }
}