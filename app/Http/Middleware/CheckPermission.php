<?php

namespace App\Http\Middleware;

use Closure;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param string $permission
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        if (!$request->user()) {
            abort(403, __('auth.permission_denied'));
        }
        if (!$request->user()->hasPermission($permission)) {
            abort(401, __('auth.permission_denied'));
        }

        return $next($request);
    }
}
