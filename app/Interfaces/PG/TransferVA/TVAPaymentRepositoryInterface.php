<?php

namespace App\Interfaces\PG\TransferVA;

interface TVAPaymentRepositoryInterface
{
    /**
     * Process payment of a customer account bills.
     *
     * @param array{
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
     * @param  array<string, mixed>  $claims
     * @return array<string, mixed>
     */
    public function payment(array $payload, array $claims): array;
}
