<?php

namespace App\Services;

use Defuse\Crypto\Exception\CryptoException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;

class EncryptionService
{
    private static string $secretKey;

    private static string $secretIv;

    private static string $encryptMethod;

    public static function init(string $unique): void
    {
        self::$encryptMethod = Config::string('app.cipher');

        if (App::environment('testing')) {
            self::$secretKey = Config::string('encryption.key');
            self::$secretIv = Config::string('encryption.iv');
        }
        // For non-testing, try to get dynamic keys if unique is provided
        // @codeCoverageIgnoreStart
        elseif ($unique) {
            $keys = self::getEncryptionKey($unique);

            self::$secretKey = $keys['key'];
            self::$secretIv = $keys['iv'];
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Encrypt provided data with provided encryption method
     *
     * @param  string|array<string, mixed>|object  $data  The data needs to be encrypted.
     * @param  string  $unique  Optional unique identifier for encryption keys
     *
     * @codeCoverageIgnore
     */
    public static function encrypt(string|array|object $data, string $unique): string
    {
        self::init($unique);

        $iv = substr(hash('sha256', self::$secretIv), 0, 16);
        $key = substr(hash('sha256', self::$secretKey), 0, 32);

        if (is_array($data) || is_object($data)) {
            $data = http_build_query($data);
        }

        $encrypted = openssl_encrypt($data, self::$encryptMethod, $key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new CryptoException('Encryption failed');
        }

        $base64Encoded = base64_encode($encrypted);

        return $base64Encoded;
    }

    /**
     * Decrypt given hash to an array.
     * Hash needs to be an url query format
     *
     * @param  string  $hash  The encrypted data needs to be decrypted.
     * @param  string  $unique  Optional unique identifier for encryption keys
     * @return array<int|string, array<mixed>|string>
     */
    public static function decrypt(string $hash, string $unique): array
    {
        self::init($unique);

        $normalized = urldecode($hash);
        $normalized = strtr($normalized, [
            ' ' => '+',
            '-' => '+',
            '_' => '/',
        ]);

        $padLen = strlen($normalized) % 4;
        if ($padLen !== 0) {
            $normalized .= str_repeat('=', 4 - $padLen);
        }

        $iv = substr(hash('sha256', self::$secretIv), 0, 16);
        $key = substr(hash('sha256', self::$secretKey), 0, 32);

        /** @var string|false $encryptedData */
        $encryptedData = base64_decode($normalized, true);

        if ($encryptedData === false) {
            throw new CryptoException('Invalid encrypted payload');
        }

        $decrypted = openssl_decrypt($encryptedData, self::$encryptMethod, $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new CryptoException('Decryption failed');
        }

        parse_str($decrypted, $params);

        return $params;
    }

    /**
     * Generate a new encryption key and store it in Redis.
     *
     * @param  string|int|null  $userId  The user ID to associate with the encryption key
     * @param  array<string, array<int, string|null>>  $headers  HTTP headers containing authentication information.
     * @param  int  $expirationTime  Time in seconds until the key expires (default: 3600)
     * @return array{
     *      encryption: array{
     *          key: string,
     *          iv: string,
     *      },
     *      unique: string,
     * } The encryption data including key, IV, and unique identifier
     */
    public static function generateEncryptionKey(string|int|null $userId, array $headers, int $expirationTime = 3600): array
    {
        /** @var string $xApp */
        $xApp = $headers['x-app'][0];

        /** @var string $timestamp */
        $timestamp = $headers['x-timestamp'][0];

        /** @var string $signature */
        $signature = $headers['x-signature'][0];

        $publicKeyContent = self::validateSignature($xApp, $timestamp, $signature);

        $unique = Str::uuid7();

        $symmetricKey = bin2hex(random_bytes(16));
        $iv = bin2hex(random_bytes(8));

        $encryptionKey = [
            'key' => $symmetricKey,
            'iv' => $iv,
        ];

        Redis::setex("encryption_key:{$unique}", $expirationTime, json_encode($encryptionKey));

        if (! is_null($userId)) {
            $userKeysKey = "user_encryption_keys:{$userId}";
            $previousKeys = Redis::smembers($userKeysKey);

            foreach ($previousKeys as $prevKey) {
                Redis::del("encryption_key:{$prevKey}");
            }

            Redis::del($userKeysKey);
            Redis::sadd($userKeysKey, $unique);
            Redis::expire($userKeysKey, $expirationTime);
        }

        $encryptedKey = self::encryptWithPublicKey($symmetricKey, $publicKeyContent);

        return [
            'encryption' => [
                'key' => $encryptedKey,
                'iv' => $iv,
            ],
            'unique' => $unique,
        ];
    }

    /**
     * Get an encryption key from Redis by its unique identifier.
     *
     * @param  string  $unique  The unique identifier for the encryption key
     * @return array<string, string> The encryption key data or null if not found
     *
     * @throws CryptoException If the key is not found.
     *
     * @codeCoverageIgnore
     */
    public static function getEncryptionKey(string $unique): array
    {
        $keyData = Redis::get("encryption_key:{$unique}");

        if (! is_string($keyData)) {
            throw new CryptoException('Encryption not found');
        }

        /** @var array<string, string> $decoded */
        $decoded = json_decode($keyData, true, flags: JSON_THROW_ON_ERROR);

        return $decoded;
    }

    /**
     * Validates the signature using the client key and timestamp.
     *
     * @param  string  $xApp  The app code.
     * @param  string  $timestamp  The timestamp included in the signature.
     * @param  string  $signature  The provided signature to validate.
     * @return string The public key content.
     *
     * @throws CryptoException If the signature is invalid.
     */
    private static function validateSignature(string $xApp, string $timestamp, string $signature): string
    {
        $stringToSign = "$xApp|$timestamp";

        $publicKey = match (true) {
            ($xApp === 'EPITEST') => Passport::keyPath('epi-apps-test-public.key'),
            ($xApp === 'EPIMO') => Passport::keyPath('epi-mobile-public.key'), // @codeCoverageIgnore
            default => throw new CryptoException('Unauthorized. [Signature]', 401), // @codeCoverageIgnore
        };

        $publicKeyContent = file_get_contents($publicKey)
            ?: throw new CryptoException('Unauthorized. [Signature]', 401); // @codeCoverageIgnore

        openssl_verify($stringToSign, base64_decode($signature), $publicKeyContent, OPENSSL_ALGO_SHA256)
            ?: throw new CryptoException('Unauthorized. [Signature]', 401);

        return $publicKeyContent;
    }

    /**
     * Encrypt data with a public key based on the app identifier.
     *
     * @param  string  $data  The data to encrypt
     * @return string The encrypted data (base64 encoded)
     *
     * @throws CryptoException If the encryption fails or public key is not found
     */
    public static function encryptWithPublicKey(string $data, string $publicKeyContent): string
    {
        /** @var string $encrypted */
        $encrypted = '';
        $result = openssl_public_encrypt($data, $encrypted, $publicKeyContent, OPENSSL_PKCS1_PADDING);

        if (! $result) {
            throw new CryptoException('Failed to encrypt data with public key', 500); // @codeCoverageIgnore
        }

        /** @var string $encrypted */
        $encrypted = $encrypted;

        return base64_encode($encrypted);
    }
}
