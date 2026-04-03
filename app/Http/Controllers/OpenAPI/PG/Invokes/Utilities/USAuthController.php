<?php

namespace App\Http\Controllers\OpenAPI\PG\Invokes\Utilities;

use App\Http\Controllers\OAuthResponseController;
use App\Http\Requests\OpenAPI\PG\Utilities\USAuthRequest;
use App\Services\OAuth\Utilities\SignatureAsymmetricService;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\HeaderParameter;
use Illuminate\Http\JsonResponse;

#[Group('OpenAPI Utility Services', 'This Endpoint is used as a OpenAPI utilities', weight: 50)]
class USAuthController extends OAuthResponseController
{
    /**
     * Signature Authentication.
     *
     * @unauthenticated
     *
     * @response array{
     *      "responseCode": "2007000",
     *      "responseMessage": "Successful",
     *      "signature": "ac36C9+GfLfXxlGINJ9dde+eQ...",
     *      "timestamp": "2025-03-29T23:07:34+07:00"
     * }
     */
    #[HeaderParameter('X-TIMESTAMP', 'yyyy-MM-ddTHH:mm:ssTZD', type: 'datetime', example: '2025-03-29T21:06:32+07:00', required: true)]
    #[HeaderParameter('X-CLIENT-KEY', 'Partner\'s Client ID', type: 'string', required: true)]
    #[HeaderParameter('PRIVATE-KEY', 'Partner\'s Private Key, encoded with base64_encode', type: 'string', required: true)]
    public function __invoke(USAuthRequest $request): JsonResponse
    {
        $arraySignature = SignatureAsymmetricService::processSignature(
            $request->header(),
        );

        return $this->sendResponse($arraySignature, '2007000', 'Successful');
    }
}
