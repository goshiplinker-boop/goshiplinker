<?php

namespace App\Console\Commands;

use App\Mail\NotificationMail;
use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class Mailcornjob extends Command
{
    protected $signature = 'app:mailcornjob';
    protected $description = 'Send email';

    public function handle()
    {
        // Query for pending notifications
        $notifications = DB::table('notifications as n')           
            ->join('companies as c', 'c.id', '=', 'n.company_id')
            ->join('notification_templates as nt', 'nt.event_type', '=', 'n.event')
            ->select('n.id', 'c.company_email_id', 'n.event', 'nt.meta', 'nt.body','c.legal_registered_name', 'c.brand_name')
            ->where('n.user_type','seller')
            ->where('n.channel', 'email')
            ->where('n.event', 'New Registration')
            ->where('n.sent_status', 0)
            ->where('nt.status', 1)
            ->where('c.status', 1)
            ->whereNull('nt.company_id')
            ->whereColumn('nt.channel', 'n.channel')
            ->whereColumn('nt.user_type', 'n.user_type')
            ->orderBy('n.id', 'asc')
            ->get();
         
        if ($notifications->isEmpty()) {
            $this->error('No pending notifications.');
            return;
        }
        // Fetch the admin email template once, not inside the loop
        $adminTemplate = NotificationTemplate::where('channel', 'email')
            ->where('user_type', 'admin')
            ->where('event_type', 'New Registration')
            ->whereNull('company_id')
            ->where('status', 1)
            ->first();

        if (!$adminTemplate) {
            $this->error('Admin notification template not found.');
            return;
        }

        // Process each notification
        foreach ($notifications as $notification) {
            $metaData = $notification->meta ? json_decode($notification->meta, true) : [];
            $subject = $metaData['subject'] ?? 'Default Subject';
            try {
                $body = $notification->body;
                $customerName = $notification->legal_registered_name ?? $notification->brand_name; 
                $body = str_replace('{customer_name}', $customerName, $body);

                Mail::to($notification->company_email_id)->send(new NotificationMail($body, $subject));
                $adminMetaData = $adminTemplate->meta;
                $adminSubject = $adminMetaData['subject'] ?? 'New User Registration';
                $adminBody = $adminTemplate->body;
                $adminBody = str_replace('{user_email}', $notification->company_email_id, $adminBody);
                Mail::to(env('NOTIFICATION_EMAIL_ID'))->send(new NotificationMail($adminBody, $adminSubject));
                $this->info("Notification email sent successfully to user {$notification->company_email_id}.");
                $this->info("Admin notification sent successfully for new registration of {$notification->company_email_id}.");

                // Update notification as sent
                Notification::where('id', $notification->id)->update(['sent_status' => 1]);

            } catch (\Exception $e) {
                // Handle errors and log them
                $this->error("Failed to send email to user {$notification->company_email_id} or admin: " . $e->getMessage());
            }
        }
    }
}
