<?php

namespace App\Services;

use App\Models\OrderLog;

class OrderLogService
{
    /**
     * Create an order log entry.
     *
     * @param int $companyId
     * @param int $orderId
     * @param string $vendorOrderId
     * @param string $type
     * @param array|null $payload
     * @param array|null $response
     * @param boolean $status
     * @return OrderLog
     */
    public function createLog($companyId, $orderId, $vendorOrderId, $type, $payload = null, $response = null,$status=false)
    {
        return OrderLog::create([
            'company_id' => $companyId,
            'order_id' => $orderId,
            'vendor_order_id' => $vendorOrderId,
            'type' => $type,
            'payload' => $payload,
            'response' => $response,
            'status' => $status
        ]);
    }
}
