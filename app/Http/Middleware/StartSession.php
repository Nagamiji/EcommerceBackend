public function handle($request, Closure $next)
{
    \Log::info('Session started', [
        'session_id' => $request->session()->getId(),
        'authenticated' => Auth::check() ? Auth::user()->email : 'Not authenticated'
    ]);
    return $next($request);
}