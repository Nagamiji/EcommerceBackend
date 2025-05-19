<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckIfAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $sessionId = session()->getId();
        $user = Auth::check() ? Auth::user() : null;
        $isAdmin = $user ? $user->is_admin : false;

        Log::info('CheckIfAdmin middleware: Processing request', [
            'user_authenticated' => Auth::check() ? 'yes' : 'no',
            'is_admin' => $isAdmin ? 'true' : 'false',
            'user_id' => $user ? $user->id : 'none',
            'email' => $user ? $user->email : 'none',
            'role' => $user ? $user->role : 'none',
            'session_id' => $sessionId,
            'request_path' => $request->path(),
        ]);

        if (!Auth::check() || !$isAdmin) {
            Log::warning('CheckIfAdmin middleware failed', [
                'user_id' => $user ? $user->id : 'none',
                'email' => $user ? $user->email : 'none',
                'is_admin' => $isAdmin,
                'session_id' => $sessionId,
                'session_data' => session()->all(),
            ]);
            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }

        Log::info('CheckIfAdmin middleware passed', [
            'user_id' => $user->id,
            'email' => $user->email,
            'session_id' => $sessionId,
        ]);
        return $next($request);
    }
}