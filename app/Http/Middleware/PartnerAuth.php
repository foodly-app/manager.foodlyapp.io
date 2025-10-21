<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PartnerAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log for debugging
        Log::info('PartnerAuth Middleware', [
            'has_session' => $request->hasSession(),
            'session_id' => session()->getId(),
            'partner_token_exists' => session()->has('partner_token'),
            'partner_token' => session('partner_token') ? 'exists' : 'null',
            'request_path' => $request->path(),
            'request_method' => $request->method(),
        ]);

        // Check if user has partner token in session
        if (!session('partner_token')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }
            
            return redirect()->route('login');
        }

        return $next($request);
    }
}
