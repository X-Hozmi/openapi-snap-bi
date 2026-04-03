<?php

namespace App\Services;

class SanitizerService
{
    /**
     * Recursively sanitize sensitive header contents from an array.
     *
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    public static function sanitizeHeaderContent(array $content): array
    {
        /** @var string $headersToSanitize */
        $headersToSanitize = config('sanitizer.headers');

        $sensitiveHeaders = array_filter(explode(',', $headersToSanitize));

        /** @var array<string, mixed> $result */
        $result = collect($content)->map(function ($values, $key) use ($sensitiveHeaders) {
            if (is_array($values) && count($values) > 0) {
                $value = $values[0];

                if (in_array(strtolower($key), $sensitiveHeaders, true)) {
                    return '[REDACTED]';
                }

                return $value;
            }

            return $values; // @codeCoverageIgnore
        })->toArray();

        return $result;
    }

    /**
     * Recursively sanitize sensitive payload contents from an array.
     *
     * @param  array<mixed, mixed>  $content
     * @return array<mixed, mixed>
     */
    public static function sanitizePayloadContent(array $content, bool $isHookEndpoint = false): array
    {
        /** @var string $payloadsToSanitize */
        $payloadsToSanitize = config('sanitizer.payloads');

        return collect($content)->map(function ($value, $key) use ($isHookEndpoint, $payloadsToSanitize) {
            if ($isHookEndpoint && $key === 'data') {
                return '[REDACTED]';
            }

            if (is_array($value)) {
                return self::sanitizePayloadContent($value);
            }

            if (in_array($key, array_filter(explode(',', $payloadsToSanitize)), true)) {
                return '[REDACTED]';
            }

            return $value;
        })->toArray();
    }
}
