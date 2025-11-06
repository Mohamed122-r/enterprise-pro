<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log user activity for authenticated users
        if (Auth::check() && $this->shouldLog($request)) {
            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                ])
                ->log('visited ' . $request->route()->getName() ?? $request->path());
        }

        return $response;
    }

    protected function shouldLog(Request $request): bool
    {
        // Don't log for these methods or paths
        return !in_array($request->method(), ['OPTIONS', 'HEAD']) &&
               !str_starts_with($request->path(), 'api/');
    }
}