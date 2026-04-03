<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW
                customer_invoices_paid AS
            SELECT
                *
            FROM
                customer_invoices
            WHERE
                status_lunas = '1'
            ORDER BY
                thblrek
        ");

        DB::statement("
            CREATE OR REPLACE VIEW
                customer_invoices_unpaid AS
            SELECT
                *
            FROM
                customer_invoices
            WHERE
                status_lunas = '0'
            ORDER BY
                thblrek
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS customer_invoices_paid');
        DB::statement('DROP VIEW IF EXISTS customer_invoices_unpaid');
    }
};
