<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            \Log::info('Login successful for user: ' . $user->email . ', is_admin: ' . ($user->is_admin ? 'true' : 'false'));

            if ($user->is_admin) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('home');
        }

        \Log::warning('Login failed for credentials: ' . json_encode($credentials));
        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function logout(Request $request)
    {
        \Log::info('Logout triggered for user: ' . Auth::user()->email);
        Auth::logout();
        return redirect()->route('login');
    }
}