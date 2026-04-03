<?php

namespace App\Http\Controllers\OpenAPI\PG\Invokes\TransferVA;

use App\Helpers\DataTypeCaster;
use App\Helpers\JWTParser;
use App\Http\Controllers\TransferVAResponseController;
use App\Http\Requests\OpenAPI\PG\TransferVA\TVAPaymentRequest;
use App\Http\Resources\OpenAPI\PG\TransferVA\TVAPaymentResource;
use App\Interfaces\PG\TransferVA\TVAPaymentRepositoryInterface;
use App\Validators\OAuth\ClientCredentialGrant\SignatureSymmetricValidator;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\HeaderParameter;
use RuntimeException;

#[Group('Transfer Virtual Account', 'This Endpoint is used as a Payment Gateway', weight: 80)]
class TVAPaymentController extends TransferVAResponseController
{
    private TVAPaymentRepositoryInterface $tVaPaymentRepositoryInterface;

    public function __construct(TVAPaymentRepositoryInterface $tVaPaymentRepositoryInterface)
    {
        $this->tVaPaymentRepositoryInterface = $tVaPaymentRepositoryInterface;
    }

    /**
     * VA Bill Payment Flag.
     */
    #[HeaderParameter('CHANNEL-ID', 'Channel Identifier', type: 'integer', required: true)]
    #[HeaderParameter('X-EXTERNAL-ID', 'Numeric String. Reference number that should be unique in the same day', type: 'integer', required: true)]
    #[HeaderParameter('X-IP-ADDRESS', 'Partner IP Address', type: 'string', example: '192.168.1.1', required: false)]
    #[HeaderParameter('X-ORIGIN', 'Domain', type: 'string', example: 'example.com', required: true)]
    #[HeaderParameter('X-PARTNER-ID', 'Partner ID using Company Code VA', type: 'integer', example: 51112, required: true)]
    #[HeaderParameter('X-SIGNATURE', 'base64_encode(hash_hmac(sha512, implode(:, [strtoupper($httpMethod), $endpointUrl, $accessToken, strtolower(hash(sha256, json_encode(json_decode($requestBody)))), $timestamp]), $clientSecret, true))', type: 'string', required: true)]
    #[HeaderParameter('X-TIMESTAMP', 'yyyy-MM-ddTHH:mm:ssTZD', type: 'datetime', example: '2025-03-29T21:06:32+07:00', required: true)]
    public function __invoke(TVAPaymentRequest $request): TVAPaymentResource
    {
        /**
         * @var array{
         *      partnerServiceId: string,
         *      customerNo: string,
         *      virtualAccountNo: string,
         *      virtualAccountName: ?string,
         *      virtualAccountEmail: ?string,
         *      virtualAccountPhone: ?string,
         *      trxId: ?string,
         *      paymentRequestId: string,
         *      channelCode: ?int,
         *      hashedSourceAccountNo: ?string,
         *      sourceBankCode: ?string,
         *      paidAmount: array{
         *          value: numeric-string,
         *          currency: string,
         *      },
         *      cumulativePaymentAmount: ?array{
         *          value: numeric-string,
         *          currency: string,
         *      },
         *      paidBills: ?string,
         *      totalAmount: array{
         *          value: numeric-string,
         *          currency: string,
         *      },
         *      trxDateTime: string,
         *      referenceNo: ?string,
         *      journalNum: ?string,
         *      paymentType: ?string,
         *      flagAdvise: ?string,
         *      subCompany: ?string,
         *      billDetails: ?array<int, object{
         *          billCode: ?string,
         *          billNo: ?string,
         *          billName: ?string,
         *          billShortName: ?string,
         *          billDescription: ?array{
         *              english: ?string,
         *              indonesia: ?string,
         *          },
         *          billSubCompany: ?string,
         *          billAmount: ?array{
         *              value: numeric-string,
         *              currency: string,
         *          },
         *          additionalInfo: ?array<string, mixed>,
         *          billReferenceNo: ?int,
         *      }>,
         *      freeTexts: ?array{
         *          english: ?string,
         *          indonesia: ?string,
         *      },
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
         * } $payload
         */
        $payload = $request->validated();

        /** @var non-empty-string $bearerToken */
        $bearerToken = $request->bearerToken();

        $claims = JWTParser::parseAndExtract($bearerToken)
            ?? throw new RuntimeException('Cannot parse JWT token claims.');

        /** @var string $xExternalId */
        $xExternalId = $request->header('x-external-id');

        /** @var string $paymentRequestId */
        $paymentRequestId = $request->input('paymentRequestId');

        SignatureSymmetricValidator::processSymmetricSignature(
            $request->header(),
            $xExternalId,
            $request->method(),
            '/'.$request->path(),
            $request->getContent(),
            'payment',
            $paymentRequestId,
        );

        $paymentData = $this->tVaPaymentRepositoryInterface->payment($payload, $claims);

        SignatureSymmetricValidator::saveResponses($xExternalId, $paymentData);

        $vaPayment = (object) array_merge([
            'responseCode' => '2002500',
            'responseMessage' => 'Successful',
        ], (array) (new DataTypeCaster)->recursiveArrayToObject($paymentData));

        return new TVAPaymentResource($vaPayment);
    }
}
