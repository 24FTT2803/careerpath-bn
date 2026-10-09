<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Optional on a live server with a cron entry for schedule:run.
 * Without it, page loads still send these (see CheckPremiumExpiry).
 */
Schedule::command('premium:notify-expired')->everyFiveMinutes()->withoutOverlapping();
