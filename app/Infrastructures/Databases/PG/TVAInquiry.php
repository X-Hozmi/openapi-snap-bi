<?php

namespace App\Infrastructures\Databases\PG;

use App\Models\Invoices\Views\CustomerInvoicesPaid;
use App\Models\Invoices\Views\CustomerInvoicesUnpaid;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class TVAInquiry
{
    private const COLUMN_NAMES = [
        'customer_no',
        'customer_name',
        'total_invoice',
    ];

    /**
     * Helper untuk mengambil data tagihan menggunakan DB Facade.
     */
    public function getBillData(string $customerNo, string $type): ?object
    {
        $modelInvoiceSudahLunas = new CustomerInvoicesPaid;
        $modelInvoiceBelumLunas = new CustomerInvoicesUnpaid;

        if ($type === 'lunas') {
            $tableName = $modelInvoiceSudahLunas->getTable();
        } else {
            $tableName = $modelInvoiceBelumLunas->getTable();
        }

        $query = DB::table($tableName)
            ->where(function (Builder $query) use ($customerNo) {
                $query->where('customer_no', $customerNo);
            });

        // 5. Hitung Sum Total Invoice (Logika: $self->sum(...))
        // Kita hitung ini dulu atau gunakan window function, tapi untuk kompatibilitas DB:
        // Clone query untuk menghitung sum agar tidak merusak query select fetch
        $sum = $query->clone()->sum('total_invoice');

        // 6. Select dengan Alias (Logika Column Mapping)
        // Kita lakukan select * manual dengan alias agar return object memiliki property
        // sesuai dengan yang diharapkan (customer_no, customer_name, total_invoice).
        $selects = [];
        foreach (self::COLUMN_NAMES as $alias) {
            $selects[] = "$alias";
        }

        // Tambahkan select * agar kolom lain yang tidak dimapping tetap terbawa (sesuai sifat Eloquent)
        // Namun, jika ingin strict seperti Model Eloquent yang hanya cast yang ada,
        // kita cukup ambil yang diperlukan. Tapi aman-nya kita ambil semua kolom original juga.
        $query->selectRaw(implode(', ', $selects));

        // 7. Ambil baris pertama
        $row = $query->first();

        if ($row) {
            /**
             * Inject total_invoice_sum ke object hasil
             *
             * @var \stdClass $row
             */
            $row->total_invoice_sum = $sum;
        }

        return $row;
    }
}
