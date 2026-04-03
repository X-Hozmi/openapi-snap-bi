<?php

namespace App\Services\OAuth\Utilities;

use App\Exceptions\SignatureInvalidException;
use App\Helpers\OAuthSnapAPIValidators;
use Symfony\Component\HttpFoundation\Request;

/**
 * Service class for handling Symmetric HMAC-SHA512 Signature.
 */
class SignatureSymmetricService
{
    private static int $errorCode = 4017100;

    /**
     * Processes the B2B signature request.
     *
     * @param  array<string, array<int, string|null>>  $headers  HTTP headers containing authentication information.
     * @param  string  $requestBody  The raw body of the request.
     * @return array<string, mixed> The generated authentication symmetric signature details.
     */
    public static function processSignature(array $headers, string $requestBody): array
    {
        /** @var string $httpMethod */
        $httpMethod = $headers['x-http-method'][0];

        /** @var string $endpointUrl */
        $endpointUrl = $headers['endpoint-url'][0];

        /** @var string $accessToken */
        $accessToken = $headers['authorization'][0];
        $accessToken = self::extractAccessToken($accessToken);

        /** @var string $timestamp */
        $timestamp = $headers['x-timestamp'][0];

        /** @var string $clientSecret */
        $clientSecret = $headers['x-client-secret'][0];

        OAuthSnapAPIValidators::validateTimestamp($timestamp, self::$errorCode);

        OAuthSnapAPIValidators::findClient($clientSecret, false, self::$errorCode);

        return self::generateSignature($httpMethod, $endpointUrl, $accessToken, $requestBody, $clientSecret, $timestamp);
    }

    /**
     * Extracts the access token from the Authorization header.
     *
     * @param  string  $authHeader  The Authorization header value.
     * @return string The extracted access token.
     */
    private static function extractAccessToken(string $authHeader): string
    {
        if (preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return ''; // @codeCoverageIgnore
    }

    /**
     * Generates an authentication signature using HMAC-SHA512.
     *
     * @param  string  $httpMethod  The HTTP method.
     * @param  string  $endpointUrl  The endpoint URL.
     * @param  string  $accessToken  The access token.
     * @param  string  $requestBody  The raw body of the request.
     * @param  string  $clientSecret  The client secret.
     * @param  string  $timestamp  The timestamp.
     * @return array<string, mixed> The generated signature details.
     */
    private static function generateSignature(
        string $httpMethod,
        string $endpointUrl,
        string $accessToken,
        string $requestBody,
        string $clientSecret,
        string $timestamp
    ): array {
        $minifiedRequestBody = (($httpMethod === Request::METHOD_GET) ? '' : json_encode(json_decode($requestBody)))
            // @codeCoverageIgnoreStart
            ?: throw new SignatureInvalidException(
                'Unauthorized. [Signature]',
                self::$errorCode,
            );
        // @codeCoverageIgnoreEnd

        $hashedRequestBody = hash('sha256', $minifiedRequestBody);

        $stringToSign = implode(':', [
            strtoupper($httpMethod),
            $endpointUrl,
            $accessToken,
            strtolower($hashedRequestBody),
            $timestamp,
        ]);

        $signature = hash_hmac('sha512', $stringToSign, $clientSecret, true);
        $base64Signature = base64_encode($signature);

        return [
            'signature' => $base64Signature,
        ];
    }
}
