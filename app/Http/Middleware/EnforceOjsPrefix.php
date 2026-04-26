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
            return redirect('/index.php' . $uri, 301);
        }

        return $next($request);
    }
}
