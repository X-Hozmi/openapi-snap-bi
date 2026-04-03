<?php

namespace Database\Seeders;

use App\Models\Invoices\CustomerInvoices;
use App\Models\User;
use Database\Seeders\Logger\LoggerSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // $this->call([
        //     OauthSeeder::class,
        //     LoggerSeeder::class,
        // ]);

        CustomerInvoices::factory(10)->create();

        CustomerInvoices::factory()->create([
            'customer_no' => '8811038480',
            'total_invoice' => '4894364',
            'status_lunas' => false,
        ]);

        CustomerInvoices::factory()->create([
            'customer_no' => '9106233996',
            'total_invoice' => '2767720',
            'status_lunas' => true,
        ]);
    }
}
