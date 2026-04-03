<?php

namespace App\Http\Resources\OpenAPI\PG\TransferVA;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string|null $responseCode
 * @property-read string|null $responseMessage
 * @property-read object|null $virtualAccountData
 * @property-read object|null $additionalInfo
 */
class TVAPaymentResource extends JsonResource
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
    public function toArray($request): array
    {
        /**
         * @var object{
         *     paymentFlagReason: array{english: string, indonesia: string}|null,
         *     partnerServiceId: string|null,
         *     customerNo: string|null,
         *     virtualAccountNo: string|null,
         *     virtualAccountName: string|null,
         *     virtualAccountEmail: string|null,
         *     virtualAccountPhone: string|null,
         *     trxId: string|null,
         *     paymentRequestId: string|null,
         *     paidAmount: array{value: string, currency: string}|null,
         *     paidBills: string|null,
         *     totalAmount: array{value: string, currency: string}|null,
         *     trxDateTime: string|null,
         *     referenceNo: string|null,
         *     journalNum: string|null,
         *     paymentType: string|null,
         *     flagAdvise: string|null,
         *     paymentFlagStatus: string|null,
         *     billDetails: array<int, array{
         *         billerReferenceId: string,
         *         billCode: string,
         *         billNo: string,
         *         billName: string,
         *         billShortName: string,
         *         billDescription: array{english: string, indonesia: string},
         *         billSubCompany: string,
         *         billAmount: array{value: string, currency: string},
         *         additionalInfo: object,
         *         status: string,
         *         reason: array{english: string, indonesia: string},
         *     }>|null,
         *     freeTexts: array<int, array{english: string, indonesia: string}>|null,
         * } $virtualAccountData
         */
        $virtualAccountData = $this->virtualAccountData;

        return [
            /**
             * @example 2002500
             */
            'responseCode' => (string) $this->responseCode,

            /**
             * @example Successful
             */
            'responseMessage' => $this->responseMessage,

            'virtualAccountData' => [
                /**
                 * Inquiry reason
                 *
                 * @example {"english": "Success", "indonesia": "Sukses"}
                 */
                'paymentFlagReason' => $virtualAccountData->paymentFlagReason,

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
                'trxId' => $this->whenNotNull($virtualAccountData->trxId),

                /**
                 * @example 202202111031031234500001136962
                 */
                'paymentRequestId' => $virtualAccountData->paymentRequestId,

                /**
                 * @example {"value": "1000000.00", "currency": "IDR"}
                 */
                'paidAmount' => $virtualAccountData->paidAmount,

                'paidBills' => $this->whenNotNull($virtualAccountData->paidBills),

                /**
                 * @example {"value": "1000000.00", "currency": "IDR"}
                 */
                'totalAmount' => $virtualAccountData->totalAmount,

                /**
                 * @example 2025-08-20T06:37:15+07:00
                 */
                'trxDateTime' => $virtualAccountData->trxDateTime,

                /**
                 * @example 00113696201
                 */
                'referenceNo' => $virtualAccountData->referenceNo,

                'journalNum' => $this->whenNotNull($virtualAccountData->journalNum),
                'paymentType' => $this->whenNotNull($virtualAccountData->paymentType),

                /**
                 * @example N
                 */
                'flagAdvise' => $this->whenNotNull($virtualAccountData->flagAdvise),

                /**
                 * @example "00"
                 */
                'paymentFlagStatus' => $virtualAccountData->paymentFlagStatus,

                /**
                 * @example [{"billerReferenceId": "", "billCode": "", "billNo": "", "billName": "", "billShortName": "", "billDescription": {"english": "", "indonesia": ""}, "billSubCompany": "", "billAmount": {"value": "", "currency": ""}, "additionalInfo": [{"additionalInfo1": {"label": {"english": "", "indonesia": ""}, "value": {"english": "", "indonesia": ""}}}], "status": "", "reason": {"english": "", "indonesia": ""}}]
                 * @example [] (Empty Array)
                 */
                'billDetails' => $virtualAccountData->billDetails,

                /**
                 * @example [{"english": "", "indonesia": ""}]
                 * @example [] (Empty Array)
                 */
                'freeTexts' => $virtualAccountData->freeTexts,
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
