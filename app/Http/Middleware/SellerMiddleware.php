<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('web')->user();

        if (!$user) {
            \Log::warning('Seller middleware: User not authenticated');
            return redirect()->route('login');
        }

        if ($user->role !== 'seller') {
            \Log::warning('Seller middleware: User does not have seller role', ['user' => $user->email]);
            return redirect()->route('home')->with('error', 'Unauthorized access');
        }

        \Log::info('Seller middleware passed', ['user' => $user->email]);
        return $next($request);
    }
}