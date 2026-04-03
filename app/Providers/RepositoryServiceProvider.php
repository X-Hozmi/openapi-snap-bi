<?php

namespace App\Providers;

use App\Interfaces\PG\TransferVA\TVAInquiryRepositoryInterface;
use App\Interfaces\PG\TransferVA\TVAPaymentRepositoryInterface;
use App\Repositories\PG\TransferVA\TVAInquiryRepository;
use App\Repositories\PG\TransferVA\TVAPaymentRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(TVAInquiryRepositoryInterface::class, TVAInquiryRepository::class);
        $this->app->bind(TVAPaymentRepositoryInterface::class, TVAPaymentRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
