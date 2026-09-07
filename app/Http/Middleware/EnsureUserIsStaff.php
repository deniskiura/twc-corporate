<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The internal console shows every company's data, so only TWC's own staff
 * get through.
 */
class EnsureUserIsStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isStaff()) {
            abort(403, 'Only TWC staff can access the console.');
        }

        return $next($request);
    }
}
