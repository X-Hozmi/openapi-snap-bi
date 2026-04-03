<?php

namespace App\Interfaces\PG\TransferVA;

interface TVAInquiryRepositoryInterface
{
    /**
     * Get an information of a customer account bills.
     *
     * @param array{
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
     * } $data
     */
    public function inquiry(array $data): object;
}
