<?php

namespace App\Http\Controllers\OpenAPI\PG\Invokes\Utilities;

use App\Http\Controllers\OAuthResponseController;
use App\Http\Requests\OpenAPI\PG\Utilities\USServiceRequest;
use App\Services\OAuth\Utilities\SignatureSymmetricService;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\HeaderParameter;
use Illuminate\Http\JsonResponse;

#[Group('OpenAPI Utility Services', 'This Endpoint is used as an OpenAPI utilities', weight: 50)]
class USServiceController extends OAuthResponseController
{
    /**
     * Signature Services.
     *
     * @response array{
     *      "responseCode": "2007100",
     *      "responseMessage": "Successful",
     *      "signature": "DdWGYDtro3v4JFBZaU01wN0jmgxatmKf...",
     * }
     */
    #[HeaderParameter('X-TIMESTAMP', 'yyyy-MM-ddTHH:mm:ssTZD', type: 'datetime', example: '2025-03-29T21:06:32+07:00', required: true)]
    #[HeaderParameter('X-CLIENT-SECRET', 'Partner\'s Client Secret', type: 'string', required: true)]
    #[HeaderParameter('HTTP-METHOD', 'Depend on service needed to create the signature', type: 'string', example: 'POST', required: true)]
    #[HeaderParameter('ENDPOINT-URL', 'The service endpoint needed to create the signature', type: 'string', example: '/openapi/v1.0/transfer-va/inquiry', required: true)]
    public function __invoke(USServiceRequest $request): JsonResponse
    {
        $arraySignature = SignatureSymmetricService::processSignature(
            $request->header(),
            $request->getContent(),
        );

        return $this->sendResponse($arraySignature, '2007100', 'Successful');
    }
}
