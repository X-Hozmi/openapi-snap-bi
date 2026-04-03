<?php

namespace App\Validators\OAuth\ClientCredentialGrant;

use App\Exceptions\SignatureInvalidException;
use App\Exceptions\TimestampInvalidException;
use App\Exceptions\TransferVAException;
use App\Models\Invoices\Views\CustomerInvoicesPaid;
use App\Models\Invoices\Views\CustomerInvoicesUnpaid;
use App\Models\Oauth\OauthClientMetadata;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Redis;

class SignatureSymmetricValidator
{
    /**
     * @var array<string, array<string, string>>
     */
    private const ERROR_CODES = [
        'inquiry' => [
            'conflict' => '4092400',
            'unauthorized_timestamp' => '4012400',
            'unauthorized_signature' => '4012400',
        ],
        'payment' => [
            'conflict' => '4092500',
            'unauthorized_timestamp' => '4012500',
            'unauthorized_signature' => '4012500',
            'inconsistent_request' => '4042518',
        ],
    ];

    /**
     * @var array<string, string>
     */
    private const ERROR_MESSAGES = [
        'conflict' => 'Conflict',
        'unauthorized_timestamp' => 'Unauthorized. [Timestamp]',
        'unauthorized_signature' => 'Unauthorized. [Signature]',
        'inconsistent_request' => 'Inconsistent Request',
    ];

    private const PAYMENT_DATA_PREFIX = 'payment_data_payment:';

    /**
     * Processes a symmetric signature request.
     *
     * @param  array<string, array<int, string|null>>  $headers  HTTP headers containing authentication information.
     * @param  string  $externalId  The External ID.
     * @param  string  $httpMethod  The HTTP method of the request.
     * @param  string  $relativeUrl  The relative URL of the request.
     * @param  string  $requestBody  The raw body of the request.
     * @param  string  $endpoint  The endpoint type ('inquiry' or 'payment').
     * @param  string|null  $paymentRequestId  Optional payment request ID for payment endpoint.
     * @return string The processed account value.
     */
    public static function processSymmetricSignature(
        array $headers,
        string $externalId,
        string $httpMethod,
        string $relativeUrl,
        string $requestBody,
        string $endpoint,
        ?string $paymentRequestId = null
    ): string {
        /** @var string $authorization */
        $authorization = $headers['authorization'][0];

        /** @var string $timestamp */
        $timestamp = $headers['x-timestamp'][0];

        /** @var string $signature */
        $signature = $headers['x-signature'][0];

        /** @var string $partnerId */
        $partnerId = $headers['x-partner-id'][0];

        /** @var string $clientSecret */
        $clientSecret = OauthClientMetadata::where('partner_id', $partnerId)->value('client_secret');

        /** @var array<string, mixed> $decodedRequestBody */
        $decodedRequestBody = json_decode($requestBody, true);

        self::validateExternalId($externalId, $endpoint, $paymentRequestId, $decodedRequestBody);

        self::validateTimestamp($timestamp, $endpoint);

        $accessToken = str_replace('Bearer ', '', $authorization);

        $minifiedBody = json_encode(json_decode((string) $requestBody))
            // @codeCoverageIgnoreStart
            ?: throw new SignatureInvalidException(
                self::ERROR_MESSAGES['unauthorized_signature'],
                (int) self::ERROR_CODES[$endpoint]['unauthorized_signature']
            );
        // @codeCoverageIgnoreEnd

        $hashedBody = hash('sha256', $minifiedBody);

        $stringToSign = implode(':', [
            strtoupper($httpMethod),
            $relativeUrl,
            $accessToken,
            strtolower($hashedBody),
            $timestamp,
        ]);

        self::validateSignature($signature, $stringToSign, $clientSecret, $endpoint);

        return '';
    }

    /**
     * @param  array<string, mixed>  $responses
     */
    public static function saveResponses(string $externalId, array $responses): void
    {
        $paymentDataKey = self::PAYMENT_DATA_PREFIX.$externalId;

        /** @var int */
        $tomorrowTimestamp = Carbon::tomorrow()->timestamp;

        Redis::set($paymentDataKey, json_encode($responses));
        Redis::expireAt($paymentDataKey, $tomorrowTimestamp);
    }

