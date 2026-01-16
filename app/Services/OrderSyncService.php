<?php
    namespace App\Services;

    use App\Models\ChannelSetting;
    use Illuminate\Support\Facades\Log;
    use App\Http\Controllers\Seller\Channels\ManageOrders\WoocommerceOrderSyncController;
    use App\Http\Controllers\Seller\Channels\ManageOrders\ShopifyOrderSyncController;
    use App\Http\Controllers\Seller\Channels\ManageOrders\ShopbaseOrderSyncController;
    use App\Http\Controllers\Seller\Channels\ManageOrders\OpencartOrderSyncController;
    use App\Http\Controllers\Seller\Channels\ManageOrders\WixOrderSyncController;
    use Exception;

    class OrderSyncService
    {
        
        /**
         * Map of <channel_key> => <controller instance>.
         *
         * @var array<string, object>
         */
        protected array $channels;
        public function __construct(
            WoocommerceOrderSyncController $woocommerce,
            ShopifyOrderSyncController     $shopify,
            ShopbaseOrderSyncController    $shopbase,
            OpencartOrderSyncController    $opencart,
            WixOrderSyncController    $wix
        ) {
            $this->channels = [
                'woocommerce' => $woocommerce,
                'shopify'     => $shopify,
                'shopbase'    => $shopbase,
                'opencart'    => $opencart,
                'wix'    => $wix,
            ];
        }

        public function syncAllOrders($companyId,$syncType)
        {
            $messages = ['success' => [], 'error' => []];

            // ── 1. Check company settings ─────────────────────────────────────────
            $settings = ChannelSetting::where('company_id', $companyId)
                ->where('status', 1)
                ->where('channel_code', '!=', 'custom')
                ->first();

            if (!$settings) {                
                throw new Exception("No channels are connected");
            }

            // ── 2. Run each channel sync ──────────────────────────────────────────
            foreach ($this->channels as $key => $controller) {
                try {
                    $response = $controller->syncOrders($companyId,$syncType);
                    // Pull success / error flashes that each controller may set
                    $success = $response->getSession()->get("{$key}_success");
                    $error   = $response->getSession()->get("{$key}_error");

                    if ($success) { $messages['success'][] = $success; }
                    if ($error)   { $messages['error'][]   = $error;   }

                } catch (Exception $e) {
                    $messages['error'][] = "[{$key}] " . $e->getMessage();
                }
            }

            // ── 3. Optional: persist a log entry ──────────────────────────────────
            Log::info('Order sync finished', [
                'company_id' => $companyId,
                'success'    => $messages['success'],
                'error'      => $messages['error'],
            ]);

            return $messages;
        }
    }
