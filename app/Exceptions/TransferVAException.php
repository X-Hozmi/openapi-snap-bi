<?php

namespace App\Exceptions;

use App\Helpers\DataTypeCaster;
use App\Http\Resources\OpenAPI\PG\TransferVA\TVAInquiryResource;
use App\Http\Resources\OpenAPI\PG\TransferVA\TVAPaymentResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use InvalidArgumentException;

class TransferVAException extends Exception
{
    public const CONTEXT_INQUIRY = 'inquiry';

    public const CONTEXT_PAYMENT = 'payment';

    /**
     * @var array{english: string, indonesia: string}
     */
    private array $exceptionReason;

    private string $paymentFlagStatus;

    private Request $request;

    private string $context = self::CONTEXT_INQUIRY;

    /**
     * @param  array{english: string, indonesia: string}  $exceptionReason
     */
    public function __construct(
        string $message,
        int $code,
        array $exceptionReason = [
            'english' => '',
            'indonesia' => '',
        ],
        string $paymentFlagStatus = '01',
        ?Request $request = null,
        string $context = self::CONTEXT_INQUIRY,
    ) {
        if ($code === 0) {
            $code = match ($context) {
                self::CONTEXT_INQUIRY => 5002400,
                self::CONTEXT_PAYMENT => 5002500,
                default => 5002400,
            };
        }

        parent::__construct($message, $code);
        $this->exceptionReason = $exceptionReason;
        $this->paymentFlagStatus = $paymentFlagStatus;
        $this->request = $request ?? request();
        $this->context = $context;
    }

    /**
     * @return array{english: string, indonesia: string}
     */
    public function getExceptionReason(): array
    {
        return $this->exceptionReason;
    }

    public function getPaymentFlagStatus(): string
    {
        return $this->paymentFlagStatus;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * Generate error DTO based on context
     */
    public function getErrorDTO(): object
    {
        return match ($this->context) {
            self::CONTEXT_INQUIRY => $this->getInquiryErrorDTO(),
            self::CONTEXT_PAYMENT => $this->getPaymentErrorDTO(),
            default => throw new InvalidArgumentException("Unsupported context: {$this->context}"),
        };
    }

    /**
     * Generate error resource based on context
     */
    public function getErrorResource(): JsonResource
    {
        return match ($this->context) {
            self::CONTEXT_INQUIRY => new TVAInquiryResource($this->getInquiryErrorDTO()),
            self::CONTEXT_PAYMENT => new TVAPaymentResource($this->getPaymentErrorDTO()),
            default => throw new InvalidArgumentException("Unsupported context: {$this->context}"),
        };
    }

    /**
     * Generate inquiry error DTO
     */
    private function getInquiryErrorDTO(): object
    {
        /** @var string $partnerServiceId */
        $partnerServiceId = $this->request->input('partnerServiceId');
        $partnerServiceId = str_pad($partnerServiceId, 8, ' ', STR_PAD_LEFT);

        /** @var string $customerNo */
        $customerNo = $this->request->input('customerNo');
        $virtualAccountNo = "{$partnerServiceId}{$customerNo}";

        $totalAmount = $this->request->input('amount') == null ? ['value' => '', 'currency' => ''] : [
            'value' => '',
            'currency' => '',
        ];

        /** @var object $result */
        $result = (new DataTypeCaster)->recursiveArrayToObject([
            'responseCode' => $this->getCode(),
            'responseMessage' => $this->getMessage(),
            'virtualAccountData' => [
                'inquiryStatus' => '01',
                'inquiryReason' => $this->exceptionReason,
                'partnerServiceId' => $partnerServiceId,
                'customerNo' => $customerNo,
                'virtualAccountNo' => $virtualAccountNo,
                'virtualAccountName' => null,
                'virtualAccountEmail' => null,
                'virtualAccountPhone' => null,
                'inquiryRequestId' => $this->request->input('inquiryRequestId'),
                'totalAmount' => $totalAmount,
                'subCompany' => '00000',
                'billDetails' => [],
                'freeTexts' => [],
                'virtualAccountTrxType' => null,
                'feeAmount' => null,
            ],
            'additionalInfo' => (object) [],
        ]);

        return $result;
    }

    /**
     * Generate payment error DTO
     */
    private function getPaymentErrorDTO(): object
    {
        /** @var string $partnerServiceId */
        $partnerServiceId = $this->request->input('partnerServiceId');
        $partnerServiceId = str_pad($partnerServiceId, 8, ' ', STR_PAD_LEFT);

        /** @var string $customerNo */
        $customerNo = $this->request->input('customerNo');
        $virtualAccountNo = "{$partnerServiceId}{$customerNo}";

        /** @var object $result */
        $result = (new DataTypeCaster)->recursiveArrayToObject([
            'responseCode' => $this->getCode(),
            'responseMessage' => $this->getMessage(),
            'virtualAccountData' => [
                'paymentFlagReason' => $this->exceptionReason,
                'partnerServiceId' => $partnerServiceId,
                'customerNo' => $customerNo,
                'virtualAccountNo' => $virtualAccountNo,
                'virtualAccountName' => $this->request->input('virtualAccountName'),
                'virtualAccountEmail' => null,
                'virtualAccountPhone' => null,
                'trxId' => null,
                'paymentRequestId' => $this->request->input('paymentRequestId'),
                'paidAmount' => $this->request->input('paidAmount'),
                'paidBills' => $this->request->input('paidBills'),
                'totalAmount' => $this->request->input('totalAmount'),
                'trxDateTime' => $this->request->input('trxDateTime'),
                'referenceNo' => $this->request->input('referenceNo'),
                'journalNum' => $this->request->input('journalNum'),
                'paymentType' => $this->request->input('paymentType'),
                'flagAdvise' => null,
                'paymentFlagStatus' => $this->paymentFlagStatus,
                'billDetails' => [],
                'freeTexts' => [],
            ],
            'additionalInfo' => (object) [],
        ]);

        return $result;
    }

    /**
     * Generate error record for exception handler (for backward compatibility)
     *
     * @return array<string, mixed>
     */
    public function getRecord(): array
    {
        /** @var array<string, mixed> $record */
        $record = $this->getErrorResource()->resolve();

        return $record;
    }

    /**
     * Set the request context for this exception
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Auto-detect context from request path
     */
    public function autoDetectContext(): self
    {
        $path = $this->request->path();

        if (str_contains($path, '/inquiry')) {
            $this->context = self::CONTEXT_INQUIRY;
        } elseif (str_contains($path, '/payment')) {
            $this->context = self::CONTEXT_PAYMENT;
        }

        return $this;
    }
}
