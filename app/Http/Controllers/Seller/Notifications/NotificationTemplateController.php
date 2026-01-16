<?php

namespace App\Http\Controllers\Seller\Notifications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NotificationTemplate;

class NotificationTemplateController extends Controller
{    
    
    public function index(Request $request)
    {
        $companyId =  null;
        $segment = request()->segment(1);
        $notification_route = 'notification_list';

        if($segment == 'admin'){
            $notification_route = 'seller_notification_list';
            $companyId = session('company_id') ?? 0;
        }

        if ($companyId > 0) {
            $hasTemplates = NotificationTemplate::where('company_id', $companyId)->exists();

            if (!$hasTemplates) {
                $adminTemplates = NotificationTemplate::where('company_id', null)
                    ->where('user_type', '!=', 'admin')
                    ->Where('event_type', '!=', 'New Registration')
                    ->get();

                foreach ($adminTemplates as $template) {
                    NotificationTemplate::create([
                        'company_id' => $companyId,
                        'channel' => $template->channel,
                        'user_type' => $template->user_type,
                        'event_type' => $template->event_type,
                        'body' => $template->body,
                        'meta' => $template->meta
                    ]);
                }
            }
        }

        $tab = $request->query('tab', 'email');       
        $templates = NotificationTemplate::where('company_id', $companyId)
            ->where('channel', $tab)
            ->get();

        $counts = [
            'sms' => NotificationTemplate::where('company_id', $companyId)->where('channel', 'sms')->count(),
            'whatsapp' => NotificationTemplate::where('company_id', $companyId)->where('channel', 'whatsapp')->count(),
            'email' => NotificationTemplate::where('company_id', $companyId)->where('channel', 'email')->count(),
            'Rcs' => NotificationTemplate::where('company_id', $companyId)->where('channel', 'Rcs')->count()
        ];

        return view('seller.notifications.notification_list', compact('tab', 'counts', 'templates', 'notification_route'));
    }

    public function create()
    {
        $companyId = !empty(session('company_id')) ? session('company_id') : null;
        return view('seller.notifications.create');
    }

    public function edit($id)
    {
        $companyId = !empty(session('company_id')) ? session('company_id') : null;
        $template = NotificationTemplate::find($id); 
        $segment = request()->segment(1);

        $update_route = 'notifications_update'; 
        if ($segment == 'admin') {
            $update_route = 'seller_notifications_update'; 
        }

        return view('seller.notifications.edit', compact('template', 'id', 'update_route'));
    }

    public function update(Request $request, $id)
    {
        $companyId = session('company_id');
       
        $template = NotificationTemplate::find($id);
        if (!$template) {
            return redirect()->route('notification_list')->with('error', 'Template not found.');
        }

        $templateType = $request->input('channel'); 
        $validatedData = $request->validate([
            'subject' => $templateType == 'email' ? 'required|string' : 'nullable|string',
            'template' => 'required|string',
            'sender_id' => in_array($templateType, ['whatsapp', 'sms', 'rcs']) ? 'required|string' : 'nullable|string',
        ]);

        $metaData = [];
        if ($templateType == 'email') {
            $metaData['subject'] = $validatedData['subject'] ?? null;
        }
        if (in_array($templateType, ['whatsapp', 'sms', 'rcs'])) {
            $metaData['sender_id'] = $validatedData['sender_id'] ?? null;
        }

        $template->meta = $metaData;
        $template->body = $validatedData['template'];

        $segment = request()->segment(1);

        $update_route = 'notifications_update'; 
        $redirect_url = 'notification_list';
        if ($segment == 'admin') {
            $redirect_url = 'seller_notification_list';
            $update_route = 'seller_notifications_update';
            $companyId = session('company_id') ?? 0;
        }
        $template->save();
       
        return redirect()->route($redirect_url)->with('success', ucfirst ($template->channel). " " . 'Template updated successfully.');
    }
}
