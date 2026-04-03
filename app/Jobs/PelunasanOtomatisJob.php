<?php

namespace App\Jobs;

use App\Actions\PelunasanOtomatis\PelunasanOtomatis;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PelunasanOtomatisJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        (new PelunasanOtomatis(
            null,
        ))();
    }
}
