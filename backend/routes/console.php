<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Revert expired free trials to the default plan.
Schedule::command('subscriptions:expire-trials')->daily();

// Send scheduled marketing campaigns whose time has arrived (premium).
Schedule::command('campaigns:dispatch')->everyMinute();

// Generate orders for due recurring-order subscriptions (premium).
Schedule::command('recurring:generate')->hourly();
