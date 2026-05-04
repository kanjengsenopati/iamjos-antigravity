<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnforceOjsPrefix
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $uri = $request->getRequestUri();
        
        // If the URL doesn't already start with /index.php, redirect to it
        if (!str_starts_with($uri, '/index.php')) {
            // Exclude static assets and system paths
            if (str_starts_with($uri, '/build') || 
                str_starts_with($uri, '/storage') || 
                str_starts_with($uri, '/site') ||
                str_starts_with($uri, '/vendor') ||
                str_ends_with($uri, '.css') ||
                str_ends_with($uri, '.js') ||
                str_ends_with($uri, '.png') ||
                str_ends_with($uri, '.jpg') ||
                str_ends_with($uri, '.jpeg') ||
                str_ends_with($uri, '.gif') ||
                str_ends_with($uri, '.svg') ||
                str_ends_with($uri, '.ico')
            ) {
                return $next($request);
            }

            return redirect('/index.php' . $uri, 301);
        }

        return $next($request);
    }
}
