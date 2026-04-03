<?php

namespace App\Repositories\PG\TransferVA;

use App\Exceptions\TransferVAException;
use App\Interfaces\PG\TransferVA\TVAPaymentRepositoryInterface;
use App\Jobs\PelunasanOtomatisJob;
use App\Models\Invoices\CustomerInvoicesQueue;
use App\Models\Invoices\Views\CustomerInvoicesPaid;
use App\Models\Invoices\Views\CustomerInvoicesUnpaid;
use App\Models\Oauth\OauthClientMetadata;
use Carbon\Carbon;

class TVAPaymentRepository implements TVAPaymentRepositoryInterface
{
    public function payment(array $data, array $claims): array
    {
        $accountDatas = CustomerInvoicesUnpaid::getRowsOfAccountDataByCustomerNo($data['customerNo']);

        if ($accountDatas->isEmpty()) {
            $accountDatasPaid = CustomerInvoicesPaid::getRowsOfAccountDataByCustomerNo($data['customerNo']);

            if (! $accountDatasPaid->isEmpty()) {
                throw new TransferVAException('Paid Bill', 4042514, [
                    'english' => 'Bill has been paid',
                    'indonesia' => 'Tagihan telah dibayar',
                ]);
            }

            throw new TransferVAException('Invalid Bill/Virtual Account [Not Found]', 4042512, [
                'english' => 'Bill not found',
                'indonesia' => 'Tagihan tidak ditemukan',
            ]);
        }

        /** @var float $totalInvoice */
        $totalInvoice = $accountDatas->sum('total_invoice');
        $totalInvoice = number_format($totalInvoice, 2, '.', '');

        if (
            bccomp($data['paidAmount']['value'], $totalInvoice, 2) !== 0 ||
            bccomp($data['totalAmount']['value'], $totalInvoice, 2) !== 0
        ) {
            throw new TransferVAException('Invalid Amount', 4042513, [
                'english' => 'Invalid Amount',
                'indonesia' => 'Jumlah Tidak Valid',
            ]);
        }

        /** @var string $clientId */
        $clientId = $claims['sub'];

        /** @var OauthClientMetadata $clientMetadata */
        $clientMetadata = OauthClientMetadata::with('client')->where('client_id', $clientId)->first();
        $clientMetadataName = strtoupper($clientMetadata->client->name);

        $customerNo = $data['customerNo'];
        $partnerServiceId = str_pad($data['partnerServiceId'], 8, ' ', STR_PAD_LEFT);
        $virtualAccountNo = "{$partnerServiceId}{$customerNo}";

        /** @var list<array<string, mixed>> $customerInvoicesQueueData */
        $customerInvoicesQueueData = [];

        foreach ($accountDatas as $accData) {
            $customerInvoicesQueueData[] = [
                'thblrek' => $accData->thblrek,
                'customer_no' => $data['customerNo'],
                'total_invoice' => $accData->total_invoice,
                'admin_fee' => $clientMetadata->rupiah_admin,
                'status_lunas' => true,
                'tanggal_lunas' => Carbon::now()->format('Y-m-d H:i:s'),
                'kode_bank' => $data['sourceBankCode'],
                'payment_media_code' => $data['channelCode'],
                'loket_lunas' => $clientMetadataName,
                'partner_service_id' => $partnerServiceId,
                'payment_request_id' => $data['paymentRequestId'],
                'trx_id' => $data['trxId'],
                'journal_num' => $data['journalNum'],
            ];
        }

        $customerInvoicesQueue = new CustomerInvoicesQueue;
        $customerInvoicesQueue->insertMapped($customerInvoicesQueueData);

        PelunasanOtomatisJob::dispatch()->onQueue('pelunasan');

        $virtualAccountData = [
            'paymentFlagReason' => [
                'english' => 'Success',
                'indonesia' => 'Sukses',
            ],
            'partnerServiceId' => $partnerServiceId,
            'customerNo' => $customerNo,
            'virtualAccountNo' => $virtualAccountNo,
            'virtualAccountName' => $data['virtualAccountName'],
            'virtualAccountEmail' => null,
            'virtualAccountPhone' => null,
            'trxId' => $data['trxId'],
            'paymentRequestId' => $data['paymentRequestId'],
            'paidAmount' => [
                'value' => $data['paidAmount']['value'],
                'currency' => $data['paidAmount']['currency'],
            ],
            'paidBills' => $data['paidBills'],
            'totalAmount' => [
                'value' => $data['totalAmount']['value'],
                'currency' => $data['totalAmount']['currency'],
            ],
            'trxDateTime' => $data['trxDateTime'],
            'referenceNo' => $data['referenceNo'],
            'journalNum' => $data['journalNum'],
            'paymentType' => $data['paymentType'],
            'flagAdvise' => null,
            'paymentFlagStatus' => '00',
            'billDetails' => [],
            'freeTexts' => [],
        ];

        $additionalInfo = new \stdClass;

        // @codeCoverageIgnoreStart
        if (false) { // @phpstan-ignore-line
            $virtualAccountData['billDetails'] = [
                [
                    'billerReferenceId' => '',
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
                    'status' => '',
                    'reason' => [
                        'english' => '',
                        'indonesia' => '',
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

        return [
            'virtualAccountData' => $virtualAccountData,
            'additionalInfo' => $additionalInfo,
        ];
    }
}
