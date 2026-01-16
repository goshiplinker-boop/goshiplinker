<?php

namespace App\Services;

use Razorpay\Api\Api;
use Exception;
use Illuminate\Support\Facades\Log;
class RazorpayService
{
    protected $api;

    public function __construct()
    {
        $this->api = new Api(config('razorpay.key_id'), config('razorpay.key_secret'));
    }

    public function createOrder($amount, $receipt, $currency = 'INR')
    {
        try {
            return $this->api->order->create([
                'amount' => $amount * 100, // Amount in paise
                'currency' => $currency,
                'receipt' => $receipt,
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            throw new Exception('Error creating Razorpay order: ' . $e->getMessage());
        }
    }

    public function verifySignature($attributes)
    {
        $generatedSignature = hash_hmac(
            'sha256',
            $attributes['razorpay_order_id'] . "|" . $attributes['razorpay_payment_id'],
            config('razorpay.key_secret')
        );

        return hash_equals($generatedSignature, $attributes['razorpay_signature']);
    }
}
