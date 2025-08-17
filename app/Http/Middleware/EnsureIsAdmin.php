<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please sign in first.');
        }

        if ($user->role !== 'ADMIN') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            return redirect('/')->with('error', 'Unauthorized: Admin access required.');
        }

        return $next($request);
    }
}
