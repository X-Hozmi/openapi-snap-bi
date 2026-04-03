<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * TransferVAResponseController handles the generation of JSON responses
 */
class TransferVAResponseController extends Controller
{
    /**
     * Generate a JSON response for a Virtual Account inquiry.
     *
     * @param  object|null  $result  Input data for the response. If a key is missing, a default value is used.
     * @param  string  $messageCode  A string representing the response code.
     * @param  string  $message  A message providing details about the response.
     * @param  int  $code  The HTTP status code of the response. Defaults to 200.
     * @return JsonResponse A JSON-formatted response containing Virtual Account data.
     */
    public function sendResponse(?object $result, string $messageCode, string $message, int $code = 200): JsonResponse
    {
        $resultArray = $result instanceof JsonResource ? $result->resolve() : (array) $result;

        $response = [
            'responseCode' => $messageCode,
            'responseMessage' => $message,
        ];

        return response()->json($response + $resultArray, $code);
    }
}
