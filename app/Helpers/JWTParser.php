<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Lcobucci\JWT\Encoding\CannotDecodeContent;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\UnsupportedHeaderFound;
use Lcobucci\JWT\UnencryptedToken;

class JWTParser
{
    /**
     * Parse and extract claims from a JWT token
     *
     * @param  non-empty-string  $tokenString  The JWT token string to parse
     * @return array<string, mixed>|null Returns token claims or null on error
     */
    public static function parseAndExtract(string $tokenString): ?array
    {
        $parser = new Parser(new JoseEncoder);

        try {
            /** @var UnencryptedToken $token */
            $token = $parser->parse($tokenString);

            return $token->claims()->all();
        } catch (CannotDecodeContent|InvalidTokenStructure|UnsupportedHeaderFound $e) {
            Log::error("JWT parsing error: {$e->getMessage()}");

            return null;
        }
    }
}
