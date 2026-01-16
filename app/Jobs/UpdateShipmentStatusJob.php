<?php

namespace App\Jobs;

use App\Services\ShopifyGraphQLService;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateShipmentStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $shipment;

    /**
     * Create a new job instance.
     *
     * @param  object  $shipment
     */
    public function __construct(object $shipment)
    {
        $this->shipment = $shipment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $shipment = $this->shipment;

        try {
            // Extract details from the shipment object
            $vendorOrderId = $shipment->vendor_order_id;
            $fulfillmentId = $shipment->fulfillment_id;
            $currentStatus = $shipment->current_status;
            $channelUrl = $shipment->channel_url;
            $clientId = $shipment->client_id;
            $secretKey = $shipment->secret_key;
            $shipment_id = $shipment->id;
            $otherDetails = json_decode($shipment->other_details, true);

            $payload = [
                'shipment_id' => $shipment_id,
                'vendor_order_id' => $vendorOrderId,
                'fulfillment_id' => $fulfillmentId,
                'current_status' => $currentStatus,
            ];

            if (!empty($otherDetails)) {
                $payload = array_merge($payload, $otherDetails);
            }

            $shopifyGraphQLService = new ShopifyGraphQLService();

            try {
                $shopifyGraphQLService->shipmentStatusUpdate($channelUrl, $secretKey, $payload);
               
            } catch (\Exception $e) {
                Log::error("Failed to update shipment status for order {$vendorOrderId}: {$e->getMessage()}");
            }

        } catch (\Throwable $e) {
            Log::error("❌ Error updating shipment status for Order ID: {$shipment->vendor_order_id}", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->release(30); // Retry after 30 seconds
        }
    }
}
