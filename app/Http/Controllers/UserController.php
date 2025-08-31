<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Display all users
    // app/Http/Controllers/UserController.php

public function index(Request $request)
{
    $q    = trim((string) $request->input('q', ''));
    $role = (string) $request->input('role', '');

    // Build query
    $query = User::query()
        ->where('id', '!=', auth()->id()); // exclude current user

    // Text search: id / name / email
    if ($q !== '') {
        $query->where(function ($sub) use ($q) {
            $sub->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%");

            // If q is numeric, also allow exact ID match
            if (ctype_digit($q)) {
                $sub->orWhere('id', (int) $q);
            }
        });
    }

    // Role filter (skip if blank or 'ALL')
    if ($role !== '' && strtoupper($role) !== 'ALL') {
        $query->where('role', $role);
    }

    $users = $query
        ->orderByDesc('id')
        ->paginate(perPage: 5)                 // keep your page size
        ->appends($request->query()); // keep filters in pagination links

    // Get distinct roles for dropdown
    $roles = User::query()
        ->select('role')
        ->whereNotNull('role')
        ->distinct()
        ->orderBy('role')
        ->pluck('role');

    return view('admin.users.index', [
        'users'   => $users,
        'roles'   => $roles,
        'filters' => ['q' => $q, 'role' => $role],
    ]);
}


    // Show create user form
    public function create()
    {
        return view('admin.users.create');
    }


    public function showDashboard()
    {
        return view('admin.index');
    }

    // Store new user
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|max:255',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);


        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
    }

    // Show edit user form
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    // Update existing user
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:users,name,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|max:255',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];


        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    // Delete user
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
    }
}