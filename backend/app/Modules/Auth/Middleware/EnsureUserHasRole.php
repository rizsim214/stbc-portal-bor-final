<?php

namespace App\Modules\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $roleName = $user->role?->name;

        if (!$roleName || !in_array($roleName, $roles, true)) {
            abort(403, 'You do not have the required role.');
        }

        return $next($request);
    }
}

