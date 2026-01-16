<?php

// use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
//use App\Jobs\ShopifyOrderSyncJob;
// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();
 

Schedule::command('app:fetch-tracking-details')->hourly(); 
Schedule::command('app:sync-all-orders')
    ->cron('0 */1 * * *')
    ->name('sync-all-orders')
    ->withoutOverlapping()
    ->runInBackground();
Schedule::command('app:stores-shipment-status-update')->everyThirtyMinutes();
Schedule::command('app:fulfilled-order-onhold')->everyThreeHours();

