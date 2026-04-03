<?php

namespace App\Services\OAuth\Utilities;

use App\Exceptions\PrivateKeyInvalidException;
use App\Helpers\OAuthSnapAPIValidators;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Passport;

/**
 * Service class for handling Asymmetric Signature.
 */
class SignatureAsymmetricService
{
    private static int $errorCode = 4017000;

    private static string $privateKeyExceptionMessage = 'Unauthorized. [Private key]';

    /**
     * Processes the B2B signature request.
     *
     * @param  array<string, array<int, string|null>>  $headers  HTTP headers containing authentication information.
     * @return array<string, mixed> The generated authentication asymmetric signature details.
     */
    public static function processSignature(array $headers): array
    {
        /** @var string $timestamp */
        $timestamp = $headers['x-timestamp'][0];

        /** @var string $clientKey */
        $clientKey = $headers['x-client-key'][0];

        /** @var string $privateKey */
        $privateKey = $headers['private-key'][0];
        $privateKey = base64_decode($privateKey);

        OAuthSnapAPIValidators::validateTimestamp($timestamp, self::$errorCode);

        $client = OAuthSnapAPIValidators::findClient($clientKey, true, self::$errorCode);

        self::validatePrivateKey($clientKey, $client['public_key_file'], $timestamp, $privateKey);

        return self::generateSignature($timestamp, $clientKey, $privateKey);
    }

    /**
     * Validates the private key using the client key and timestamp.
     *
     * @param  string  $clientKey  The client key included in the header requests.
     * @param  string  $clientPublicKey  The client private key used to generate the signature.
     * @param  string  $timestamp  The timestamp included in the header requests.
     * @param  string  $privateKey  The provided private key to validate.
     *
     * @throws PrivateKeyInvalidException If the private key is invalid.
     */
    private static function validatePrivateKey(string $clientKey, string $clientPublicKey, string $timestamp, string $privateKey): void
    {
        $stringToSign = "$clientKey|$timestamp";
        $binarySignature = '';

        $publicKeyPath = Passport::keyPath($clientPublicKey);

        $publicKeyContent = file_get_contents($publicKeyPath)
            ?: throw new PrivateKeyInvalidException('Unauthorized. [Private key]', self::$errorCode); // @codeCoverageIgnore

        $result = @openssl_sign($stringToSign, $binarySignature, $privateKey, OPENSSL_ALGO_SHA256);

        if (! $result) {
            $opensslError = openssl_error_string();
            if ($opensslError) {
                Log::error("OpenSSL signing failed: {$opensslError}");
            }

            throw new PrivateKeyInvalidException('Unauthorized. [Private key]', self::$errorCode);
        }

        /** @var string $binarySignature */
        $binarySignature = $binarySignature;
        $encodedSignature = base64_encode($binarySignature);

        openssl_verify($stringToSign, base64_decode($encodedSignature), $publicKeyContent, OPENSSL_ALGO_SHA256)
            ?: throw new PrivateKeyInvalidException(self::$privateKeyExceptionMessage, self::$errorCode);
    }

    /**
     * Generates an authentication signature using the client credentials.
     *
     * @return array<string, mixed> The generated signature details including timestamp and signature.
     */
    private static function generateSignature(string $timestamp, string $clientKey, string $privateKey): array
    {
        $stringToSign = "$clientKey|$timestamp";
        $binarySignature = '';

        openssl_sign($stringToSign, $binarySignature, $privateKey, OPENSSL_ALGO_SHA256);

        /** @var string $binarySignature */
        $binarySignature = $binarySignature;
        $signature = base64_encode($binarySignature);

        return [
            'signature' => $signature,
            'timestamp' => $timestamp,
        ];
    }
}
