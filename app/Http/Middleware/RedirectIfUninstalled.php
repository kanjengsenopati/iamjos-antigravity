<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfUninstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Bypass for testing environment
        if (app()->runningUnitTests()) {
            return $next($request);
        }

        // 1. Check if the app is already installed
        // Use case-insensitive checks and wrap in try-catch to handle permission or caching issues on VPS.
        $isInstalled = false;
        try {
            $isInstalled = file_exists(storage_path('installed'))
                        || file_exists(storage_path('Installed'))
                        || file_exists(storage_path('INSTALLED'));
        } catch (\Throwable $e) {
            // Fallback to true if checking file throws exception to prevent loop
            $isInstalled = true;
        }

        if ($isInstalled) {
            return $next($request);
        }

        // 2. Prevent redirect loops for installation, asset, and administrative paths.
        $path = $request->path();
        $uri = $request->getRequestUri();

        // Bypass check for AJAX, JSON, or Livewire requests
        if ($request->ajax() || $request->expectsJson() || $request->hasHeader('X-Livewire')) {
            return $next($request);
        }

        // Bypass check if request is already an installation path
        if (str_contains($path, 'install') || str_contains($uri, 'install')) {
            return $next($request);
        }

        // Bypass check for static assets
        $assetPatterns = [
            'build*', '*/build*',
            'storage*', '*/storage*',
            'vendor*', '*/vendor*',
            'livewire*', '*/livewire*',
            'assets*', '*/assets*',
            'css*', '*/css*',
            'js*', '*/js*'
        ];
        foreach ($assetPatterns as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        // Bypass check for files with typical asset extensions
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot|map|json|txt)$/i', $uri)) {
            return $next($request);
        }

        // Bypass check for global admin paths
        if ($request->is('admin*') || $request->is('*/admin*') || str_contains($path, 'admin')) {
            return $next($request);
        }

        // Bypass check for journal-specific administrative paths (e.g. mashlahah/*)
        $segments = $request->segments();
        if (!empty($segments) && $segments[0] === 'index.php') {
            array_shift($segments);
        }

        if (!empty($segments)) {
            $firstSegment = $segments[0];
            $globalSegments = [
                'login', 'register', 'logout', 'forgot-password', 'reset-password',
                'change-password', 'auth', 'select-journal', 'admin', 'build',
                'storage', 'vendor', 'livewire', 'up', 'search', 'journals',
                'about', 'page', 'files', 'sitemap.xml'
            ];

            // If the first segment is not in the global/static list, it represents a dynamic journal slug (e.g., mashlahah/*)
            if (!in_array($firstSegment, $globalSegments, true)) {
                return $next($request);
            }
        }

        // Redirect to installation wizard if not installed and path is not bypassed
        return redirect('/install');
    }
}
