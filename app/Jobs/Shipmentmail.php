<?php

namespace App\Jobs;

use App\Mail\NotificationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\ShipmentStatus;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Shipmentmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {  
        $order_id = $this->data['order_id'];
        $order = DB::table('orders')
            ->join('companies', 'orders.company_id', '=', 'companies.id')
            ->where('orders.id', $order_id)
            ->select(
                'orders.fullname','orders.vendor_order_number','orders.email',
                'companies.legal_registered_name',
                'companies.brand_name'
            )
            ->first();
        $current_status_code = $this->data['current_status_code'];
        $old_current_status = $this->data['old_current_status_code'];
        $company_id = $this->data['company_id'];

        if ($old_current_status != $current_status_code) {
            $shipment_status_name = ShipmentStatus::where('code', $current_status_code)->value('name');

            if ($shipment_status_name) {
                $template = NotificationTemplate::where('event_type', $shipment_status_name)
                    ->where('user_type', 'buyer')
                    ->where('channel', 'email')
                    ->where('status', '1')
                    ->where('company_id', $company_id)
                    ->first();

                if ($template) {
                    $buyer_name = $order->fullname ?? 'Customer';
                    $order_number = $order->vendor_order_number ?? 'N/A';
                    $company_name = $order->legal_registered_name ?? $order->brand_name;

                    $body = str_replace(
                        ['{{buyer_name}}', '{{order_number}}', '{{company_name}}', '{{shipment_status}}'],
                        [$buyer_name, $order_number, $company_name, $shipment_status_name],
                        $template->body
                    );
                    $metaData = is_string($template->meta) ? json_decode($template->meta, true) : $template->meta;
                    $subject = $metaData['subject'] ?? 'Order Status Update';
                    $subjects = str_replace('[Order Number]',  $order_number, $subject);
                    $buyer_email = $order->email ?? null;
                    if ($buyer_email) {
                        try {
                            Log::info("Sending email to: $buyer_email | Subject: $subjects");
                            Mail::to($buyer_email)->send(new NotificationMail($body, $subjects));
                        } catch (\Exception $e) {
                            Log::error("Failed to send email to $buyer_email: " . $e->getMessage());
                        }
                    } else {
                        Log::warning("Order ID $order_id has no buyer email address.");
                    }
                } else {
                    Log::warning("Notification template not found for event '$shipment_status_name' and company ID $company_id.");
                }
            } else {
                Log::warning("Shipment status code '$current_status_code' not found.");
            }
        }
    }
}
