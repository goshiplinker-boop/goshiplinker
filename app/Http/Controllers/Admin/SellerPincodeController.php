<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\SellerPincodeImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Courier;
use App\Models\SellerPincode;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
class SellerPincodeController extends Controller
{
    public function importForm()
    {
        $company_id = session('company_id');
        $pincodes = SellerPincode::select(
            'courier_id',
            DB::raw('COUNT(*) as total_pincodes')
        )
       ->where('company_id', $company_id)
        ->groupBy('courier_id')
        ->with('courier')
        ->get();
       // return $pincodes;
        $couriers =  DB::table('couriers as c')
            ->join('couriers as p', 'p.id', '=', 'c.parent_id')
            ->where('c.company_id', $company_id)
            ->where('c.status', 1)
            ->select('p.id', 'p.name')
            ->distinct()
            ->get();
        $sellers = Company::with('user')->where('parent_id', $company_id)->get();
        return view('admin.seller_pincodes.import', compact('couriers', 'pincodes', 'sellers'));
    }
    public function import(Request $request)
    {
        $request->validate([
            'file'       => 'required|file|mimes:csv,xlsx',
            'courier_id' => 'required|integer',
            'seller_company_id' => 'required|integer',
        ]);

        Excel::import(
            new SellerPincodeImport(
                $request->seller_company_id,
                $request->courier_id
            ),
            $request->file('file')
        );

        return back()->with('success', 'Pincodes imported successfully');
    }
}
