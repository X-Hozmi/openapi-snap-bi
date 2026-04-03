<?php

namespace App\Http\Requests\OpenAPI\PG\TransferVA;

use App\Helpers\DataTypeCaster;
use App\Http\Resources\OpenAPI\PG\TransferVA\TVAInquiryResource;
use App\Models\Oauth\OauthClientMetadata;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TVAInquiryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            /**
             * Derivative of X-PARTNER-ID, similar to company code, 8 digit left padding space “ “
             *
             * @example    51112
             * */
            'partnerServiceId' => 'required|string|max:8',

            /**
             * Unique number (up to 20 digits)
             *
             * @example 512234567890
             * */
            'customerNo' => 'required|string|regex:/^\d{1,20}$/|max:20',

            /**
             * partnerServiceId (8 digit left padding space “ “) + customerNo (up to 20 digit)
             *
             * @example    51112512234567890
             * */
            'virtualAccountNo' => 'required|string|max:28',

            /**
             * System datetime with timezone, which follows the ISO-8601 standard
             *
             * @example 2025-03-29T21:06:32+07:00
             * */
            'trxDateInit' => 'date_format:Y-m-d\TH:i:sP|size:25',

            /** Channel code based on ISO 18245 */
            'channelCode' => 'numeric|digits:4',

            /** Language code based on ISO 639-1 */
            'language' => 'nullable|in:ID,EN|size:2',

            'amount' => 'nullable|array',
            /**
             * Transaction Amount. Nominal inputted by Customer with 2 decimal
             *
             * @example 10000.00
             * */
            'amount.value' => 'required_with:amount|string|regex:/^\d+(\.\d{1,2})?$/',
            /** Currency */
            'amount.currency' => 'required_with:amount|string|in:IDR,USD|size:3',

            /** Source account number in hash */
            'hashedSourceAccountNo' => 'nullable|string|regex:/^[a-zA-Z0-9]+$/|max:32',

            /**
             * Source account bank code
             *
             * @example 008
             * */
            'sourceBankCode' => 'string|regex:/^\d{3}$/|size:3',

            /** Key for 3rd party to access API like client secret */
            'passApp' => 'nullable|string|max:64',

            /** Unique identifier for this inquiry */
            'inquiryRequestId' => 'required|string|regex:/^[a-zA-Z0-9-]+$/|max:128',

            /** Additional Information for custom use */
            'additionalInfo' => 'nullable|array',
            'additionalInfo.*.label.english' => 'nullable|string|max:50',
            'additionalInfo.*.label.indonesia' => 'nullable|string|max:50',
            'additionalInfo.*.value.english' => 'nullable|string|max:200',
            'additionalInfo.*.value.indonesia' => 'nullable|string|max:200',
        ];
    }

    /**
     * Custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // partnerServiceId Validation Messages
            'partnerServiceId.required' => 'Invalid Mandatory Field {partnerServiceId}',
            'partnerServiceId.string' => 'Invalid Field Format partnerServiceId',
            'partnerServiceId.regex' => 'Invalid Field Format partnerServiceId',
            'partnerServiceId.max' => 'Invalid Field Format partnerServiceId',

            // customerNo Validation Messages
            'customerNo.required' => 'Invalid Mandatory Field {customerNo}',
            'customerNo.string' => 'Invalid Field Format customerNo',
            'customerNo.regex' => 'Invalid Field Format customerNo',
            'customerNo.max' => 'Invalid Field Format customerNo',

            // virtualAccountNo Validation Messages
            'virtualAccountNo.required' => 'Invalid Mandatory Field {virtualAccountNo}',
            'virtualAccountNo.string' => 'Invalid Field Format virtualAccountNo',
            'virtualAccountNo.max' => 'Invalid Field Format virtualAccountNo',

            // trxDateInit Validation Messages
            'trxDateInit.date_Format' => 'Invalid Field Format trxDateInit',
            'trxDateInit.size' => 'Invalid Field Format trxDateInit',

            // channelCode Validation Messages
            'channelCode.numeric' => 'Invalid Field Format channelCode',
            'channelCode.digits' => 'Invalid Field Format channelCode',

            // language Validation Messages
            'language.in' => 'Invalid Field Format language',
            'language.size' => 'Invalid Field Format language',

            // amount Validation Messages
            'amount.array' => 'Invalid Field Format amount',
            'amount.value.required_with' => 'Invalid Mandatory Field {amount.value}',
            'amount.value.string' => 'Invalid Field Format amount.value',
            'amount.value.regex' => 'Invalid Field Format amount.value',
            'amount.currency.required_with' => 'Invalid Mandatory Field {amount.currency}',
            'amount.currency.string' => 'Invalid Field Format amount.currency',
            'amount.currency.in' => 'Invalid Field Format amount.currency',
            'amount.currency.size' => 'Invalid Field Format amount.currency',

            // hashedSourceAccountNo Validation Messages
            'hashedSourceAccountNo.string' => 'Invalid Field Format hashedSourceAccountNo',
            'hashedSourceAccountNo.regex' => 'Invalid Field Format hashedSourceAccountNo',
            'hashedSourceAccountNo.size' => 'Invalid Field Format hashedSourceAccountNo',

            // sourceBankCode Validation Messages
            'sourceBankCode.string' => 'Invalid Field Format sourceBankCode',
            'sourceBankCode.regex' => 'Invalid Field Format sourceBankCode',
            'sourceBankCode.size' => 'Invalid Field Format sourceBankCode',

            // passApp Validation Messages
            'passApp.string' => 'Invalid Field Format passApp',
            'passApp.max' => 'Invalid Field Format passApp',

            // inquiryRequestId Validation Messages
            'inquiryRequestId.required' => 'Invalid Mandatory Field {inquiryRequestId}',
            'inquiryRequestId.string' => 'Invalid Field Format inquiryRequestId',
            'inquiryRequestId.regex' => 'Invalid Field Format inquiryRequestId',
            'inquiryRequestId.max' => 'Invalid Field Format inquiryRequestId',

            // additionalInfo Validation Messages
            'additionalInfo.array' => 'Invalid Field Format additionalInfo',
            'additionalInfo.*.label.english.nullable' => 'Invalid Field Format additionalInfo.label.english',
            'additionalInfo.*.label.english.string' => 'Invalid Field Format additionalInfo.label.english',
            'additionalInfo.*.label.english.max' => 'Invalid Field Format additionalInfo.label.english',
            'additionalInfo.*.label.indonesia.nullable' => 'Invalid Field Format additionalInfo.label.indonesia',
            'additionalInfo.*.label.indonesia.string' => 'Invalid Field Format additionalInfo.label.indonesia',
            'additionalInfo.*.label.indonesia.max' => 'Invalid Field Format additionalInfo.label.indonesia',
            'additionalInfo.*.value.english.nullable' => 'Invalid Field Format additionalInfo.value.english',
            'additionalInfo.*.value.english.string' => 'Invalid Field Format additionalInfo.value.english',
            'additionalInfo.*.value.english.max' => 'Invalid Field Format additionalInfo.value.english',
            'additionalInfo.*.value.indonesia.nullable' => 'Invalid Field Format additionalInfo.value.indonesia',
            'additionalInfo.*.value.indonesia.string' => 'Invalid Field Format additionalInfo.value.indonesia',
            'additionalInfo.*.value.indonesia.max' => 'Invalid Field Format additionalInfo.value.indonesia',
        ];
    }

    /**
     * Add additional validation rules for headers.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $requiredHeaders = [
                'authorization',
                'content-type',
                'channel-id',
                'x-external-id',
                'x-partner-id',
                'x-signature',
                'x-timestamp',
            ];

            foreach ($requiredHeaders as $header) {
                if (! $this->header($header)) {
                    $validator->errors()->add($header, 'Invalid Mandatory Field {'.strtoupper(str_replace('_', '-', $header)).'}');

                    return;
                }
            }

            /** @var string $timestamp */
            $timestamp = $this->header('x-timestamp');

            /** @var string $partnerServiceId */
            $partnerServiceId = $this->input('partnerServiceId');

            /** @var string $customerNo */
            $customerNo = $this->input('customerNo');

            /** @var string $virtualAccountNo */
            $virtualAccountNo = $this->input('virtualAccountNo');

            /** @var string $channelId */
            $channelId = $this->header('channel-id');

            /** @var string $partnerId */
            $partnerId = $this->header('x-partner-id');

            if (empty($this->bearerToken())) {
                $validator->errors()->add('authorization', 'Invalid Field Format Authorization'); // @codeCoverageIgnore
            }

            if (! ctype_digit($channelId) || ! OauthClientMetadata::where('channel_id', $channelId)->exists()) {
                $validator->errors()->add('channel-id', 'Unauthorized. [Unknown client]');
            }

            if (! ctype_digit($partnerId) || ! OauthClientMetadata::where('partner_id', $partnerId)->exists()) {
                $validator->errors()->add('x-partner-id', 'Unauthorized. [Unknown client]');
            }

            if (! is_string($this->header('x-external-id')) || ! ctype_digit($this->header('x-external-id'))) {
                $validator->errors()->add('x-external-id', 'Invalid Field Format X-EXTERNAL-ID'); // @codeCoverageIgnore
            }

            if (! is_string($this->header('x-signature'))) {
                $validator->errors()->add('x-signature', 'Invalid Field Format X-SIGNATURE'); // @codeCoverageIgnore
            }

            if (! \DateTime::createFromFormat(DATE_ATOM, $timestamp)) {
                $validator->errors()->add('x-timestamp', 'Invalid Field Format X-TIMESTAMP');
            }

            if (strlen(trim($partnerServiceId)) > 8 || ! ctype_digit($partnerServiceId)) {
                $validator->errors()->add('partnerServiceId', 'Invalid Field Format partnerServiceId'); // @codeCoverageIgnore
            }

            if (! ctype_digit($customerNo)) {
                $validator->errors()->add('customerNo', 'Invalid Field Format customerNo'); // @codeCoverageIgnore
            }

            if ($virtualAccountNo !== ("{$partnerServiceId}{$customerNo}")) {
                $validator->errors()->add('virtualAccountNo', 'Invalid Field Format virtualAccountNo'); // @codeCoverageIgnore
            }
        });
    }

    /**
     * Handle a failed validation attempt.
     */
    public function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();

        /** @var string|null $firstErrorKey */
        $firstErrorKey = array_key_first($errors);

        /** @var string $firstErrorMessage */
        $firstErrorMessage = (is_string($firstErrorKey) && isset($errors[$firstErrorKey]) && is_array($errors[$firstErrorKey]) && isset($errors[$firstErrorKey][0]))
            ? $errors[$firstErrorKey][0]
            : 'Invalid Field Format'; // @codeCoverageIgnore

        $responseCode = match (true) {
            str_contains($firstErrorMessage, 'Unauthorized') => '4012400',
            str_contains($firstErrorMessage, 'Invalid Mandatory Field') => '4002402',
            default => '4002401',
        };

        /** @var float|int|string $amountValue */
        $amountValue = $this->input('amount.value');

        $totalAmount = $this->input('amount') == null ? null : [
            'value' => number_format((float) $amountValue, 2, '.', ''),
            'currency' => $this->input('amount.currency'),
        ];

        $errorReason = match ($responseCode) {
            '4002402' => [
                'english' => "Invalid Mandatory Field {{$firstErrorKey}}",
                'indonesia' => "Format Bidang Harus Diisi {{$firstErrorKey}}",
            ],
            default => [
                'english' => 'Invalid Field Format',
                'indonesia' => 'Format Bidang Tidak Valid',
            ],
        };

        $response = [
            'responseCode' => $responseCode,
            'responseMessage' => $firstErrorMessage,
        ];

        $errorResponse = (new DataTypeCaster)->recursiveArrayToObject(
            array_merge($response, [
                'virtualAccountData' => [
                    'inquiryStatus' => '01',
                    'inquiryReason' => $errorReason,
                    'partnerServiceId' => $this->input('partnerServiceId'),
                    'customerNo' => $this->input('customerNo'),
                    'virtualAccountNo' => $this->input('virtualAccountNo'),
                    'virtualAccountName' => null,
                    'virtualAccountEmail' => null,
                    'virtualAccountPhone' => null,
                    'inquiryRequestId' => $this->input('inquiryRequestId'),
                    'totalAmount' => $totalAmount,
                    'subCompany' => '',
                    'billDetails' => [],
                    'freeTexts' => [$errorReason],
                    'virtualAccountTrxType' => null,
                    'feeAmount' => null,
                ],
                'additionalInfo' => (object) [],
            ])
        );

        $errorResponse = new TVAInquiryResource($errorResponse);

        throw new HttpResponseException(
            response()->json(
                $responseCode === '4012400' ? $response : $errorResponse,
                (int) substr((string) $responseCode, 0, 3)
            )
        );
    }
}
