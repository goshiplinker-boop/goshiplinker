<?php

namespace App\Http\Controllers\Admin;
use App\Imports\SellerRateCardImport;
use App\Models\Courier;
use App\Models\Company;
use App\Models\CourierRateCard;
use App\Models\SellerRateCard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;
class SellerRateCardController extends Controller
{
    public function index(Request $request)
    {
        $role_id = session('role_id');
        $companyId = ($role_id==1)?session('company_id'):session('parent_company_id');  
        $sellers = Company::with('user')->where('parent_id', $companyId)->get();
        $first_seller_id = $sellers->first()->id ?? 0;
        //return $first_seller_id;
        $seller_company_id = ($role_id == 1)?($request->seller_company_id ?? $first_seller_id) : session('company_id');   
        $rateCards = SellerRateCard::with('courier')
            ->whereHas('company', function ($q)use ($companyId) {
                $q->where('parent_id', $companyId);                
            });
        if($seller_company_id>0){
            $rateCards->where('company_id',$seller_company_id);
        }
        $rateCards = $rateCards->orderBy('courier_id')
            ->orderBy('zone_name')
            ->orderBy('weight_slab_kg')
            ->paginate(default_pagination_limit());
        $sellers = Company::with('user')->where('parent_id', $companyId)->get();
        return view('admin.seller_rate_cards.index', compact('rateCards','sellers','seller_company_id'));
    }

    public function create()
    {
        $companyId = session('company_id');
        $couriers = Courier::where('company_id',$companyId)->where('status',1)->get();
        $sellers = Company::with('user')
            ->where('parent_id', $companyId)
            ->get();
        $slas = [
            '0'   => '0 day',
            '0-1' => '0–1 day',
            '1-2' => '1–2 days',
            '2-3' => '2–3 days',
            '3-5' => '3–5 days',
            '5-7' => '5–7 days',
        ];
        $weight_slabs = [
            '0.1'     => '100 g',
            '0.25'   => '250 g',
            '0.5'   => '500 g',
            '1'  => '1 kg',
            '2' => '2 kg',
            '3' => '3 kg',
            '5' => '5 kg',
            '10' => '10 kg',
        ];
        return view('admin.seller_rate_cards.create', compact('couriers','sellers','slas','weight_slabs'));
    }

   
    public function store(Request $request)
    {
        $companyId = session('company_id');
        $seller_company_id = $request->company_id??0;
        $validated = $request->validate([
            'courier_id' => [
                'required',
                Rule::exists('couriers', 'id')->where(fn ($q) =>
                    $q->where('company_id', $companyId)
                ),
            ],   
            'company_id' => ['required', 'numeric'],
            'zone_name'      => ['required', 'in:A,B,C,D,E,F'],
            'weight_slab_kg' => [
                'required',
                'numeric',
                Rule::in([0.1, 0.25, 0.5, 1, 2, 3, 5, 10]),
                Rule::unique('seller_rate_card')->where(fn ($q) =>
                    $q->where('company_id', request('company_id'))
                    ->where('courier_id', request('courier_id'))
                    ->where('zone_name', request('zone_name'))
                ),
            ],
            'base_freight_forward' => ['required', 'numeric'],
            'additional_freight'   => ['required', 'numeric'],
            'rto_freight'          => ['required', 'numeric'],
            'cod_charge'           => ['required', 'numeric'],
            'cod_percentage'       => ['required', 'numeric'],
            'delivery_sla' => [
                'required',
                Rule::in(['0', '0-1', '1-2', '2-3', '3-5', '5-7']),
            ],
            'cod_allowed'          => ['required', 'boolean'],
            'sort_order'           => ['required', 'numeric'],
        ]);


        SellerRateCard::create($validated);

        return redirect()
            ->route('manage_seller_rate_card.index')
            ->with('success', 'Rate card created successfully.');
    }


