<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Everything behind this middleware is scoped to the caller's own company,
 * so a user without one has nothing to see.
 */
class EnsureUserIsCompanyAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isCompanyAdmin() || $user->company_id === null) {
            abort(403, 'Only company admins can manage a team.');
        }

        return $next($request);
    }
}
