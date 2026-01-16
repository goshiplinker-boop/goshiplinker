<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use App\Models\ChannelSetting;
class WixOAuthService
{
  
    public function createAccessToken(ChannelSetting $channel_setting)
    {
        // 1. Ask Wix for a token (client_credentials flow)
        $response = Http::asJson()->post('https://www.wixapis.com/oauth2/token', [
            'grant_type'    => 'client_credentials',
            'client_id'     => config('services.wix.app_id'),
            'client_secret' =>  config('services.wix.secret'),
            'instance_id'   => $channel_setting->client_id,
        ]);
        // 2. Handle failure
        if ($response->failed()) {
            throw new \RuntimeException(
                'Failed to obtain Wix access token: '
                . $response->status() . ' → ' . $response->body()
            );
        }

        // 3. Persist token + expiry (Wix tokens last 4 h by default)
        $data = $response->json();
        $data['token_expires_at']=Carbon::now()->addSeconds($data['expires_in'] ?? 14400)->format('Y-m-d H:i:s');
        
        $channel_setting->update([
            'other_details'=>json_encode($data)
        ]);
        // 4. Return the fresh model
        $channel_setting->refresh();
        $access_token = $data['access_token']??'';
        return $access_token;
    }
}
