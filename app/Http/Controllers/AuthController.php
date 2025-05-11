<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();
            \Log::info('User already authenticated, redirecting', [
                'user_email' => $user->email,
                'is_admin' => $user->is_admin,
                'role' => $user->role,
            ]);
            if ($user->is_admin) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'seller') {
                return redirect()->route('seller.dashboard');
            } else {
                return redirect()->route('home');
            }
        }

        \Log::info('User not authenticated, showing login form');
        return view('auth.login');
    }

    /**
     * Handle login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Validate the login request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        // Attempt to authenticate the user
        if (Auth::attempt($credentials)) {
            \Log::info('Login successful for user: ' . Auth::user()->email);

            // Regenerate session to prevent session fixation
            $request->session()->regenerate();

            // Redirect based on user role
            $user = Auth::user();
            if ($user->is_admin) {
                return redirect()->intended(route('admin.dashboard'));
            } elseif ($user->role === 'seller') {
                return redirect()->intended(route('seller.dashboard'));
            } else {
                return redirect()->intended(route('home'));
            }
        }

        // If authentication fails, redirect back with error
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        \Log::info('Logout triggered for user: ' . (Auth::check() ? Auth::user()->email : 'Unknown'));
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}