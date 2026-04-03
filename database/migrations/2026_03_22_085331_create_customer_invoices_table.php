<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string> */
    private $tableNames = [
        'customer_invoices',
        'customer_invoices_queue',
        'customer_invoices_log',
        'customer_invoices_error',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tableNames as $tableName) {
            Schema::create($tableName, function (Blueprint $table) use ($tableName) {
                $table->id();
                $table->string('thblrek', 6)->comment('Tahun Bulan Rekening (Misal: 202603)');
                $table->string('customer_no')->comment('Sama dengan id_bayar / Customer Number VA');

                if ($tableName === 'customer_invoices') {
                    $table->string('customer_name')->comment('Sama dengan nama_cust / Virtual Account Name');
                    $table->string('customer_email')->nullable();
                    $table->string('customer_phone')->nullable();
                }

                $table->decimal('total_invoice', 15, 2);
                $table->decimal('admin_fee', 15, 2)->default(0)->comment('Sama dengan rupiah_admin');
                $table->boolean('status_lunas')->default(false);
                $table->timestamp('tanggal_lunas')->nullable()->comment('Sama dengan trxDateTime');
                $table->string('kode_bank')->nullable()->comment('Sama dengan sourceBankCode');
                $table->string('payment_media_code')->nullable()->comment('Sama dengan channelCode');
                $table->string('loket_lunas')->nullable()->comment('Nama client Oauth');
                $table->string('partner_service_id')->nullable();
                $table->string('payment_request_id')->nullable();
                $table->string('trx_id')->nullable();
                $table->string('journal_num')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

                if ($tableName === 'customer_invoices') {
                    $table->softDeletes();
                }

                $table->index('thblrek');
                $table->index('customer_no');
                $table->index('status_lunas');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tableNames as $tableName) {
            Schema::dropIfExists($tableName);
        }
    }
};
