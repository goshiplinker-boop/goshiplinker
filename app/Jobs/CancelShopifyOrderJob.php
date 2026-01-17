<?php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use App\Models\Order;
use App\Models\OrderWebhook;

class CancelShopifyOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $webhookOrder;

    public function __construct(array $webhookOrder)
    {
        $this->webhookOrder = $webhookOrder;
    }

    public function handle(): void
    {
        $companyId = $this->webhookOrder['company_id'];
        $channelId = $this->webhookOrder['channel_id'];
        $vendorOrderId = $this->webhookOrder['channel_order_id'];

        DB::beginTransaction();

        try {
            // Cancel order only if still NEW
            $updated = Order::where('company_id', $companyId)
                ->where('channel_id', $channelId)
                ->where('vendor_order_id', $vendorOrderId)
                ->where('status_code', 'N')
                ->update([
                    'status_code' => 'C',
                    'updated_at' => now(),
                ]);

            // Mark webhook as processed (idempotency)
            OrderWebhook::where('company_id', $companyId)
                ->where('channel_id', $channelId)
                ->where('channel_order_id', $vendorOrderId)
                ->where('webhook_type', 'ORDERS_CANCELLED')
                ->update(['status' => 1]);

            DB::commit();

            Log::info('Order cancelled locally', [
                'company_id' => $companyId,
                'channel_id' => $channelId,
                'vendor_order_id' => $vendorOrderId,
                'rows_updated' => $updated,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed to cancel order locally', [
                'error' => $e->getMessage(),
                'payload' => $this->webhookOrder,
            ]);

            throw $e; // allows Laravel retry
        }
    }
}