    /**
     * Validates the external ID to ensure it has not been used before.
     *
     * @param  string  $externalId  The external ID to validate.
     * @param  string  $endpoint  The endpoint type.
     * @param  string|null  $paymentRequestId  Optional payment request ID for payment endpoint.
     * @param  array<string, mixed>  $requestData  Request body data for checking customer number.
     *
     * @throws TransferVAException If the external ID is invalid or already used.
     */
    private static function validateExternalId(string $externalId, string $endpoint, ?string $paymentRequestId = null, array $requestData = []): void
    {
        $cacheKey = "external_id_{$endpoint}:{$externalId}:".Carbon::today()->format('Y-m-d');

        $errorReason = [
            'english' => 'Cannot use the same X-EXTERNAL-ID',
            'indonesia' => 'Tidak bisa menggunakan X-EXTERNAL-ID yang sama',
        ];

        /** @var int */
        $tomorrowTimestamp = Carbon::tomorrow()->timestamp;

        if ($endpoint === 'payment') {
            // For payment endpoint, also store the paymentRequestId
            $paymentDataKey = self::PAYMENT_DATA_PREFIX.$externalId;
            $paymentRequestKey = "payment_request_{$endpoint}:{$externalId}";

            // @codeCoverageIgnoreStart
            if (Redis::exists($cacheKey)) {
                if (! is_null($paymentRequestId)) {
                    /** @var string $storedPaymentRequestId */
                    $storedPaymentRequestId = Redis::get($paymentRequestKey);

                    if ($storedPaymentRequestId && $storedPaymentRequestId !== $paymentRequestId) {
                        // Different paymentRequestId - Conflict error
                        throw new TransferVAException(
                            self::ERROR_MESSAGES['conflict'],
                            (int) self::ERROR_CODES[$endpoint]['conflict'],
                            $errorReason,
                        );
                    } else {
                        // Same paymentRequestId - Inconsistent Request
                        /** @var string $paymentDataKeyRedis */
                        $paymentDataKeyRedis = Redis::get($paymentDataKey);

                        /**
                         * @var array{
                         *      virtualAccountData: array{
                         *          paymentFlagReason: array{
                         *              english: string,
                         *              indonesia: string,
                         *          },
                         *          paymentFlagStatus: string,
                         *      },
                         * }|null $originalResponse
                         */
                        $originalResponse = json_decode($paymentDataKeyRedis, true);

                        if (! is_null($originalResponse)) {
                            throw new TransferVAException(
                                self::ERROR_MESSAGES['inconsistent_request'],
                                (int) self::ERROR_CODES[$endpoint]['inconsistent_request'],
                                (array) $originalResponse['virtualAccountData']['paymentFlagReason'],
                                (string) $originalResponse['virtualAccountData']['paymentFlagStatus']
                            );
                        }

                        /** @var string $customerNo */
                        $customerNo = $requestData['customerNo'];

                        /** @var Collection<int, CustomerInvoicesPaid> $accountDatasPaid */
                        $accountDatasPaid = CustomerInvoicesPaid::getRowsOfAccountDataByCustomerNo($customerNo);

                        /** @var Collection<int, CustomerInvoicesUnpaid> $accountDatas */
                        $accountDatas = CustomerInvoicesUnpaid::getRowsOfAccountDataByCustomerNo($customerNo);

                        $errorReason = self::determineErrorReason($accountDatasPaid, $accountDatas);

                        throw new TransferVAException(
                            self::ERROR_MESSAGES['inconsistent_request'],
                            (int) self::ERROR_CODES[$endpoint]['inconsistent_request'],
                            $errorReason['errorReason'],
                            $errorReason['paymentFlagStatus'],
                        );
                    }
                }
            }

            Redis::set($paymentRequestKey, $paymentRequestId);
            Redis::expireAt($paymentRequestKey, $tomorrowTimestamp);
        } elseif (Redis::exists($cacheKey)) {
            throw new TransferVAException(
                self::ERROR_MESSAGES['conflict'],
                (int) self::ERROR_CODES[$endpoint]['conflict'],
                $errorReason,
            );
        }
        // @codeCoverageIgnoreEnd

        Redis::set($cacheKey, true);
        Redis::expireAt($cacheKey, $tomorrowTimestamp);
    }

    /**
     * Determines the appropriate error reason based on account status.
     *
     * @param  Collection<int, CustomerInvoicesPaid>  $accountDatasPaid  Paid account data collection
     * @param  Collection<int, CustomerInvoicesUnpaid>  $accountDatas  Active account data collection
     * @return array{
     *      errorReason: array{
     *          english: string,
     *          indonesia: string
     *      },
     *      paymentFlagStatus: string
     * } Error reason messages
     *
     * @codeCoverageIgnore
     */
    private static function determineErrorReason($accountDatasPaid, $accountDatas): array
    {
        if ($accountDatasPaid->isNotEmpty()) {
            return [
                'errorReason' => [
                    'english' => 'Bill has been paid',
                    'indonesia' => 'Tagihan telah dibayar',
                ],
                'paymentFlagStatus' => '01',
            ];
        }

        if ($accountDatas->isNotEmpty()) {
            return [
                'errorReason' => [
                    'english' => 'Success',
                    'indonesia' => 'Sukses',
                ],
                'paymentFlagStatus' => '00',
            ];
        }

        return [
            'errorReason' => [
                'english' => 'Bill not found',
                'indonesia' => 'Tagihan tidak ditemukan',
            ],
            'paymentFlagStatus' => '01',
        ];
    }

    /**
     * Validates the timestamp to ensure it is within an acceptable range.
     *
     * @param  string  $timestamp  The timestamp to validate.
     * @param  string  $endpoint  The endpoint type.
     *
     * @throws TimestampInvalidException If the timestamp is invalid or expired.
     *
     * @codeCoverageIgnore
     */
    private static function validateTimestamp(string $timestamp, string $endpoint): void
    {
        $timestampDate = Carbon::createFromFormat(Carbon::ATOM, $timestamp);
        if (Carbon::now()->diffInSeconds($timestampDate) > config('passport.threshold.oauth-timestamp')) {
            throw new TimestampInvalidException(
                self::ERROR_MESSAGES['unauthorized_timestamp'],
                (int) self::ERROR_CODES[$endpoint]['unauthorized_timestamp']
            );
        }
    }

    /**
     * Validates the signature to ensure request integrity.
     *
     * @param  string  $signature  The provided signature to validate.
     * @param  string  $stringToSign  The string that was signed.
     * @param  string  $clientSecret  The secret key used to validate the signature.
     * @param  string  $endpoint  The endpoint type.
     *
     * @throws SignatureInvalidException If the signature is invalid.
     *
     * @codeCoverageIgnore
     */
    private static function validateSignature(string $signature, string $stringToSign, string $clientSecret, string $endpoint): void
    {
        $calculatedSignature = base64_encode(hash_hmac('sha512', $stringToSign, $clientSecret, true));

        if (! hash_equals($signature, $calculatedSignature)) {
            throw new SignatureInvalidException(
                self::ERROR_MESSAGES['unauthorized_signature'],
                (int) self::ERROR_CODES[$endpoint]['unauthorized_signature']
            );
        }
    }
}
