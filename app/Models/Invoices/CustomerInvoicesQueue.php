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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesQueue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesQueue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesQueue query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesQueue whereAdminFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesQueue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesQueue whereCustomerNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesQueue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesQueue whereJournalNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesQueue whereKodeBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesQueue whereLoketLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesQueue wherePartnerServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesQueue wherePaymentMediaCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesQueue wherePaymentRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesQueue whereStatusLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesQueue whereTanggalLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesQueue whereThblrek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesQueue whereTotalInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesQueue whereTrxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesQueue whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CustomerInvoicesQueue extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_invoices_queue';

    protected $guarded = ['id'];
}
