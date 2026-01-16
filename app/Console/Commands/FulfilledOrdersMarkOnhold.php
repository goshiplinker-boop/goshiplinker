<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderWebhook;

class FulfilledOrdersMarkOnhold extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fulfilled-order-onhold {company_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark fulfilled orders as on-hold if they are in new status at our end.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companyId = $this->argument('company_id');

        $query = OrderWebhook::where('webhook_type', 'ORDERS_FULFILLED')
            ->where('status', 0)
            ->select('id', 'company_id', 'channel_order_id', 'channel_id');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $count = 0;

        // Process in chunks of 500 records
        $query->chunkById(500, function ($orders) use (&$count) {
            DB::beginTransaction();

            try {
                foreach ($orders as $order) {
                    Order::where('company_id', $order->company_id)
                        ->where('status_code', 'N')
                        ->where('vendor_order_id', $order->channel_order_id)
                        ->where('channel_id', $order->channel_id)
                        ->update(['status_code' => 'H']);
                }

                // Mark webhooks as processed
                OrderWebhook::whereIn('id', $orders->pluck('id'))
                    ->update(['status' => 1]);

                DB::commit();

                $count += $orders->count();
                $this->info("Processed {$orders->count()} orders in this batch...");
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error('Error occurred: ' . $e->getMessage());
            }
        });

        if ($count > 0) {
            $this->info("✅ Successfully processed {$count} fulfilled orders.");
        } else {
            $this->info("No fulfilled orders found to mark on hold.");
        }

        return Command::SUCCESS;
    }
}
