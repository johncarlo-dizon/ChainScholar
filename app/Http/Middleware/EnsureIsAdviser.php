<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureIsAdviser
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // If not logged in
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please sign in first.');
        }

        // Check role
        if ($user->role !== 'ADVISER') {
            // If it’s an API request, return 403 JSON; else redirect.
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            return redirect('/')->with('error', 'Unauthorized: Adviser access required.');
        }

        return $next($request);
    }
}
