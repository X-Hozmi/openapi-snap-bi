<?php

use App\Exceptions\CustomerInvoicesProcessingException;
use App\Exceptions\OAuthClientNotFoundException;
use App\Exceptions\PrivateKeyInvalidException;
use App\Exceptions\RepositoryNotFoundException;
use App\Exceptions\SignatureInvalidException;
use App\Exceptions\TimestampInvalidException;
use App\Exceptions\TransferVAException;
use App\Http\Middlewares\ForceJsonResponseMiddleware;
use App\Http\Middlewares\SwitchEnvironmentMiddleware;
use App\Http\Middlewares\UnifiedAPILoggerMiddleware;
use App\Models\Invoices\CustomerInvoicesError;
use Defuse\Crypto\Exception\CryptoException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Console\ClientCommand;
use Laravel\Passport\Console\InstallCommand;
use Laravel\Passport\Console\KeysCommand;
use Laravel\Passport\Http\Middleware\CheckTokenForAnyScope;
use Laravel\Passport\Http\Middleware\EnsureClientIsResourceOwner;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',

        /**
         * Remove /api prefix for API routes.
         * Reason: allow developers to fully customize routes/api.php
         * to be able to use any prefix they want.
         */
        apiPrefix: '',

        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        ClientCommand::class,
        InstallCommand::class,
        KeysCommand::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            /**
             * This is from laravel passport package
             */
            'client' => EnsureClientIsResourceOwner::class,

            /**
             * Mainly, this middleware is used to make sure only requests
             * from on-premise and hosting can access hook endpoints.
             */
            'internal.servers' => CheckTokenForAnyScope::using(['cloud']),
        ]);

        /**
         * Middleware for API routes.
         * prepend means that these middlewares will be executed
         * before the other middleware in the stack.
         */
        $middleware->api(prepend: [
            /**
             * Switch environment based on the request.
             * This middleware is often used on testing
             */
            SwitchEnvironmentMiddleware::class,

            /**
             * Force JSON response for API requests.
             * Well, this project main focuses is as a Back-End
             */
            ForceJsonResponseMiddleware::class,

            /**
             * Logs any incoming and outgoing requests
             * from /openapi
             */
            UnifiedAPILoggerMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $isOpenApiRequest = fn (Request $request) => $request->is('openapi/*');

        /**
         * Current standard for handling exceptions
         * Subject to change if needed
         *
         * @param  array<mixed>  $errorRecord
         */
        $renderException = function (Throwable $e, Request $request, int $defaultCode = 404, array $errorRecord = []) use ($isOpenApiRequest) {
            $errorCode = $e->getCode() ?: 4007300;

            /**
             * Exception handling as per API SNAP docs requirement
             * regarding error responses that should be returned
             */
            if ($isOpenApiRequest($request)) {
                return response()->json(array_merge([
                    'responseCode' => (string) $errorCode,
                    'responseMessage' => $defaultCode < 500 ? $e->getMessage() : 'General Error',
                ], $errorRecord), $defaultCode);
            }

            return response()->json(array_merge([
                'responseCode' => (string) $errorCode,
                'responseMessage' => $defaultCode < 500 ? $e->getMessage() : 'General Error',
            ], $errorRecord), $defaultCode);
        };

        /** When no data found during process on repositories */
        $exceptions->render(fn (RepositoryNotFoundException $e, Request $r) => $renderException($e, $r));

        /** When no data found during query */
        $exceptions->render(fn (ModelNotFoundException $e, Request $r) => $renderException($e, $r));

        /** I don't know about this one. I made this just to make sure no errors on testing */
        $exceptions->render(fn (ItemNotFoundException $e, Request $r) => $renderException($e, $r));

        /** Part of the Symfony package */
        $exceptions->render(fn (NotFoundHttpException $e, Request $r) => $renderException($e, $r));

        /** When error occurs validating form requests */
        $exceptions->render(fn (ValidationException $e, Request $r) => $renderException($e, $r, 422));

        /** When error occurs during encrypting or decrypting process */
        $exceptions->render(fn (CryptoException $e, Request $r) => $renderException($e, $r, 422));

        /** Part of the Symfony package */
        $exceptions->render(fn (AccessDeniedHttpException $e, Request $r) => $renderException($e, $r, 403));

        /** When error occurs during query to make sure that no SQL error returned on responses */
        $exceptions->render(fn (QueryException $e, Request $r) => $renderException($e, $r, 500));

        /** When error occurs during authenticating clients */
        $exceptions->render(function (AuthenticationException $e, Request $request) use ($renderException, $isOpenApiRequest) {
            if ($isOpenApiRequest($request)) {
                $path = $request->path();

                $errorCode = match (true) {
                    str_contains($path, '/inquiry') => 4012401,
                    str_contains($path, '/payment') => 4012501,
                    default => $e->getCode() ?: 4012001,
                };

                return $renderException(
                    new Exception('Invalid Token (B2B)', $errorCode),
                    $request,
                    401
                );
            }

            return $renderException($e, $request, 401);
        });

        /** When error occurs during authenticating client timestamps */
        $exceptions->render(fn (TimestampInvalidException $e, Request $r) => $renderException($e, $r, 401, $e->getRecord()));

        /** When error occurs during finding clients */
        $exceptions->render(fn (OAuthClientNotFoundException $e, Request $r) => $renderException($e, $r, 401, $e->getRecord()));

        /** When error occurs during authenticating client signatures */
        $exceptions->render(fn (SignatureInvalidException $e, Request $r) => $renderException($e, $r, 401, $e->getRecord()));

        /** When error occurs during validating client's private key */
        $exceptions->render(fn (PrivateKeyInvalidException $e, Request $r) => $renderException($e, $r, 401, $e->getRecord()));

        /**
         * Handle TransferVAException specifically for OpenAPI routes
         */
        $exceptions->render(function (TransferVAException $e, Request $request) use ($renderException) {
            $e->setRequest($request)->autoDetectContext();
            $errorResource = $e->getErrorResource();
            $errorRecord = $errorResource->resolve();

            $errorCode = (int) substr((string) $e->getCode(), 0, 3) ?: 500;

            return $renderException($e, $request, $errorCode, $errorRecord);
        });

        /** When error occurs during PHP native processes */
        $exceptions->render(fn (RuntimeException $e, Request $r) => $renderException($e, $r, 500));

        /** Final counter for handling exception */
        $exceptions->render(fn (Exception $e, Request $r) => $renderException($e, $r, 500));

        /**
         * On openapi routes, when there's error during payment processing,
         * we need to log the error and insert the record
         */
        $exceptions->report(function (CustomerInvoicesProcessingException $e) {
            Log::error('Failed to process Customer Invoices Queue record', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            try {
                $errorMessage = $e->getMessage();
                $errorData = $e->getRecord();
                unset($errorData['id']);
                $errorData['keterangan'] = "Error: $errorMessage";

                // CustomerInvoicesError::create($errorData);
                $customerInvoicesQueueError = new CustomerInvoicesError;
                $customerInvoicesQueueError->insertMapped([$errorData + [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]]);
            } catch (Exception $ex) {
                Log::error('Failed to log error in database', [
                    'error' => $ex->getMessage(),
                ]);
            }

            return null;
        })->stop();
    })->create();
