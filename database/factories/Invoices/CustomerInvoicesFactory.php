<?php

namespace Database\Factories\Invoices;

use App\Models\Invoices\CustomerInvoices;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<CustomerInvoices>
 */
class CustomerInvoicesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = CustomerInvoices::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $baseDate = fake()->dateTime();

        $thblrek = $baseDate->format('Ym');
        $customerNo = $baseDate->format('ymd').fake()->unique()->numerify('####');

        $statusLunas = fake()->boolean();

        $lunasFields = [
            'admin_fee' => $statusLunas ? '10000' : 0,
            'tanggal_lunas' => $statusLunas ? fake()->dateTime() : null,
            'kode_bank' => $statusLunas ? fake()->numerify() : null,
            'payment_media_code' => $statusLunas ? fake()->lexify() : null,
            'loket_lunas' => $statusLunas ? fake()->word() : null,
            'partner_service_id' => $statusLunas ? fake()->numerify() : null,
            'payment_request_id' => $statusLunas ? fake()->numerify() : null,
            'trx_id' => $statusLunas ? fake()->numerify() : null,
            'journal_num' => $statusLunas ? fake()->numerify() : null,
        ];

        return [
            'thblrek' => $thblrek,
            'customer_no' => $customerNo,
            'customer_name' => fake()->name(),
            'customer_email' => fake()->optional()->email(),
            'customer_phone' => fake()->optional()->phoneNumber(),
            'total_invoice' => fake()->numberBetween(100000, 10000000),
            'admin_fee' => $lunasFields['admin_fee'],
            'status_lunas' => $statusLunas,
            'tanggal_lunas' => $lunasFields['tanggal_lunas'],
            'kode_bank' => $lunasFields['kode_bank'],
            'payment_media_code' => $lunasFields['payment_media_code'],
            'loket_lunas' => $lunasFields['loket_lunas'],
            'partner_service_id' => $lunasFields['partner_service_id'],
            'payment_request_id' => $lunasFields['payment_request_id'],
            'trx_id' => $lunasFields['trx_id'],
            'journal_num' => $lunasFields['journal_num'],
        ];
    }
}
