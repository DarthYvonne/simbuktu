<?php

namespace App\Jobs;

use App\Services\News\NewsDigester;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DigestNewsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 120;

    public function handle(NewsDigester $digester): void
    {
        $digester->refresh();
    }
}
