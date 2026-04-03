<?php

namespace App\Validators\OAuth\ClientCredentialGrant;

use App\Exceptions\OAuthClientNotFoundException;
use App\Exceptions\SignatureInvalidException;
use App\Helpers\OAuthSnapAPIValidators;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;

/**
 * Validator class for handling B2B Authentication.
 */
class SignatureAsymmetricValidator
{
    private static int $errorCode = 4017300;

    private static string $signatureExceptionMessage = 'Unauthorized. [Signature]';

    /**
     * Processes the B2B login request.
     *
     * @param  string  $grantType  The Grant Type for Access Token.
     * @param  array<string, array<int, string|null>>  $headers  $headers  HTTP headers containing authentication information.
     * @param  string  $scope  Scopes allow your API clients to request a specific set of permissions when requesting authorization to access an account.
     * @return array{
     *      accessToken: string,
     *      tokenType: string,
     *      expiresIn: int,
     * } The generated authentication token details.
     */
    public static function processLogin(string $grantType, array $headers, string $scope): array
    {
        /** @var string $timestamp */
        $timestamp = $headers['x-timestamp'][0];

        /** @var string $clientKey */
        $clientKey = $headers['x-client-key'][0];

        /** @var string $signature */
        $signature = $headers['x-signature'][0];

        OAuthSnapAPIValidators::validateTimestamp($timestamp, self::$errorCode);

        $client = OAuthSnapAPIValidators::findClient($clientKey, true, self::$errorCode);

        self::validateSignature($clientKey, $client['public_key_file'], $timestamp, $signature);

        if (empty($scope)) {
            $scope = $client['scope'];
        }

        return self::generateToken($grantType, $clientKey, $client['secret'], $scope);
    }

    /**
     * Validates the signature using the client key and timestamp.
     *
     * @param  string  $clientKey  The client key used to generate the signature.
     * @param  string  $clientPrivateKey  The client private key used to generate the signature.
     * @param  string  $timestamp  The timestamp included in the signature.
     * @param  string  $signature  The provided signature to validate.
     *
     * @throws SignatureInvalidException If the signature is invalid.
     */
    private static function validateSignature(string $clientKey, string $clientPrivateKey, string $timestamp, string $signature): void
    {
        $stringToSign = "$clientKey|$timestamp";

        $publicKeyPath = Passport::keyPath($clientPrivateKey);

        $publicKeyContent = file_get_contents($publicKeyPath)
            ?: throw new SignatureInvalidException(self::$signatureExceptionMessage, self::$errorCode); // @codeCoverageIgnore

        openssl_verify($stringToSign, base64_decode($signature), $publicKeyContent, OPENSSL_ALGO_SHA256)
            ?: throw new SignatureInvalidException(self::$signatureExceptionMessage, self::$errorCode);
    }

    /**
     * Generates an authentication token using the client credentials.
     *
     * @param  string  $grantType  The Grant Type for Access Token.
     * @param  string  $clientKey  The client ID.
     * @param  string  $clientSecret  The client secret key.
     * @return array{
     *      accessToken: string,
     *      tokenType: string,
     *      expiresIn: int,
     * } The generated token details including access token, token type, and expiration.
     */
    private static function generateToken(string $grantType, string $clientKey, string $clientSecret, string $scope): array
    {
        $response = app()->handle(Request::create('/oauth/token', 'POST', [
            'grant_type' => $grantType,
            'client_id' => $clientKey,
            'client_secret' => $clientSecret,
            'scope' => $scope,
        ]));

        $json = $response->getContent()
            ?: throw new OAuthClientNotFoundException('Unauthorized. [Unknown client]', self::$errorCode); // @codeCoverageIgnore

        /**
         * @var array{
         *      access_token: string,
         *      token_type: string,
         *      expires_in: int,
         * } $arrayToken
         */
        $arrayToken = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        return [
            'accessToken' => $arrayToken['access_token'],
            'tokenType' => strtolower($arrayToken['token_type']),
            'expiresIn' => $arrayToken['expires_in'],
        ];
    }
}
