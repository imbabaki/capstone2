<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    /**
     * Handle an incoming request and add cache headers for faster loading
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only cache GET requests
        if ($request->isMethod('GET') && $response->getStatusCode() === 200) {
            // Cache static assets for a long time
            if ($request->is('css/*') || $request->is('js/*') || $request->is('images/*')) {
                $response->header('Cache-Control', 'public, max-age=31536000'); // 1 year
            }
            // Cache admin pages for a short time
            else if ($request->is('admin/*')) {
                $response->header('Cache-Control', 'private, max-age=0, must-revalidate');
                $response->header('Pragma', 'no-cache');
            }
        }

        return $response;
    }
}
