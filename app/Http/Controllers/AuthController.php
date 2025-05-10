<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'message' => 'User registered successfully!',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            \Log::error("Registration error: " . $e->getMessage());
            return response()->json(['error' => 'Something went wrong during registration.'], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            \Log::error('Login validation failed', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                \Log::error('Invalid credentials for login', ['email' => $request->email]);
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            \Log::error('JWT generation failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Could not create token'], 500);
        }

        $user = JWTAuth::user();
        \Log::info('Login successful', ['user_id' => $user->id, 'token' => $token]);

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Logged out successfully.']);
        } catch (JWTException $e) {
            \Log::error('Logout failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to logout'], 500);
        }
    }

    public function registerSeller(Request $request)
    {
        // Similar validation and creation logic for seller
        // Placeholder for now
        return response()->json(['message' => 'Seller registration not implemented yet'], 501);
    }
}