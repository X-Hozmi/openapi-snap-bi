<?php

namespace App\Models\Invoices;

use App\Models\BaseModel;

/**
 * @property int $id
 * @property string $thblrek Tahun Bulan Rekening (Misal: 202603)
 * @property string $customer_no Sama dengan id_bayar / Customer Number VA
 * @property numeric $total_invoice
 * @property numeric $admin_fee Sama dengan rupiah_admin
 * @property int $status_lunas
 * @property string|null $tanggal_lunas Sama dengan trxDateTime
 * @property string|null $kode_bank Sama dengan sourceBankCode
 * @property string|null $payment_media_code Sama dengan channelCode
 * @property string|null $loket_lunas Nama client Oauth
 * @property string|null $partner_service_id
 * @property string|null $payment_request_id
 * @property string|null $trx_id
 * @property string|null $journal_num
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesLog whereAdminFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesLog whereCustomerNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesLog whereJournalNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesLog whereKodeBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesLog whereLoketLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesLog wherePartnerServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesLog wherePaymentMediaCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesLog wherePaymentRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesLog whereStatusLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesLog whereTanggalLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesLog whereThblrek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesLog whereTotalInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesLog whereTrxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesLog whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CustomerInvoicesLog extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_invoices_log';

    protected $guarded = ['id'];
}
