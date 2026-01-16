<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\SmsGateway;
use App\Models\SmsDltTemplate;
use App\Models\order;
use Illuminate\Support\Facades\Log;
use App\Models\ShipmentStatus;
use Illuminate\Support\Facades\Http;
use App\Models\Notification;



class SendSmsJob implements ShouldQueue
{
    use Queueable;

    protected $data;
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $sentstatus = 3;
        $order_id = $this->data['order_id'];
        $current_status_code = $this->data['current_status_code'];
        $old_status_code = $this->data['old_current_status_code'];
        $company_id = $this->data['company_id'];
        $sendsmsdata['event'] = $current_status_code;
        $current_status_code = $this->data['event'];
        $notificationId = $this->data['notification_id'];
        $gateway = $this->data['gateway_data'];
       
        $template = SmsDltTemplate::where('order_status', $current_status_code)
            ->where('company_id', $company_id)
            ->where('status', 1)
            ->first();
            $message = "Template does not  exist";
        if ($template) {
            $order = Order::find($order_id);
            $message = "shipment status is not change";
            if ($old_status_code != $current_status_code) {
                $buyer_name = $order->fullname ?? 'Customer';
                $order_number = $order->vendor_order_number ?? 'N/A';
                $phone = $order->s_phone ?: $order->b_phone ?: $order->phone_number;

                $params = [
                    $gateway->mobile => $phone,
                    $gateway->dlt_header_name => $gateway->dlt_header_id,
                    $gateway->dlt_template_name => $gateway->dlt_template_id,
                ];

                $other_parameters = (!empty($gateway->other_parameters)) ? json_decode($gateway->other_parameters, true) : [];

                if (!empty($other_parameters)) {
                    foreach ($other_parameters as $other_parameter) {
                        $params[$other_parameter['key']] = $other_parameter['value'];
                    }
                }

                $url_parms = http_build_query($params);
                $sms = $template->message_content;
                $Message = str_replace(
                    ['{customer_name}', '{order_id}'],
                    [$buyer_name, $order_number],
                    $sms
                );
                $Message = rawurlencode($Message);
                $templateid = $template->template_registration_id;
                $finalUrl = str_replace(['message','gateway_template_id'], [$Message,$templateid], $url_parms);
                $finalUrl = $gateway->gateway_url . '?' . $finalUrl;

                if (strtoupper($gateway->http_method) === 'GET') {
                    try {
                        $response = Http::get($finalUrl);
                        $responseData = json_decode($response->body(), true);

                        if (isset($responseData['STATUS']) && $responseData['STATUS'] === 'OK') {
                            $sentstatus = 1;
                            Log::info("SMS sent to $phone | Message sent successfully.");
                        } else {
                            $sentstatus = 2;
                            $info = $responseData['RESPONSE']['INFO'] ?? 'Unknown error';
                            Log::warning("SMS failed for $phone | $info");
                        }
                       
                        Notification::where('id', $notificationId)->update([
                            'sent_status' => $sentstatus,  
                            'response' => $response->body(), 
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to send SMS: " . $e->getMessage());
                        $sentstatus = 2;  
                        Notification::where('notification_id', $notificationId)->update([
                            'sent_status' => $sentstatus,
                            'response' => json_encode(['error' => $e->getMessage()]),
                        ]);
                    }
                }
            }
        }
                
        if ($sentstatus === 3) {
           
            Notification::where('id', $notificationId)->update([
                'sent_status' => 3, 
                'response' => json_encode(['info' => $message]),
            ]);
        }
    }
    
}
