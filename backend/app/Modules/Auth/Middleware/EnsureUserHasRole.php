<?php

namespace App\Modules\Auth\Middleware;

use App\Modules\Shared\Exceptions\ForbiddenApiException;
use App\Modules\Shared\Exceptions\UnauthorizedApiException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            throw new UnauthorizedApiException();
        }

        $roleName = $user->role?->name;

        if (!$roleName || !\in_array($roleName, $roles, true)) {
            throw new ForbiddenApiException();
        }

        return $next($request);
    }
}
