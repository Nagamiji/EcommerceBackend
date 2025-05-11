<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Seller
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'seller') {
            return $next($request);
        }

        return redirect()->route('home')->with('error', 'Unauthorized access. You must be a seller to access this page.');
    }
}