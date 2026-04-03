<?php

namespace App\Repositories\PG\TransferVA;

use App\Exceptions\TransferVAException;
use App\Helpers\DataTypeCaster;
use App\Infrastructures\Databases\PG\TVAInquiry;
use App\Interfaces\PG\TransferVA\TVAInquiryRepositoryInterface;

class TVAInquiryRepository implements TVAInquiryRepositoryInterface
{
    public function inquiry(array $data): object
    {
        $tVaInquiry = new TVAInquiry;

        /**
         * @var object{
         *     customer_name?: string,
         *     total_invoice?: float|int,
         *     total_invoice_sum: string|float|int
         * }|null $accountData
         */
        $accountData = $tVaInquiry->getBillData($data['customerNo'], 'pending');

        if (is_null($accountData)) {
            $accountDataPaid = $tVaInquiry->getBillData($data['customerNo'], 'lunas');

            if (
                ! is_null($accountDataPaid)
                && property_exists($accountDataPaid, 'customer_name')
                && property_exists($accountDataPaid, 'total_invoice')
            ) {
                throw new TransferVAException('Paid Bill', 4042414, [
                    'english' => 'Bill has been paid',
                    'indonesia' => 'Tagihan telah dibayar',
                ]);
            }

            throw new TransferVAException('Invalid Bill/Virtual Account [Not Found]', 4042412, [
                'english' => 'Bill not found',
                'indonesia' => 'Tagihan tidak ditemukan',
            ]);
        }

        if (
            ! property_exists($accountData, 'customer_name')
            || ! property_exists($accountData, 'total_invoice')
        ) {
            throw new TransferVAException('Invalid Bill/Virtual Account [Not Found]', 4042412, [
                'english' => 'Bill not found',
                'indonesia' => 'Tagihan tidak ditemukan',
            ]);
        }

        $customerNo = $data['customerNo'];
        $partnerServiceId = str_pad($data['partnerServiceId'], 8, ' ', STR_PAD_LEFT);
        $virtualAccountNo = "{$partnerServiceId}{$customerNo}";

        $virtualAccountData = [
            'inquiryStatus' => '00',
            'inquiryReason' => [
                'english' => 'Success',
                'indonesia' => 'Sukses',
            ],
            'partnerServiceId' => $partnerServiceId,
            'customerNo' => $customerNo,
            'virtualAccountNo' => $virtualAccountNo,
            'virtualAccountName' => $accountData->customer_name,
            'virtualAccountEmail' => null,
            'virtualAccountPhone' => null,
            'inquiryRequestId' => $data['inquiryRequestId'],
            'totalAmount' => [
                'value' => number_format((float) $accountData->total_invoice_sum, 2, '.', ''),
                'currency' => 'IDR',
            ],
            'subCompany' => '00000',
            'billDetails' => [],
            'freeTexts' => [],
            'virtualAccountTrxType' => null,
            'feeAmount' => null,
        ];

        $additionalInfo = new \stdClass;

        // @codeCoverageIgnoreStart
        if (false) { // @phpstan-ignore-line
            $virtualAccountData['billDetails'] = [
                [
                    'billCode' => '',
                    'billNo' => '',
                    'billName' => '',
                    'billShortName' => '',
                    'billDescription' => [
                        'english' => '',
                        'indonesia' => '',
                    ],
                    'billSubCompany' => '',
                    'billAmount' => [
                        'value' => '',
                        'currency' => '',
                    ],
                    'billAmountLabel' => '',
                    'billAmountValue' => '',
                    'additionalInfo' => [
                        'additionalInfo1' => [
                            'label' => [
                                'english' => '',
                                'indonesia' => '',
                            ],
                            'value' => [
                                'english' => '',
                                'indonesia' => '',
                            ],
                        ],
                    ],
                ],
            ];

            $virtualAccountData['freeTexts'] = [
                [
                    'english' => 'This struck is valid',
                    'indonesia' => 'Struk ini dinyatakan sah',
                ],
                [
                    'english' => 'Bills info can be downloaded',
                    'indonesia' => 'Detail tagihan dapat di download',
                ],
                [
                    'english' => 'at www.ecopowerport.co.id',
                    'indonesia' => 'melalui www.ecopowerport.co.id',
                ],
            ];

            $virtualAccountData['virtualAccountTrxType'] = 'O';

            $virtualAccountData['feeAmount'] = [
                'value' => '',
                'currency' => '',
            ];

            $additionalInfo = [
                'additionalInfo1' => [
                    'label' => [
                        'english' => '',
                        'indonesia' => '',
                    ],
                    'value' => [
                        'english' => '',
                        'indonesia' => '',
                    ],
                ],
            ];
        }
        // @codeCoverageIgnoreEnd

        /** @var object $result */
        $result = (new DataTypeCaster)->recursiveArrayToObject([
            'virtualAccountData' => $virtualAccountData,
            'additionalInfo' => $additionalInfo,
        ]);

        return $result;
    }
}
