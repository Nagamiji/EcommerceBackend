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
            'guards' => $guards,
        ]);

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::user();
                \Log::info('Authenticated user tried to access restricted page', [
                    'user' => $user->email ?? 'unknown',
                    'user_id' => $user->id ?? 'unknown',
                    'is_admin' => $user->is_admin ?? false,
                    'role' => $user->role ?? 'none',
                    'session_id' => $request->session()->getId(),
                ]);

                if ($user->is_admin) {
                    \Log::info('Redirecting admin to dashboard', [
                        'redirecting_to' => route('admin.dashboard'),
                        'session_id' => $request->session()->getId(),
                    ]);
                    return redirect()->route('admin.dashboard');
                } elseif ($user->role === 'seller') {
                    \Log::info('Redirecting seller to dashboard', [
                        'redirecting_to' => route('seller.dashboard'),
                        'session_id' => $request->session()->getId(),
                    ]);
                    return redirect()->route('seller.dashboard');
                } else {
                    \Log::info('Redirecting regular user to home', [
                        'redirecting_to' => route('home'),
                        'session_id' => $request->session()->getId(),
                    ]);
                    return redirect()->route('home');
                }
            }
        }

        \Log::info('Guest middleware passed, user is not authenticated', [
            'session_id' => $request->session()->getId(),
            'path' => $request->path(),
        ]);
        return $next($request);
    }
}