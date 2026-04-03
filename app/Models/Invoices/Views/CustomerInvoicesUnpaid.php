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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid whereAdminFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid whereCustomerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid whereCustomerNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid whereCustomerPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid whereJournalNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid whereKodeBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid whereLoketLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid wherePartnerServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid wherePaymentMediaCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid wherePaymentRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid whereStatusLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid whereTanggalLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid whereThblrek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid whereTotalInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid whereTrxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerInvoicesUnpaid whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CustomerInvoicesUnpaid extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_invoices_unpaid';

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
