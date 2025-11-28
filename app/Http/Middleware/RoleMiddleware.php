<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
   public function handle($request, Closure $next, $role)
{
    if (!Auth::check()) {
        return redirect('/login');
    }

    $userRole = strtolower(Auth::user()->role);
    $requiredRole = strtolower($role);

    if ($userRole !== $requiredRole) {
        abort(403, 'Unauthorized.');
    }

    return $next($request);
}
}
