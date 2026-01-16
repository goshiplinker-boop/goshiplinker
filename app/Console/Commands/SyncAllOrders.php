<?php

namespace App\Console\Commands;
use App\Services\OrderSyncService;
use Illuminate\Console\Command;
use App\Models\ChannelSetting;
class SyncAllOrders extends Command
{
    /**
     * Execute the console command.
     */

    /**
    * The name and signature of the console command.
    *
    * @var string
    */
    protected $signature = 'app:sync-all-orders';
    /**
    * The console command description.
    *
    * @var string
    */
    protected $description = 'Sync all orders for all active channel settings';

    protected $orderSyncService;

    public function __construct(OrderSyncService $orderSyncService)
    {
        parent::__construct();
        $this->orderSyncService = $orderSyncService;
    }

    public function handle()
    {
        $channelSettings = ChannelSetting::where('status', 1)
        ->where('channel_code', '!=', 'custom')
        ->get();

        foreach ($channelSettings as $setting) {
            $otherDetails = json_decode($setting->other_details ?? '{}', true);
            // Check if auto_sync is enabled
            $autoSync = $otherDetails['auto_sync'] ?? 0;

            if ($autoSync) {
                $this->orderSyncService->syncAllOrders($setting->company_id,'auto');
            }            
        }
    }
}
