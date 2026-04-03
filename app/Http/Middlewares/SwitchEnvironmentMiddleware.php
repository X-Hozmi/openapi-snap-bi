<?php

namespace App\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SwitchEnvironmentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $originalEnv = app()->environment();

        $testingAgents = array_map('trim', explode(',', Config::string('internal.testing-user-agents', '')));

        /** @var string $userAgent */
        $userAgent = $request->header('User-Agent');

        foreach ($testingAgents as $agent) {
            // @codeCoverageIgnoreStart
            if (! empty($agent) && str_contains(strtolower($userAgent), strtolower($agent))) {
                App::detectEnvironment(function () {
                    return 'testing';
                });
                break;
            }
            // @codeCoverageIgnoreEnd
        }

        $response = $next($request);

        // @codeCoverageIgnoreStart
        if (app()->environment() !== $originalEnv) {
            App::detectEnvironment(function () use ($originalEnv) {
                return $originalEnv;
            });
        }
        // @codeCoverageIgnoreEnd

        return $response;
    }
}
