<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
class ClearTemplateSessionIfNotEditing
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();
        Log::info("Middleware active. Path: " . $path);

        if (!preg_match('#^documents/\d+/edit$#', $path)) {
            Log::info("Not in editor. Clearing session.");
            session()->forget('templateContent');
            session()->forget('previousEditorContent');
        }

        return $next($request);
    }
}
