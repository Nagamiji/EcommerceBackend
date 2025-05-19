<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();
            Log::info('User already authenticated, redirecting', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'session_id' => session()->getId(),
            ]);
            if ($user->is_admin) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'seller') {
                return redirect()->route('seller.dashboard');
            }
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            Log::info('Login successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'session_id' => session()->getId(),
            ]);
            if ($user->is_admin) {
                return redirect()->intended(route('admin.dashboard'));
            } elseif ($user->role === 'seller') {
                return redirect()->intended(route('seller.dashboard'));
            }
            return redirect()->intended(route('home'));
        }

        Log::warning('Login failed', [
            'email' => $request->email,
            'session_id' => session()->getId(),
        ]);
        return back()->withErrors(['email' => 'Invalid credentials'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Log::info('Logout', [
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
        ]);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}