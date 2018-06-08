<?php

namespace App\Http\Middleware;

use Closure;

class DefineCsrfToken
{
    /**
     * Handle an incoming request.
     *
     * Definieer de CSRF token voor legacy code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!defined('CSRF_TOKEN')) {
            define('CSRF_TOKEN', csrf_token());
        }

        return $next($request);
    }
}
