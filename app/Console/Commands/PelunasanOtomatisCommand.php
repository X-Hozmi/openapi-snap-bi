<?php

namespace App\Console\Commands;

use App\Actions\PelunasanOtomatis\PelunasanOtomatis;
use App\Traits\CapturesCommandOutput;
use Illuminate\Console\Command;

class PelunasanOtomatisCommand extends Command
{
    use CapturesCommandOutput;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pelunasan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute Pelunasan Otomatis process';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        (new PelunasanOtomatis($this))();
    }
}
