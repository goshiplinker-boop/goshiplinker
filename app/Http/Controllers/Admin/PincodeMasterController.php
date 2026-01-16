<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PincodeMaster;
use App\Models\CourierSetting;
use Illuminate\Support\Facades\DB;
class PincodeMasterController extends Controller
{
    public function index(){
        $company_id = session('company_id');
        $pincodes = PincodeMaster::select(
            'courier_id',
            DB::raw('COUNT(*) as total_pincodes')
        )
        ->where('company_id', $company_id)
        ->groupBy('courier_id')
        ->with('courier:id,name')
        ->get();
        $couriers = DB::table('couriers as c')
            ->join('couriers as p', 'p.id', '=', 'c.parent_id')
            ->where('c.company_id', $company_id)
            ->where('c.status', 1)
            ->selectRaw('DISTINCT p.id as id, p.name')
            ->get();
        // $couriers = CourierSetting::where('status', 1)
        //     ->where('company_id', $company_id)
        //     ->get();
        return view('admin.pincode_master.import', [
            'pincodes' => $pincodes,
            'couriers' => $couriers
        ]);
    }
    public function import(Request $request)
    {
        $request->validate([
            'courier_id' => 'required|exists:couriers,id',
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $companyId = session('company_id');
        $courierId = $request->courier_id;

        $file = fopen($request->file('file')->getRealPath(), 'r');

        $header = fgetcsv($file);

        if (!$header) {
            return back()
                ->withErrors(['file' => 'CSV file is empty.'])
                ->withInput();
        }

        $requiredHeaders = [
            'PINCODES',
            'CITY',
            'STATE',
            'ROUTE_CODE',
            'FORWARD_PICKUP',
            'FORWARD_DELIVERY',
            'REVERSE_PICKUP',
            'COD',
            'PREPAID',
            'STATUS',
        ];

        $normalizedHeader = array_map(fn ($h) => strtoupper(trim($h)), $header);

        foreach ($requiredHeaders as $req) {
            if (!in_array($req, $normalizedHeader)) {
                return back()
                    ->withErrors(['file' => "Missing required header: {$req}"])
                    ->withInput();
            }
        }

        $indexMap = [];
        foreach ($requiredHeaders as $req) {
            $indexMap[$req] = array_search($req, $normalizedHeader);
        }

        while (($row = fgetcsv($file)) !== false) {

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            PincodeMaster::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'courier_id' => $courierId,
                    'pincode'    => $row[$indexMap['PINCODES']] ?? null,
                ],
                [
                    'city'             => $row[$indexMap['CITY']] ?? null,
                    'state'            => $row[$indexMap['STATE']] ?? null,
                    'route_code'       => $row[$indexMap['ROUTE_CODE']] ?? null,
                    'forward_pickup'   => $row[$indexMap['FORWARD_PICKUP']] ?? 0,
                    'forward_delivery' => $row[$indexMap['FORWARD_DELIVERY']] ?? 0,
                    'reverse_pickup'   => $row[$indexMap['REVERSE_PICKUP']] ?? 0,
                    'cod'              => $row[$indexMap['COD']] ?? 0,
                    'prepaid'          => $row[$indexMap['PREPAID']] ?? 0,
                    'status'           => $row[$indexMap['STATUS']] ?? 0,
                ]
            );
        }

        fclose($file);

        return back()->with('success', 'Pincode CSV imported successfully!');
    }

   public function masterPincodeExport(Request $request)
    {
        $courierId = $request->courier_id;
        $companyId = session("company_id");
        $role_id = session("role_id");
        if($role_id==2){
            $companyId = session("parent_company_id");
        }

        $pincodes = PincodeMaster::where("courier_id", $courierId)
            ->where("company_id", $companyId)
            ->get([
                "pincode",
                "city",
                "state",
                "route_code",
                "forward_pickup",
                "forward_delivery",
                "reverse_pickup",
                "cod",
                "prepaid",
                "status",
            ]);

        if ($pincodes->isEmpty()) {
            return response()->json([
                'error' => 'No Pincode numbers available for export.'
            ], 404);
        }

        $filename = "pincode_numbers_{$courierId}.csv";

        $headers = [
            "PINCODES",
            "CITY",
            "STATE",
            "ROUTE_CODE",
            "FORWARD_PICKUP",
            "FORWARD_DELIVERY",
            "REVERSE_PICKUP",
            "COD",
            "PREPAID",
            "STATUS"
        ];

        return response()->streamDownload(function () use ($headers, $pincodes) {
            $output = fopen('php://output', 'w');
            fputcsv($output, $headers);

            foreach ($pincodes as $row) {
                fputcsv($output, $row->toArray());
            }

            fclose($output);

        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }


}
