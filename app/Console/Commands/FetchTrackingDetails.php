<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShipmentInfo;
use App\Jobs\FetchTrackingJob;
use Illuminate\Support\Facades\Bus;

class FetchTrackingDetails extends Command
{
    protected $signature = 'app:fetch-tracking-details';
    protected $description = 'Fetch tracking details for orders';

    public function handle()
    {
        $this->info("Dispatching tracking jobs in batches with delays...");

        $batchSize = 1000; // Process in batches of 1000 shipments
        $shipmentQuery = ShipmentInfo::where(function ($query) {
                $query->whereNull('current_status')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('current_status', '!=', 'DEL')
                                 ->where('current_status', '!=', 'RTOD');
                    });
            })
            ->where('updated_at', '>=', now()->subDays(30))
            ->orderBy('updated_at', 'asc');

        $shipmentQuery->chunkById($batchSize, function ($shipments) {
            $this->info("Dispatching batch of " . $shipments->count() . " jobs...");

            $batchJobs = [];
            $delaySeconds = 0;

            foreach ($shipments as $shipment) {
                // Add incremental delay for each job
                $batchJobs[] = (new FetchTrackingJob($shipment->id))
                                ->delay(now()->addSeconds($delaySeconds));

                $delaySeconds += 2; // delay 2 seconds for each job
            }

            Bus::batch($batchJobs)
                ->name('Fetch Tracking Jobs')
                ->dispatch();
        });

        $this->info("All batches dispatched successfully.");
    }
}
