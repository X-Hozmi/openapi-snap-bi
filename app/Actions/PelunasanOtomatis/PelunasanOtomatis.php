<?php

namespace App\Actions\PelunasanOtomatis;

use App\Models\Invoices\CustomerInvoices;
use App\Models\Invoices\CustomerInvoicesLog;
use App\Models\Invoices\CustomerInvoicesQueue;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PelunasanOtomatis
{
    public function __construct(
        private ?Command $console = null,
    ) {
        //
    }

    /**
     * Execute the automatic payment settlement task.
     *
     * This method processes queued payment records:
     * 1. Retrieves all records from CustomerInvoicesQueue
     * 2. For each record, attempts to:
     *    - Prepare Invoices data
     *    - Insert the data into CustomerInvoices table
     *    - Log successful processing
     *    - Remove the processed record from the queue
     * 3. If any record fails processing, it:
     *    - Rolls back the transaction
     *    - Logs the error
     *    - Inserts the failed record into CustomerInvoicesQueueError
     */
    public function __invoke(): void
    {
        $customerInvoicesQueue = new CustomerInvoicesQueue;

        /** @var Collection<int, CustomerInvoicesQueue> $queueRecords */
        $queueRecords = $customerInvoicesQueue->latest('id')->get();

        if ($queueRecords->isEmpty()) {
            $this->logInfo('No records found in CustomerInvoicesQueue to process.');

            return; // @codeCoverageIgnore
        }

        $customerInvoices = new CustomerInvoices;
        $customerInvoicesLog = new CustomerInvoicesLog;

        foreach ($queueRecords as $record) {
            $wsDppData = $record->toArray();

            /** @var array<string, mixed> $wsDppData */
            $wsDppData = Arr::except($wsDppData, ['id', 'created_at', 'updated_at']);

            $recordId = $record->id;

            try {
                DB::transaction(function () use ($customerInvoices, $customerInvoicesLog, $wsDppData, $recordId) {
                    $transformedData = $customerInvoices->transformDataForDatabase($wsDppData);
                    unset($transformedData['id']);

                    $transformedData['updated_at'] = now();

                    /** @var string $customerNoCol */
                    $customerNoCol = $customerInvoices->getActualColumnName('customer_no');

                    /** @var string $thblrekCol */
                    $thblrekCol = $customerInvoices->getActualColumnName('thblrek');

                    DB::table($customerInvoices->getTable())
                        ->where($customerNoCol, $wsDppData['customer_no'])
                        ->where($thblrekCol, $wsDppData['thblrek'])
                        ->update($transformedData);

                    $this->logInfo('Successfully processed CustomerInvoicesQueue record.', ['record_id' => $recordId]);

                    $transformedDataLog = $customerInvoicesLog->transformDataForDatabase($wsDppData);
                    unset($transformedDataLog['id']);

                    DB::table($customerInvoicesLog->getTable())
                        ->insert($transformedDataLog + [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                    $this->logInfo('Logged successful processing in CustomerInvoicesLog.', ['record_id' => $recordId]);
                });

                $successfulRecordIds[] = $recordId;
                // @codeCoverageIgnoreStart
            } catch (Exception $e) {
                $this->logError($e->getMessage(), ['record_id' => $recordId]);

                continue;
            }
            // @codeCoverageIgnoreEnd
        }

        if (! empty($successfulRecordIds)) {
            $customerInvoicesQueue->whereIn('id', $successfulRecordIds)->delete();

            $this->logInfo('Deleted processed records from CustomerInvoicesQueue.', [
                'count' => count($successfulRecordIds),
                'ids' => $successfulRecordIds,
            ]);
        }
    }

    /**
     * @param  array<mixed>  $context
     */
    private function logInfo(string $message, array $context = []): void
    {
        $this->console?->info($message);
        Log::info($message, $context);
    }

    /**
     * @param  array<mixed>  $context
     */
    private function logError(string $message, array $context = []): void
    {
        $this->console?->error($message);
        Log::error($message, $context);
    }
}
