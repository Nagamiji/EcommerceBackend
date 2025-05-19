<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class APIAuthController extends Controller
{
    /**
     * Handle API login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
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
            \Log::info('API Login successful for user: ' . Auth::user()->email);

            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken; // Sanctum token

            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_admin' => $user->is_admin,
                ],
            ], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    /**
     * Handle API logout request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        \Log::info('API Logout triggered for user: ' . (Auth::check() ? Auth::user()->email : 'Unknown'));
        $request->user()->tokens()->delete(); // Revoke all tokens for the user
        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    /**
     * Handle API user registration (optional, if needed for frontend).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'customer', // Default role
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'is_admin' => $user->is_admin,
            ],
        ], 201);
    }

    /**
     * Handle API seller registration (optional, if needed for frontend).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerSeller(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'seller',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'is_admin' => $user->is_admin,
            ],
        ], 201);
    }

    /**
     * Get authenticated user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'status_code' => 200,
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'is_admin' => $user->is_admin,
            ]
        ], 200);
    }

    /**
     * Get list of users (for admin use).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiUsers(Request $request)
    {
        $this->middleware('admin'); // Restrict to admins
        $users = User::select('id', 'name')->get();
        return response()->json($users, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}