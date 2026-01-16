<?php

namespace App\Http\Controllers\Admin;
use App\Imports\CourierRateCardImport;
use App\Models\Courier;
use App\Models\CourierRateCard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class CourierRateCardController extends Controller
{
    public function index(Request $request)
    {
        $companyId = session('company_id');

        $rateCards = CourierRateCard::with('courier')
            ->where('company_id', $companyId)
            ->orderBy('courier_id')
            ->orderBy('zone_name')
            ->orderBy('weight_slab_kg')
            ->paginate(default_pagination_limit());

        return view('admin.courier_rate_cards.index', compact('rateCards'));
    }

    public function create()
    {
        $companyId = session('company_id');
        $couriers = Courier::where('company_id',$companyId)->where('status',1)->get();
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
        return view('admin.courier_rate_cards.create', compact('couriers','slas','weight_slabs'));
    }

    public function store(Request $request)
    {
        $companyId = session('company_id');
        $validated = $request->validate([
            'courier_id' => [
                'required',
                Rule::exists('couriers', 'id')->where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                }),
            ],
            'zone_name'            => ['required', 'in:A,B,C,D,E,F'],
            'weight_slab_kg' => [
                'required',
                'numeric',
                Rule::in([0.1, 0.25, 0.5, 1, 2, 3, 5, 10]),
                Rule::unique('courier_rate_card')->where(fn ($q) =>
                    $q->where('company_id', $companyId)
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
        $validated['company_id'] = $companyId;
        CourierRateCard::create($validated);

        return redirect()
            ->route('manage_rate_card.index')
            ->with('success', 'Rate card created successfully.');
    }

    public function edit(CourierRateCard $manage_rate_card)
    {
        $courierRateCard = $manage_rate_card;
        $companyId = session('company_id');
        $couriers = Courier::where('company_id',$companyId)->where('status',1)->get();
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
        return view('admin.courier_rate_cards.edit', compact('courierRateCard', 'couriers','slas','weight_slabs'));
    }

    public function update(Request $request, CourierRateCard $manage_rate_card)
    {   
        $courierRateCard = $manage_rate_card;

        $companyId = session('company_id');

        $validated = $request->validate([
            'courier_id' => [
                'required',
                Rule::exists('couriers', 'id')->where(fn($q) => $q->where('company_id', $companyId)),
            ],
            'zone_name'            => ['required', 'in:A,B,C,D,E,F'],
            'weight_slab_kg' => [
                'required',
                'numeric',
                Rule::in([0.1, 0.25, 0.5, 1, 2, 3, 5, 10]),
                Rule::unique('courier_rate_card')->where(fn ($q) =>
                    $q->where('company_id', $companyId)
                    ->where('courier_id', request('courier_id'))
                    ->where('zone_name', request('zone_name'))
                )
                ->ignore($courierRateCard->id),
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

        $validated['company_id'] = $companyId;

        $updated = $courierRateCard->update($validated);

        if (!$updated) {
            return back()
                    ->withErrors(['error' => "Update failed. Please check the data."])
                    ->withInput();
        }

        return redirect()
            ->route('manage_rate_card.index')
            ->with('success', 'Rate card updated successfully.');
    }


    public function destroy(CourierRateCard $manage_rate_card)
    {
        $manage_rate_card->delete();

        return redirect()
            ->route('manage_rate_card.index')
            ->with('success', 'Rate card deleted successfully.');
    }

    // -------- Excel import ----------

    public function showImportForm()
    {
        $companyId = session('company_id');
        $couriers = Courier::where('company_id',$companyId)->where('status',1)->get();
        return view('admin.courier_rate_cards.import', compact('couriers'));
    }

    public function import(Request $request)
    {
        $companyId = session('company_id');
        $validated = $request->validate([
            'file'       => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new CourierRateCardImport($companyId);
        Excel::import($import, $validated['file']);
        // If there are validation failures
        if ($import->failures()->isNotEmpty()) {

            return redirect()
                ->back()
                ->with('import_errors', $import->failures())
                ->with('warning', 'Some rows were skipped due to validation errors.');
        }

        return redirect()
            ->route('manage_rate_card.index')
            ->with('success', 'Rate card imported successfully.');
    }
}
