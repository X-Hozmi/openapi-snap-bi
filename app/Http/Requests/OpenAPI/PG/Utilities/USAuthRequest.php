<?php

namespace App\Http\Requests\OpenAPI\PG\Utilities;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class USAuthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Add additional validation rules for headers.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $requiredHeaders = [
                'x-timestamp',
                'x-client-key',
                'private-key',
                'content-type',
            ];

            foreach ($requiredHeaders as $header) {
                if (! $this->header($header)) {
                    $validator->errors()->add($header, 'Invalid mandatory field ['.strtoupper(str_replace('_', '-', $header)).']');

                    return;
                }
            }

            /** @var string $clientKey */
            $clientKey = $this->header('x-client-key');

            /** @var string $timestamp */
            $timestamp = $this->header('x-timestamp');

            if (! preg_match('/^[a-f0-9\-]{36}$/', $clientKey)) {
                $validator->errors()->add('x-client-key', 'Invalid Field Format X-CLIENT-KEY');
            }

            if (! \DateTime::createFromFormat(DATE_ATOM, $timestamp)) {
                $validator->errors()->add('x-timestamp', 'Invalid Field Format X-TIMESTAMP');
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
            str_contains($firstErrorMessage, 'Unauthorized') => '4017000',
            str_contains($firstErrorMessage, 'Invalid Mandatory Field') => '4007002',
            default => '4007001',
        };

        $response = [
            'responseCode' => $responseCode,
            'responseMessage' => $firstErrorMessage,
        ];

        throw new HttpResponseException(
            response()->json(
                $response,
                (int) substr((string) $responseCode, 0, 3)
            )
        );
    }
}
