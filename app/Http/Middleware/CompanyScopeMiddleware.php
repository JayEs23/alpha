<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompanyScopeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Tenant scoping is handled through model-level global scopes (HasCompanyId + CompanyScope).
        // Keep this middleware as a safe no-op for backward compatibility with route aliases.
        return $next($request);
    }
}
