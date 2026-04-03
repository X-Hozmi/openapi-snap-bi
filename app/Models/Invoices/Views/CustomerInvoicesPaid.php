<?php

namespace App\Models\Invoices\Views;

use App\Models\BaseModel;
use Illuminate\Support\Collection;

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
 * @property string|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid whereAdminFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid whereCustomerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid whereCustomerNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid whereCustomerPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid whereJournalNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid whereKodeBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid whereLoketLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid wherePartnerServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid wherePaymentMediaCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid wherePaymentRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid whereStatusLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid whereTanggalLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid whereThblrek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid whereTotalInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid whereTrxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesPaid whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CustomerInvoicesPaid extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_invoices_paid';

    protected $guarded = ['id'];

    /**
     * Retrieve rows of paid account data for a specific customer.
     *
     * @param  string  $customerNo  The Payment ID of the customer.
     * @return Collection<int, self> A collection of objects containing the account data, or null if not found.
     */
    public static function getRowsOfAccountDataByCustomerNo(string $customerNo): Collection
    {
        return self::where('customer_no', $customerNo) // @phpstan-ignore return.type
            ->get()
            ->map(function ($item) {
                return (object) $item->attributesToArray();
            });
    }
}
