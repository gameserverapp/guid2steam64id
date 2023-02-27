<?php

namespace App\Http\Middleware;

use Closure;

class CheckApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $key = env('API_KEY', false);

        if(!$key) {
            return $next($request);
        }

        $header = $request->header('x-api-key', false);

        if(!$header or $header != $key) {
            return response('Invalid key', 401);
        }

        return $next($request);
    }
}
