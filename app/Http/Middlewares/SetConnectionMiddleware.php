<?php

namespace App\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetConnectionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $connectionFromRoute = null): Response
    {
        $connection = 'cloud';

        if (! empty($connectionFromRoute)) {
            $connection = $connectionFromRoute;
        } elseif ($request->has('connection')) {
            $connection = $request->get('connection');
        } elseif ($request->hasHeader('x-connection')) {
            $connection = $request->header('x-connection');
        }

        app()->instance('current_connection', $connection);

        $request->attributes->set('connection', $connection);

        return $next($request);
    }
}
