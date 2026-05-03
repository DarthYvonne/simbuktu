<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('simulation:tick')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::job(new \App\Jobs\FetchNewsJob())->everyThirtyMinutes();
Schedule::job(new \App\Jobs\DigestNewsJob())->everySixHours();

Schedule::command('covers:refresh')->dailyAt('03:15')->withoutOverlapping();
