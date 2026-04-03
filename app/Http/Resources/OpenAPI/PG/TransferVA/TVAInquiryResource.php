<?php

namespace App\Http\Resources\OpenAPI\PG\TransferVA;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string|null $responseCode
 * @property-read string|null $responseMessage
 * @property-read object|null $virtualAccountData
 * @property-read object|null $additionalInfo
 */
class TVAInquiryResource extends JsonResource
{
    /**
     * Disable the wrapping of the outer-most resource array.
     *
     * @var string|null
     */
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /**
         * @var object{
         *     inquiryStatus: string|null,
         *     inquiryReason: array{english: string, indonesia: string}|null,
         *     partnerServiceId: string|null,
         *     customerNo: string|null,
         *     virtualAccountNo: string|null,
         *     virtualAccountName: string|null,
         *     virtualAccountEmail: string|null,
         *     virtualAccountPhone: string|null,
         *     inquiryRequestId: string|null,
         *     totalAmount: array{value: string, currency: string}|null,
         *     subCompany: string|null,
         *     billDetails: array<int, array{
         *         billCode: string,
         *         billNo: string,
         *         billName: string,
         *         billShortName: string,
         *         billDescription: array{english: string, indonesia: string},
         *         billSubCompany: string,
         *         billAmount: array{value: string, currency: string},
         *         billAmountLabel: string,
         *         billAmountValue: string,
         *         additionalInfo: object
         *     }>|null,
         *     freeTexts: array<int, array{english: string, indonesia: string}>|null,
         *     virtualAccountTrxType: string|null,
         *     feeAmount: array{value: string, currency: string}|null
         * } $virtualAccountData
         */
        $virtualAccountData = $this->virtualAccountData;

        return [
            /**
             * @example 2002400
             */
            'responseCode' => (string) $this->responseCode,

            /**
             * @example Successful
             */
            'responseMessage' => $this->responseMessage,

            'virtualAccountData' => [
                /**
                 * Status inquiry
                 *
                 * @example "00"
                 */
                'inquiryStatus' => $virtualAccountData->inquiryStatus,

                /**
                 * Inquiry reason
                 *
                 * @example {"english": "Success", "indonesia": "Sukses"}
                 */
                'inquiryReason' => $virtualAccountData->inquiryReason,

                /**
                 * Partner Service ID padded
                 *
                 * @example "   51112"
                 */
                'partnerServiceId' => $virtualAccountData->partnerServiceId,

                /**
                 * Customer No
                 *
                 * @example "512508149999"
                 */
                'customerNo' => $virtualAccountData->customerNo,

                /**
                 * Virtual Account Number
                 *
                 * @example "   51112512508149999"
                 */
                'virtualAccountNo' => $virtualAccountData->virtualAccountNo,

                /**
                 * Customer Name
                 *
                 * @example "Fulan"
                 */
                'virtualAccountName' => $virtualAccountData->virtualAccountName,

                'virtualAccountEmail' => $this->whenNotNull($virtualAccountData->virtualAccountEmail),
                'virtualAccountPhone' => $this->whenNotNull($virtualAccountData->virtualAccountPhone),

                /**
                 * Inquiry request ID
                 *
                 * @example "abcdef-123456-abcdef"
                 */
                'inquiryRequestId' => $virtualAccountData->inquiryRequestId,

                /**
                 * Total amount
                 *
                 * @example {"value": "1000000.00", "currency": "IDR"}
                 */
                'totalAmount' => $virtualAccountData->totalAmount,

                /**
                 * Sub Company
                 *
                 * @example "00000"
                 */
                'subCompany' => $virtualAccountData->subCompany,

                /**
                 * Bill details (optional)
                 *
                 * @example [{"billCode": "", "billNo": "", "billName": "", "billShortName": "", "billDescription": {"english": "", "indonesia": ""}, "billSubCompany": "", "billAmount": {"value": "", "currency": ""}, "billAmountLabel": "", "billAmountValue": "", "additionalInfo": [{"additionalInfo1": {"label": {"english": "", "indonesia": ""}, "value": {"english": "", "indonesia": ""}}}]}]
                 * @example [] (Empty Array)
                 */
                'billDetails' => $virtualAccountData->billDetails,

                /**
                 * Free texts (optional)
                 *
                 * @example [{"english": "", "indonesia": ""}]
                 * @example [] (Empty Array)
                 */
                'freeTexts' => $virtualAccountData->freeTexts,

                'virtualAccountTrxType' => $this->whenNotNull($virtualAccountData->virtualAccountTrxType),
                'feeAmount' => $this->whenNotNull($virtualAccountData->feeAmount),
            ],

            /**
             * @var object
             *
             * @example [{"additionalInfo1": {"label": {"english": "", "indonesia": ""}, "value": {"english": "", "indonesia": ""}}}]
             * @example '{}' (Empty Object)
             */
            'additionalInfo' => $this->additionalInfo,
        ];
    }
}
