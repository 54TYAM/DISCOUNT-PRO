<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate that ensures the current store-manager has registered a store before they
 * can manage products, discounts, or promotions. Admins (super-admins) skip the
 * check since they oversee the whole platform, not one store.
 */
class RequireStore
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isManager() && ! $user->isAdmin() && ! $user->hasStore()) {
            return redirect()->route('admin.store.create')
                ->with('info', 'Register your store before creating products or coupons.');
        }

        return $next($request);
    }
}