    public function edit(SellerRateCard $manage_seller_rate_card)
    {
        $companyId = session('company_id');
        $SellerRateCard = $manage_seller_rate_card;
        $couriers = Courier::where('company_id',$companyId)->where('status',1)->get();
        $sellers = Company::with('user')
            ->where('parent_id', $companyId)
            ->get();
        $slas = [
            '0'   => '0 day',
            '0-1' => '0–1 day',
            '1-2' => '1–2 days',
            '2-3' => '2–3 days',
            '3-5' => '3–5 days',
            '5-7' => '5–7 days',
        ];
        $weight_slabs = [
            '0.1'     => '100 g',
            '0.25'   => '250 g',
            '0.5'   => '500 g',
            '1'  => '1 kg',
            '2' => '2 kg',
            '3' => '3 kg',
            '5' => '5 kg',
            '10' => '10 kg',
        ];
        return view('admin.seller_rate_cards.edit', compact('SellerRateCard', 'couriers','sellers','slas','weight_slabs'));
    }

    public function update(Request $request, SellerRateCard $manage_seller_rate_card)
    {
        $SellerRateCard = $manage_seller_rate_card;
        $companyId = session('company_id');
        $validated = $request->validate([
            'courier_id' => [
                    'required',
                    Rule::exists('couriers', 'id')->where(function ($q) use ($companyId) {
                        $q->where('company_id', $companyId);
                    }),
                ],
            'weight_slab_kg' => [
                'required',
                'numeric',
                Rule::in([0.1, 0.25, 0.5, 1, 2, 3, 5, 10]),
                Rule::unique('seller_rate_card')->where(fn ($q) =>
                    $q->where('company_id', request('company_id'))
                    ->where('courier_id', request('courier_id'))
                    ->where('zone_name', request('zone_name'))
                )
                ->ignore($SellerRateCard->id),
            ],
            'zone_name'            => ['required', 'in:A,B,C,D,E,F'],
            'base_freight_forward' => ['required', 'numeric'],
            'additional_freight'   => ['required', 'numeric'],
            'rto_freight'          => ['required', 'numeric'],
            'cod_charge'           => ['required', 'numeric'],
            'cod_percentage'       => ['required', 'numeric'],
            'delivery_sla' => [
                'required',
                Rule::in(['0', '0-1', '1-2', '2-3', '3-5', '5-7']),
            ],
            'cod_allowed'          => ['required', 'boolean'],
            'sort_order'          => ['required', 'numeric'],
        ]);
        $SellerRateCard->update($validated);

        return redirect()
            ->route('manage_seller_rate_card.index')
            ->with('success', 'Rate card updated successfully.');
    }

    public function destroy(SellerRateCard $manage_seller_rate_card)
    {
        $manage_seller_rate_card->delete();

        return redirect()
            ->route('manage_seller_rate_card.index')
            ->with('success', 'Rate card deleted successfully.');
    }

    // -------- Excel import ----------

    public function showImportForm()
    {
        $companyId = session('company_id');
        $sellers = Company::with('user')
            ->where('parent_id', $companyId)
            ->get();
        $couriers = Courier::where('company_id',$companyId)->where('status',1)->get();
        return view('admin.seller_rate_cards.import', compact('couriers','sellers'));
    }

    public function import(Request $request)
    {
        $companyId = session('company_id');
        $validated = $request->validate([
            'seller_company_id' => ['required', 'numeric'],
            'file'       => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new SellerRateCardImport($companyId,$validated['seller_company_id']);
        Excel::import($import, $validated['file']);
        // If there are validation failures
        if ($import->failures()->isNotEmpty()) {

            return redirect()
                ->back()
                ->with('import_errors', $import->failures())
                ->with('warning', 'Some rows were skipped due to validation errors.');
        }

        return redirect()
            ->route('manage_seller_rate_card.index')
            ->with('success', 'Rate card imported successfully.');
    }
}
