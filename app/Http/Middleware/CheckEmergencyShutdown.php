<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SystemSetting;

class CheckEmergencyShutdown
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip check for admin routes and API status endpoint
        if ($request->is('admin/*') || $request->is('admin') || $request->is('api/emergency-status')) {
            return $next($request);
        }

        // Check if emergency shutdown is enabled
        if (SystemSetting::isEmergencyShutdown()) {
            return response()->view('maintenance', [], 503);
        }

        return $next($request);
    }
}
