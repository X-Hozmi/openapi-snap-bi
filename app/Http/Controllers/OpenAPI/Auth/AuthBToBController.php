<?php

namespace App\Http\Controllers\OpenAPI\Auth;

use App\Http\Controllers\OAuthResponseController;
use App\Http\Requests\OpenAPI\Auth\AuthBToBRequest;
use App\Validators\OAuth\ClientCredentialGrant\SignatureAsymmetricValidator;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\HeaderParameter;
use Illuminate\Http\JsonResponse;

#[Group('Authentications', 'This Endpoint is used as a validator for authenticated users', weight: 20)]
class AuthBToBController extends OAuthResponseController
{
    /**
     * OpenAPI B2B.
     *
     * @unauthenticated
     *
     * @response array{
     *      "responseCode": "2007300",
     *      "responseMessage": "Successful",
     *      "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
     *      "tokenType": "bearer",
     *      "expiresIn": 900,
     * }
     */
    #[HeaderParameter('X-TIMESTAMP', 'yyyy-MM-ddTHH:mm:ssTZD', type: 'datetime', example: '2025-03-29T21:06:32+07:00', required: true)]
    #[HeaderParameter('X-CLIENT-KEY', 'Partner\'s Client ID', type: 'string', required: true)]
    #[HeaderParameter('X-SIGNATURE', 'base64_encode(openssl_sign(“$clientKey|$timestamp”, ‘’, $privateKey, OPENSSL_ALGO_SHA256))', type: 'string', required: true)]
    public function __invoke(AuthBToBRequest $request): JsonResponse
    {
        $headers = $request->header();

        /** @var array{grantType: string} $payloads */
        $payloads = $request->validated();

        $arrayToken = SignatureAsymmetricValidator::processLogin($payloads['grantType'], $headers, '');

        return $this->sendResponse($arrayToken, '2007300', 'Successful');
    }
}
