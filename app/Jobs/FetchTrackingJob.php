<?php

namespace App\Jobs;

use App\Http\Controllers\Seller\Couriers\Fulfillment\AssignTrackingNumber;
use App\Models\ShipmentInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\BuyerShippingLabelSetting;
class FetchTrackingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,Batchable;

    public int $shipmentId;

    public $tries = 3;           // Retry up to 3 times
    public $backoff = [10, 30];  // Seconds before retry

    public function __construct(int $shipmentId)
    {
        $this->shipmentId = $shipmentId;
    }

    public function handle()
    {
        
        $shipment = ShipmentInfo::with('order')->find($this->shipmentId);
        if (!$shipment) {
            \Log::warning("Shipment {$this->shipmentId} not found.");
            return;
        }
        $auto_shipped=0;
        if($shipment->order->status_code=='P' || $shipment->order->status_code=='M'){
            $buyerSettings = BuyerShippingLabelSetting::where('company_id', $shipment->company_id)->first();
            $extra_details = json_decode($buyerSettings->extra_details ?? '{}', true);
            $auto_shipped = $extra_details->auto_shipped??1;
        }

        $trackingService = new AssignTrackingNumber();

        try {
            $trackingService->track(
                $shipment->tracking_id,
                $shipment->courier_id,
                $shipment->company_id
            );
        } catch (\Exception $e) {
            \Log::error("Failed to fetch tracking for order {$shipment->order_id}: {$e->getMessage()}");
        }
    }
}
