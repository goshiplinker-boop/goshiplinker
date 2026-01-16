<?php

namespace App\Http\Controllers\Seller\Channels\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use UnexpectedValueException;
use App\Models\ChannelSetting;
use App\Models\Channel;
use App\Models\Company;
use App\Models\User;

class WixWebhookController extends Controller
{
    /**
     * Handle Wix lifecycle webhooks.
     *
     * Wix sends a signed JWT **in the raw body** of the POST request.
     * The JWT header uses alg = RS256 and kid = your public-key ID.
     *
     * @see https://dev.wix.com/docs/app-market/apps/webhooks
     */
    public function handleWebhook(Request $request)
    {
        $jwt = $request->getContent();

        if (blank($jwt) || substr_count($jwt, '.') !== 2) {
            Log::warning('Wix webhook hit with malformed JWT', ['body' => $jwt]);
            return response('Bad JWT', 400);
        }

        $publicKey = env('WIX_PUBLIC_KEY');
        if (blank($publicKey)) {
            Log::critical('Missing Wix public key');
            return response('Server mis-configuration', 500);
        }

        try {
            $decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
        } catch (ExpiredException $e) {
            return response('JWT expired', 400);
        } catch (SignatureInvalidException $e) {
            return response('Bad signature', 400);
        } catch (UnexpectedValueException $e) {
            return response('Malformed JWT', 400);
        } catch (\Throwable $e) {
            Log::error('Webhook decode error', ['msg' => $e->getMessage()]);
            return response('Decode error', 400);
        }

        $payload = $decoded->data ?? null;
        $payload = $payload ? json_decode($payload) : null;

        $eventType  = $payload->eventType  ?? null;
        $instanceId = $payload->instanceId ?? null;
        $eventData  = $payload->data       ?? null;

        if (! $eventType || ! $instanceId) {
            return response('Missing fields', 400);
        }

        switch ($eventType) {
            case 'AppInstalled':
                Log::info("Wix app Installed for client_id: $instanceId");
                break;
            case 'AppRemoved':
                $this->handleRemoved($instanceId);
                break;
            default:
                Log::notice('Unhandled Wix event', ['type' => $eventType, 'id' => $instanceId]);
        }

        return response('ok', 200);
    }

   

    /**
     * Handle Wix app removal event.
     */
    private function handleRemoved(string $instanceId)
    {
        ChannelSetting::where('client_id', $instanceId)->update(['status' => 0]);
        Log::info("Wix app uninstalled for client_id: $instanceId");
    }
}
