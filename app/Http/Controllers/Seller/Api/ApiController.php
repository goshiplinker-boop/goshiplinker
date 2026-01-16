<?php

namespace App\Http\Controllers\Seller\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ApiCredential;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{
    public function show()
    {
        $credentials = Auth::user()->apiCredential;
   
        return view('seller.api.show', compact('credentials'));
    }

    public function generate(Request $request)
    {
        $user = Auth::user();
        if ($user->apiCredential) {
            return back()->with('error', 'API credentials already exist.');
        }

        $apiKey = 'seller_' . Str::random(10);
        $apiSecret = Str::random(32);
        ApiCredential::create([
            'user_id' => $user->id,
            'api_key' => $apiKey,
            'api_secret' => bcrypt($apiSecret),
        ]);
        return redirect()->route('api.credentials.show')->with([
            'success' => 'API credentials generated successfully.',
            'api_key' => $apiKey,
            'api_secret' => $apiSecret, // Show only once
        ]);
    }

    public function issueToken(Request $request)
    {
        $request->validate([
            'api_key' => 'required',
            'api_secret' => 'required',
        ]);

        $credential = ApiCredential::where('api_key', $request->api_key)->first();
        $companyId = $credential->user->company_id;
        if (!$credential || !Hash::check($request->api_secret, $credential->api_secret)) {
            return response()->json(['message' => 'Invalid API credentials'], 401);
        }

        $token = $credential->user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'company_id' => $companyId ?? null, 
        ]);
    
    }
}
