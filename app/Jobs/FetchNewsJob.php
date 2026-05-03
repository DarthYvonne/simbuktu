<?php

namespace App\Jobs;

use App\Services\News\RssFetcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchNewsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 60;

    public function handle(RssFetcher $fetcher): void
    {
        $fetcher->fetchAll();
    }
}
