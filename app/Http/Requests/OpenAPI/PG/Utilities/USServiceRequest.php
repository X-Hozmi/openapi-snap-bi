<?php

namespace App\Http\Requests\OpenAPI\PG\Utilities;

use DateTime;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Request;

class USServiceRequest extends FormRequest
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
                'authorization',
                'x-timestamp',
                'x-client-secret',
                'x-http-method',
                'endpoint-url',
                'content-type',
            ];

            foreach ($requiredHeaders as $header) {
                if (! $this->header($header)) {
                    $validator->errors()->add($header, 'Invalid mandatory field ['.strtoupper(str_replace('_', '-', $header)).']');

                    return;
                }
            }

            $validMethods = [
                Request::METHOD_GET,
                Request::METHOD_POST,
                Request::METHOD_PUT,
                Request::METHOD_PATCH,
                Request::METHOD_DELETE,
            ];

            /** @var string $timestamp */
            $timestamp = $this->header('x-timestamp');

            /** @var string $httpMethod */
            $httpMethod = $this->header('x-http-method');

            if (empty($this->bearerToken())) {
                $validator->errors()->add('authorization', 'Invalid Field Format Authorization'); // @codeCoverageIgnore
            }

            if (! DateTime::createFromFormat(DATE_ATOM, $timestamp)) {
                $validator->errors()->add('x-timestamp', 'Invalid Field Format X-TIMESTAMP');
            }

            if (! in_array(strtoupper($httpMethod), $validMethods)) {
                $validator->errors()->add('x-http-method', 'Invalid Field Format HTTP-METHOD'); // @codeCoverageIgnore
            }

            if (in_array($httpMethod, [
                Request::METHOD_POST,
                Request::METHOD_PUT,
                Request::METHOD_PATCH,
            ])) {
                $content = $this->getContent();

                /** @var array<string, mixed> $decodedContent */
                $decodedContent = json_decode($content, true);

                if (empty($decodedContent)) {
                    $validator->errors()->add('payload', 'Payload cannot be empty');
                }
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
            str_contains($firstErrorMessage, 'Unauthorized') => '4017100',
            str_contains($firstErrorMessage, 'Invalid Mandatory Field') => '4007102',
            str_contains($firstErrorMessage, 'Payload cannot be empty') => '4007103',
            default => '4007101',
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
