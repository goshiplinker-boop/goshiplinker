<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\company;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationMail;

class ExpiredUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expired-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to send email reminders to expired subscriptions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $results = DB::table('subscriptions as s')
            ->join('companies as c', 's.company_id', '=', 'c.id')
            ->join('notification_templates as nt', 's.company_id', '=', 'nt.company_id')
            ->where('nt.channel', '=', 'email')
            ->where('nt.user_type', '=', 'seller')
            ->where('nt.event_type', '=', 'Before Subscription Expired')
            ->whereNotNull('s.expiry_date')
            ->where('nt.status', 1)
            ->where('c.subscription_status', 1)
            ->where('c.status', 1)
            ->select('s.id', 's.expiry_date', 'c.subscription_status', 'nt.company_id', 'c.company_email_id', 'nt.meta', 'nt.body', 'c.legal_registered_name', 'c.brand_name')
            ->groupBy('s.company_id')
            ->orderByDesc('s.expiry_date')
            ->get();
    
        if ($results->isEmpty()) {
            $this->error('No pending notifications.');
            return;
        }
    
        foreach ($results as $result) {
            $expiryDate = \Carbon\Carbon::parse($result->expiry_date);
            $currentDate = now()->startOfDay();
            $daysLeft = $currentDate->diffInDays($expiryDate, false);
            $customerName = $result->legal_registered_name ?? $result->brand_name;
            $metaData = json_decode($result->meta, true);
            
            try {
                $body = $result->body;
                $body = str_replace('{{expiry_date}}', $expiryDate->toDateString(), $body); 
                $body = str_replace('{{customer_name}}', $customerName, $body); 
                $body = str_replace('{{days_left}}', $daysLeft . ' days left', $body); 
                $subject = $metaData['subject'] ?? 'Subscription Expired';
                
                if ($expiryDate->isToday() || $expiryDate->lt($currentDate)) {
                    Mail::to($result->company_email_id)->send(new NotificationMail($body, $subject));
                    $this->info("Email sent to: " . $result->company_email_id . " regarding subscription expired today.");
                    company::where('id', $result->id)->update(['subscription_status' => 0]);
                } elseif ($expiryDate->isFuture() && (
                    $currentDate->diffInDays($expiryDate) == 2 || 
                    $currentDate->diffInDays($expiryDate) == 5 || 
                    $currentDate->diffInDays($expiryDate) == 10
                )) {
                    Mail::to($result->company_email_id)->send(new NotificationMail($body, $subject));
                    $this->info("Email sent to: " . $result->company_email_id . " for " . $currentDate->diffInDays($expiryDate) . " days left reminder.");
                }
            } catch (\Exception $e) {
                $this->info("Failed to send email to: " . $result->company_email_id . ". Error: " . $e->getMessage());
            }
        }
    }
}
