<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isManager()) {
            abort(403, 'Access restricted to store managers and above.');
        }

        return $next($request);
    }
}
