<?php

namespace App\Http\Controllers\Seller\SellerPayment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\company;
use App\Models\Subscription;
use Barryvdh\DomPDF\Facade\Pdf;
class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id; 
        $subscriptions = Subscription::with('plan')
            ->where("company_id", $companyId)
            ->latest('id')
            ->paginate(default_pagination_limit());
        //return $subscriptions;

        if ($request->wantsJson() || $request->is("api/*")) {
            return response()->json([
                "success" => true,
                "data" => $subscriptions
            ]);
        }

        return view("seller.seller_payments.index", compact("subscriptions"));
    }

    public function generate($subscriptionId)
    {
        // Fetch subscription with related plan and company
        $companyId = session('company_id');
        $subscription = Subscription::with(['plan', 'company'])
        ->where('id', $subscriptionId)
        ->where('company_id', $companyId)
        ->firstOrFail();
        if ($subscription->company_id != $companyId) {
            abort(403, 'Unauthorized access to this invoice');
        }
       // return $subscription; 

        // Prepare data for the invoice
        $data = [
            'invoice_number' => 'INV-' . str_pad($subscription->id, 6, '0', STR_PAD_LEFT),
            'subscription'   => $subscription,
        ];
        $pdf = Pdf::loadView('seller.seller_payments.invoice', $data);

        // $html = view('seller.seller_payments.invoice', $data)->render();
        // return response($html); 

        // Option 2: Stream to browser
         return $pdf->stream('invoice-' . $data['invoice_number'] . '.pdf');

        // Option 3: Download directly
         return $pdf->download('invoice-' . $data['invoice_number'] . '.pdf');
    }
    
}