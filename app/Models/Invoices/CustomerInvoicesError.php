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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesError newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesError newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesError query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesError whereAdminFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesError whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesError whereCustomerNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesError whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesError whereJournalNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesError whereKodeBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesError whereLoketLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesError wherePartnerServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesError wherePaymentMediaCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesError wherePaymentRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesError whereStatusLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesError whereTanggalLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesError whereThblrek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesError whereTotalInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesError whereTrxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesError whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CustomerInvoicesError extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_invoices_error';

    protected $guarded = ['id'];
}
