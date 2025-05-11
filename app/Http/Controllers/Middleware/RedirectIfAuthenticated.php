<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        \Log::info('Checking authentication state for guest middleware', [
            'path' => $request->path(),
            'method' => $request->method(),
            'is_authenticated' => Auth::check(),
            'session_id' => $request->session()->getId(),
        ]);

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::user();
                \Log::info('Authenticated user tried to access restricted page', [
                    'user' => $user->email ?? 'unknown',
                    'is_admin' => $user->is_admin,
                    'role' => $user->role,
                ]);

                if ($user->is_admin) {
                    \Log::info('Redirecting admin to dashboard', ['redirecting_to' => route('admin.dashboard')]);
                    return redirect()->route('admin.dashboard');
                } elseif ($user->role === 'seller') {
                    \Log::info('Redirecting seller to dashboard', ['redirecting_to' => route('seller.dashboard')]);
                    return redirect()->route('seller.dashboard');
                } else {
                    \Log::info('Redirecting regular user to home', ['redirecting_to' => route('home')]);
                    return redirect()->route('home');
                }
            }
        }

        \Log::info('Guest middleware passed, user is not authenticated', [
            'session_id' => $request->session()->getId(),
        ]);
        return $next($request);
    }
}