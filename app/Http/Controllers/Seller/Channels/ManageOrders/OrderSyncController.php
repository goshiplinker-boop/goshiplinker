<?php
namespace App\Http\Controllers\Seller\Channels\ManageOrders;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ChannelSetting;
use App\Services\OrderSyncService;
use Exception;


class OrderSyncController extends Controller
{
     
    public function __construct(OrderSyncService $orderSyncService) {
        $this->orderSyncService = $orderSyncService;
    }

    public function syncAllOrders($companyId = null,$syncType="auto")
    {
        $companyId ??= session('company_id');
        try {
            $responseMessages = $this->orderSyncService->syncAllOrders($companyId,$syncType);

            // Web request: session() exists
            if (app()->runningInConsole() === false && session()->has('company_id')) {
                return redirect()->route('order_list')->with([
                    'success' => implode("<br>", $responseMessages['success']),
                    'error'   => implode("<br>", $responseMessages['error']),
                ]);
            }

            // CLI or API response: return raw data or JSON
            return $responseMessages;

        } catch (\Exception $e) {
            if (app()->runningInConsole() === false && session()->has('company_id')) {
                return redirect()->route('order_list')->with('error', $e->getMessage());
            }

            return ['error' => [$e->getMessage()], 'success' => []];
        }      
    }
    
}
