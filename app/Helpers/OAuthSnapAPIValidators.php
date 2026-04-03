<?php

namespace App\Helpers;

use App\Exceptions\OAuthClientNotFoundException;
use App\Exceptions\TimestampInvalidException;
use App\Models\Oauth\OauthClientMetadata;
use Carbon\Carbon;
use Laravel\Passport\ClientRepository;

class OAuthSnapAPIValidators
{
    /**
     * Validates the timestamp to ensure it is within an acceptable range.
     *
     * @param  string  $timestamp  The timestamp to validate.
     * @param  int  $errorCode  Standard to API SNAP.
     *
     * @throws TimestampInvalidException If the timestamp is invalid or expired.
     */
    public static function validateTimestamp(string $timestamp, int $errorCode): void
    {
        $timestampDate = Carbon::createFromFormat(Carbon::ATOM, $timestamp);

        if (Carbon::now()->diffInSeconds($timestampDate, true) > config('passport.threshold.oauth-timestamp')) {
            throw new TimestampInvalidException('Unauthorized. [Timestamp]', $errorCode);
        }
    }

    /**
     * Finds a client by its key.
     *
     * @param  string  $clientKey  The client key to search for.
     * @param  int  $errorCode  Standard to API SNAP.
     *
     * @phpstan-return ($needReturnValue is true
     *     ? array{secret: string, public_key_file: string, scope: string}
     *     : null
     * )
     *
     * @throws OAuthClientNotFoundException If the client is not found or lacks a secret key.
     */
    public static function findClient(string $clientKey, bool $needReturnValue, int $errorCode): ?array
    {
        $meta = OauthClientMetadata::where('client_id', $clientKey)
            ->orWhere('client_secret', $clientKey)
            ->first();

        if (is_null($meta)) {
            throw new OAuthClientNotFoundException('Unauthorized. [Unknown client]', $errorCode);
        }

        /** @var string $clientKey */
        $clientKey = $meta->client_id;

        $client = (new ClientRepository)->find($clientKey);

        if (is_null($client)) {
            throw new OAuthClientNotFoundException('Unauthorized. [Unknown client]', $errorCode); // @codeCoverageIgnore
        }

        if ($needReturnValue) {
            return [
                'secret' => $meta->client_secret,
                'public_key_file' => $meta->public_key_file,
                'scope' => $meta->scope,
            ];
        }

        return null;
    }
}
