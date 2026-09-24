<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdministrator
{
    /**
     * Handle an incoming request.
     *
     * Returns 404 instead of 403 so the panel's existence is not
     * disclosed to authenticated non-administrators.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdministrator()) {
            abort(404);
        }

        return $next($request);
    }
}
