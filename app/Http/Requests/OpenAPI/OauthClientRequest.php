<?php

namespace App\Http\Requests\OpenAPI;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class OauthClientRequest extends FormRequest
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
            // This is for oauth_clients

            /** @example Client Name */
            'name' => 'required|string|max:255',

            // This is for oauth_client_metadatas

            /** @example 51111 */
            'channel_id' => 'required|integer|digits_between:5,5',

            /** @example 51112 */
            'partner_id' => 'required|integer|digits_between:5,5',

            /** @example 511 */
            'source_code' => 'required|integer|digits_between:3,3',

            /** @example 51113 */
            'kdpp' => 'required|integer|digits_between:5,5',

            /** @example 127.0.0.1 */
            'ip_address' => 'required|ipv4',

            /** @example file_name_public.key */
            'public_key_file' => 'required|string',

            /** @example file_name_private.key */
            'private_key_file' => 'nullable|string',

            /**
             * Separated by spaces if multiple scopes needed
             *
             * @example "scope1 scope2"
             */
            'scope' => 'string|max:255',
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
            'name.required' => 'Invalid Mandatory Field {name}',
            'name.*' => 'Invalid Field Format name',

            'channel_id.required' => 'Invalid Mandatory Field {channel_id}',
            'channel_id.*' => 'Invalid Field Format channel_id',

            'partner_id.required' => 'Invalid Mandatory Field {partner_id}',
            'partner_id.*' => 'Invalid Field Format partner_id',

            'source_code.required' => 'Invalid Mandatory Field {source_code}',
            'source_code.*' => 'Invalid Field Format source_code',

            'kdpp.required' => 'Invalid Mandatory Field {kdpp}',
            'kdpp.*' => 'Invalid Field Format kdpp',

            'ip_address.required' => 'Invalid Mandatory Field {ip_address}',
            'ip_address.*' => 'Invalid Field Format ip_address',

            'public_key_file.required' => 'Invalid Mandatory Field {public_key_file}',
            'public_key_file.*' => 'Invalid Field Format public_key_file',

            'scope.*' => 'Invalid Field Format scope',
        ];
    }

    /**
     * Add additional validation rules for headers.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $requiredHeaders = [
                'content-type',
            ];

            foreach ($requiredHeaders as $header) {
                // @codeCoverageIgnoreStart
                if (! $this->header($header)) {
                    $validator->errors()->add($header, 'Invalid mandatory field {'.strtoupper(str_replace('_', '-', $header)).'}');

                    return;
                }
                // @codeCoverageIgnoreEnd
            }
        });
    }

    /**
     * Handle a failed validation attempt.
     *
     * @codeCoverageIgnore
     */
    public function failedValidation(Validator $validator): void
    {
        // Get the first error from the validator
        $errors = $validator->errors()->toArray();

        /** @var string|null $firstErrorKey */
        $firstErrorKey = array_key_first($errors);

        /** @var string $firstErrorMessage */
        $firstErrorMessage = (is_string($firstErrorKey) && isset($errors[$firstErrorKey]) && is_array($errors[$firstErrorKey]) && isset($errors[$firstErrorKey][0]))
            ? $errors[$firstErrorKey][0]
            : 'Invalid Field Format';

        $responseCode = match (true) {
            str_contains($firstErrorMessage, 'Invalid Mandatory Field') => '4007202',
            str_contains($firstErrorMessage, 'Unauthorized') => '4007200',
            default => '4007201',
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
