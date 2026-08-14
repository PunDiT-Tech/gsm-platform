<?php

use App\Commands\ExpireUnpaidOrders;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Schedule::command(ExpireUnpaidOrders::class)->everyFifteenMinutes();

Schedule::call(function () {
    Cache::put('scheduler-last-run', now(), 3600);
})->everyFiveMinutes();
