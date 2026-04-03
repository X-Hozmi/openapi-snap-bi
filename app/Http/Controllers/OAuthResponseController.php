<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class OAuthResponseController extends Controller
{
    /**
     * Success response method.
     *
     * @param  array<string, mixed>  $result
     */
    public function sendResponse(array $result, string $messageCode, string $message, int $code = 200): JsonResponse
    {
        $response = [
            'responseCode' => $messageCode,
            'responseMessage' => $message,
        ];

        if (! empty($result)) {
            $response = array_merge($response, $result);
        }

        return response()->json($response, $code);
    }

    /**
     * Success response method with data key.
     *
     * @param  array<string, mixed>  $result
     *
     * @codeCoverageIgnore
     */
    public function sendResponseWithDataKey(array $result, string $messageCode, string $message, int $code = 200): JsonResponse
    {
        $response = [
            'responseCode' => $messageCode,
            'responseMessage' => $message,
            'data' => $result,
        ];

        return response()->json($response, $code);
    }

    /**
     * Return error response.
     *
     * @param  array<string, mixed>  $errorData
     *
     * @codeCoverageIgnore
     */
    public function sendError(array $errorData, string $errorCode, string $errorMessages, int $code = 404): JsonResponse
    {
        $response = [
            'responseCode' => $errorCode,
            'responseMessage' => $errorMessages,
        ];

        if (! empty($errorData)) {
            $response['data'] = $errorData;
        }

        return response()->json($response, $code);
    }

    /**
     * Return error response with data key.
     *
     * @param  array<string, mixed>  $errorData
     *
     * @codeCoverageIgnore
     */
    public function sendErrorWithDataKey(array $errorData, string $errorCode, string $errorMessages, int $code = 404): JsonResponse
    {
        $response = [
            'responseCode' => $errorCode,
            'responseMessage' => $errorMessages,
            'data' => $errorData,
        ];

        return response()->json($response, $code);
    }
}
