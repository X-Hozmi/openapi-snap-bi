<?php

namespace App\Http\Middlewares;

use App\Models\Logger\LoggerAccessOpenAPI;
use App\Services\SanitizerService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnifiedAPILoggerMiddleware
{
    /**
     * Handle an outgoing request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $response = $next($request);
        $processingTime = (microtime(true) - $startTime) * 1000;

        $responseContent = [];

        if ($response instanceof JsonResponse) {
            $json = $response->getContent() ?: '{}';
            $responseContent = (array) json_decode($json, true);
        }

        $path = $request->path();
        $isHookEndpoint = str_starts_with($path, 'hook/');

        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'request_headers' => SanitizerService::sanitizeHeaderContent($request->headers->all()),
            'response_headers' => SanitizerService::sanitizeHeaderContent($response->headers->all()),
            'request_contents' => SanitizerService::sanitizePayloadContent($request->all(), $isHookEndpoint),
            'response_contents' => SanitizerService::sanitizePayloadContent($responseContent),
            'response_code' => $response->getStatusCode(),
            'processing_time' => $processingTime,
        ];

        // Determine which model to use based on the request path
        switch (true) {
            case str_starts_with($path, 'openapi/'):
                LoggerAccessOpenAPI::create($logData);
                break;
        }

        return $response;
    }
}
