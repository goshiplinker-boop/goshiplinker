<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerWalletLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
class WalletController extends Controller
{
    public function index(Request $request){
    
        $companyId = session('company_id');
        $query = SellerWalletLedger::where('company_id', $companyId);

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        $transactions = $query
            ->orderBy('id', 'desc')
            ->paginate(default_pagination_limit())
            ->withQueryString();
        return view('seller.wallet.index', compact('transactions'));
    }
}
