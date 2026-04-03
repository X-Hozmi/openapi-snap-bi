<?php

namespace App\Models\Invoices;

use App\Models\BaseModel;
use App\Traits\ConditionalSoftDeletes;
use Database\Factories\Invoices\CustomerInvoicesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $thblrek Tahun Bulan Rekening (Misal: 202603)
 * @property string $customer_no Sama dengan id_bayar / Customer Number VA
 * @property string $customer_name Sama dengan nama_cust / Virtual Account Name
 * @property string|null $customer_email
 * @property string|null $customer_phone
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
 * @property Carbon|null $deleted_at
 *
 * @method static \Database\Factories\Invoices\CustomerInvoicesFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices whereAdminFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices whereCustomerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices whereCustomerNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices whereCustomerPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices whereJournalNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices whereKodeBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices whereLoketLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices wherePartnerServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices wherePaymentMediaCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices wherePaymentRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices whereStatusLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices whereTanggalLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices whereThblrek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices whereTotalInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices whereTrxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoices withoutTrashed()
 *
 * @mixin \Eloquent
 */
class CustomerInvoices extends BaseModel
{
    /** @use HasFactory<CustomerInvoicesFactory> */
    use ConditionalSoftDeletes, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_invoices';

    protected $guarded = ['id'];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): CustomerInvoicesFactory
    {
        return CustomerInvoicesFactory::new();
    }
}
