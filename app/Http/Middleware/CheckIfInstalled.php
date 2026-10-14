<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIfInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (file_exists(storage_path('installed')) || file_exists(storage_path('Installed')) || file_exists(storage_path('INSTALLED'))) {
            abort(404, 'Installation wizard is locked and unavailable.');
        }

        return $next($request);
    }
}
