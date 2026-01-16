<?php

namespace App\Http\Controllers\Seller\SellerPayment;
use App\Services\RazorpayService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SellerPaymentHistory;
use Illuminate\Support\Facades\Session;
use App\Models\Plan;
use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
class RazorpayController extends Controller
{
    protected $razorpayService;

    public function __construct(RazorpayService $razorpayService)
    {
        $this->razorpayService = $razorpayService;
    }
    public function index(Request $request)
    {
        $plan_id = $request->plan_id ?? 0;
        $duration_months = $request->duration_months ?? 0;

        if (empty($plan_id) || empty($duration_months)) {
            return response()->json(['error' => 'Invalid input parameters'], 400);
        }
        $user = auth()->guard('web')->user();
        //return $user;
        // Execute the query to fetch the plan
        $plan = Plan::with("durations")
            ->where('id', $plan_id)
            ->first();
        $duration_months = $plan->durations->where('duration_months', $duration_months)->first();
        // Debugging: If no plan is found, return a response
        if (!$plan) {
            return response()->json(['error' => 'Plan not found'], 404);
        }
        // Pass the `plan` data to the view or use it as needed
        return view('seller.seller_payments.razorpay', [
            'amount' => $duration_months->total_amount+$plan->setup_fee,
            'plan_id' => $plan_id,
            'duration_months' => $duration_months->duration_months,
            'plan' => $plan->name,
            'setup_fee' => $plan->setup_fee,
            'user'=>$user
        ]);
    }

    // Create Razorpay Orderrazorpay
    public function createOrder(Request $request)
    {   
        $company_id = session('company_id');
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);
        $amount = $request->input('amount');
        $plan_id = $request->input('plan_id')??0;
        $duration_months = $request->input('duration_months')??0;
        $receipt = 'receipt_' . time();
        if (empty($plan_id) || empty($duration_months)) {
            session()->flash('error', 'Invalid input parameters.');
            return response()->json(['error' => 'Invalid input parameters'], 400);
        }
        // Execute the query to fetch the plan
        $plan = Plan::with("durations")
            ->where('id', $plan_id)
            ->first();
        $plan_durations = $plan->durations->where('duration_months', $duration_months)->first();

        try {
            $order = $this->razorpayService->createOrder($amount, $receipt);

            $payment = SellerPaymentHistory::create([
                'company_id' => $company_id,
                'payment_order_id' => $order['id'],
                'amount' => $amount,
                'gateway' =>'Razorpay',
                'status' => 'pending',
            ]);
            $payment = Subscription::create([
                'company_id' => $company_id,
                'payment_order_id' => $order['id'],
                'plan_id' => $plan_id,
                'paid_amount' => $amount,
                'purchased_credits'=>$plan_durations->shipment_credits,
                'expiry_date' => date('Y-m-d', strtotime('+ '.$duration_months.' months'))
            ]);
            return response()->json([
                'order_id' => $order['id'],
                'amount' => $amount,
                'plan_id' => $plan_id,
                'currency' => $order['currency'],
                'status' => 'pending',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Order creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Order creation failed'], 500);
        }
    }

    // Razorpay Callback
    public function paymentCallback(Request $request)
    {  
        Log::error('Request Data: ' . json_encode($request->all()));

        $attributes = $request->only(['razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature']);

        if (empty($attributes['razorpay_order_id']) || empty($attributes['razorpay_payment_id']) || empty($attributes['razorpay_signature'])) {
            Log::error('Missing required Razorpay fields', $attributes);
            return response()->json(['error' => 'Invalid payment data received'], 400);
        }

        try {
            if ($this->razorpayService->verifySignature($attributes)) {
                $payment = SellerPaymentHistory::where('payment_order_id', $attributes['razorpay_order_id'])->first();

                if ($payment) {
                    $payment->update([
                        'txn_id' => $attributes['razorpay_payment_id'],
                        'status' => 'success',
                    ]);
                    Subscription::where('payment_order_id', $attributes['razorpay_order_id'])->update([
                        'payment_status'=>1
                    ]);
                    $subscription = Subscription::where('payment_order_id', $attributes['razorpay_order_id'])->first();
                    $plan_id = $subscription->plan_id??'';
                    $company_id = $subscription->company_id??'';
                    $plan = Plan::where('id', $plan_id)->first();
                    if($plan){
                        Company::where('id', $company_id)->update(['subscription_plan' => $plan->name,'subscription_status' => 1]);
                        Session::put("subscription_plan", $plan->name);
                        Session::put("subscription_status", 1);
                    }
                    session()->flash('success', 'Payment verified successfully');
                    return response()->json(['success'=>true,'message' => 'Payment verified successfully'], 200);
                }
                session()->flash('error', 'Payment record not found');
                $payment->update([
                    'txn_id' => $attributes['razorpay_payment_id'],
                    'status' => 'failed',
                ]);
                Subscription::where('payment_order_id', $attributes['razorpay_order_id'])->update([
                    'payment_status'=>0
                ]);
                return response()->json(['error' => 'Payment record not found'], 404);
            }
            SellerPaymentHistory::where('payment_order_id', $attributes['razorpay_order_id'])->update([
                'txn_id' => $attributes['razorpay_payment_id']??null,
                'status' => 'failed',
            ]);
            Subscription::where('payment_order_id', $attributes['razorpay_order_id'])->update([
                'payment_status'=>0
            ]);
            session()->flash('error', 'Payment verification failed');
            return response()->json(['error' => 'Payment verification failed'], 400);
        } catch (\Exception $e) {
            SellerPaymentHistory::where('payment_order_id', $attributes['razorpay_order_id'])->update([
                'txn_id' => $attributes['razorpay_payment_id']??null,
                'status' => 'failed',
            ]);
            Subscription::where('payment_order_id', $attributes['razorpay_order_id'])->update([
                'payment_status'=>0
            ]);
            session()->flash('error', $e->getMessage());
            Log::error('Payment callback failed: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
