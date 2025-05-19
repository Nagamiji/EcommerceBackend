<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Seller
{
    public function handle(Request $request, Closure $next)
    {
        \Log::info('Seller middleware: User authenticated: ' . (auth()->check() ? 'yes' : 'no') . ', role: ' . (auth()->check() ? auth()->user()->role : 'none'));

        if (!auth()->check() || auth()->user()->role !== 'seller') {
            \Log::warning('Seller middleware failed', ['user_id' => auth()->id() ?? 'none']);
            return redirect()->route('home')->with('error', 'Unauthorized access. You must be a seller to access this page.');
        }

        \Log::info('Seller middleware passed', ['user_id' => auth()->id()]);
        return $next($request);
    }
}