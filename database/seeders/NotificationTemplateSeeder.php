<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NotificationTemplate;


class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void 
    {
        $channels = ['email', 'sms', 'rcs', 'whatsapp'];
        $userTypes = [
            'buyer' => ['New', 'Ready to Ship', 'Shipped', 'In-Transit', 'Undelivered', 'Delivered', 'RTO', 'RTO Delivered'],
            'seller' => ['New Registration', 'Payment Failed', 'Before Subscription Expired', 'After Subscription Expired'],
            'admin' => ['New Registration'],
        ];
    
        foreach ($userTypes as $userType => $eventTypes) {
            foreach ($eventTypes as $eventType) {
                foreach ($channels as $channel) {    
                    NotificationTemplate::firstOrCreate([
                        'company_id' => null, 
                        'channel' => $channel, // ✅ Ensure this is not empty
                        'user_type' => $userType,
                        'event_type' => $eventType,
                    ], [
                        'body' => "Notification for {$userType}: {$eventType}",
                        'meta' => ['subject' => ucfirst($eventType) . " Notification"],
                        'status' => true,
                    ]);
                }
            }
        }
    }
}
