<?php

namespace App\Http\Controllers\OpenAPI\PG\Invokes\TransferVA;

use App\Http\Controllers\TransferVAResponseController;
use App\Http\Requests\OpenAPI\PG\TransferVA\TVAInquiryRequest;
use App\Http\Resources\OpenAPI\PG\TransferVA\TVAInquiryResource;
use App\Interfaces\PG\TransferVA\TVAInquiryRepositoryInterface;
use App\Validators\OAuth\ClientCredentialGrant\SignatureSymmetricValidator;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\HeaderParameter;

#[Group('Transfer Virtual Account', 'This Endpoint is used as a Payment Gateway', weight: 80)]
class TVAInquiryController extends TransferVAResponseController
{
    private TVAInquiryRepositoryInterface $tVaInquiryRepositoryInterface;

    public function __construct(TVAInquiryRepositoryInterface $tVaInquiryRepositoryInterface)
    {
        $this->tVaInquiryRepositoryInterface = $tVaInquiryRepositoryInterface;
    }

    /**
     * VA Bill Presentment.
     */
    #[HeaderParameter('CHANNEL-ID', 'Channel Identifier', type: 'integer', required: true)]
    #[HeaderParameter('X-EXTERNAL-ID', 'Numeric String. Reference number that should be unique in the same day', type: 'integer', required: true)]
    #[HeaderParameter('X-IP-ADDRESS', 'Partner IP Address', type: 'string', example: '192.168.1.1', required: false)]
    #[HeaderParameter('X-ORIGIN', 'Domain', type: 'string', example: 'example.com', required: true)]
    #[HeaderParameter('X-PARTNER-ID', 'Partner ID using Company Code VA', type: 'integer', example: 51112, required: true)]
    #[HeaderParameter('X-SIGNATURE', 'base64_encode(hash_hmac(sha512, implode(:, [strtoupper($httpMethod), $endpointUrl, $accessToken, strtolower(hash(sha256, json_encode(json_decode($requestBody)))), $timestamp]), $clientSecret, true))', type: 'string', required: true)]
    #[HeaderParameter('X-TIMESTAMP', 'yyyy-MM-ddTHH:mm:ssTZD', type: 'datetime', example: '2025-03-29T21:06:32+07:00', required: true)]
    public function __invoke(TVAInquiryRequest $request): TVAInquiryResource
    {
        /** @var string $xExternalId */
        $xExternalId = $request->header('x-external-id');

        /**
         * @var array{
         *      partnerServiceId: string,
         *      customerNo: string,
         *      virtualAccountNo: string,
         *      trxDateInit: string,
         *      channelCode: int,
         *      language: ?string,
         *      amount: ?array{
         *          value: string,
         *          currency: string,
         *      },
         *      hashedSourceAccountNo: ?string,
         *      sourceBankCode: string,
         *      passApp: ?string,
         *      inquiryRequestId: string,
         *      additionalInfo: array<int, object{
         *          label: object{
         *              english: ?string,
         *              indonesia: ?string,
         *          },
         *          value: object{
         *              english: ?string,
         *              indonesia: ?string,
         *          },
         *      }>|object,
         * } $payloads
         */
        $payloads = $request->validated();

        SignatureSymmetricValidator::processSymmetricSignature(
            $request->header(),
            $xExternalId,
            $request->method(),
            '/'.$request->path(),
            $request->getContent(),
            'inquiry'
        );

        $vaInquiry = (object) array_merge([
            'responseCode' => '2002400',
            'responseMessage' => 'Successful',
        ], (array) $this->tVaInquiryRepositoryInterface->inquiry($payloads));

        return new TVAInquiryResource($vaInquiry);
    }
}
