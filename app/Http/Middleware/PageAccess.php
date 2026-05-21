<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PageAccess
{
    /**
     * Gate a route by a page-access key.
     *
     * Super admins always pass. Every other user must have the given key
     * enabled in their effective page access (per-user override merged over
     * the global agent defaults).
     *
     * Usage: ->middleware('page:agents')
     */
    public function handle(Request $request, Closure $next, string $key): Response
    {
        $user = auth()->user();

        if (! $user) {
            abort(403, 'Access restricted by administrator.');
        }

        if (! $user->isSuperAdmin()) {
            $perms = $user->effectivePageAccess();
            abort_if(! ($perms[$key] ?? false), 403, 'Access restricted by administrator.');
        }

        return $next($request);
    }
}
