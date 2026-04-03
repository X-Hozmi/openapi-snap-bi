<?php

namespace Database\Seeders\Logger;

use App\Models\Logger\LoggerAccessOpenAPI;
use Illuminate\Database\Seeder;

class LoggerSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        LoggerAccessOpenAPI::factory(20)->create();
    }
}
