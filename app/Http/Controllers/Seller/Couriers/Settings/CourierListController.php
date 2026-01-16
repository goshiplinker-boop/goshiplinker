<?php

namespace App\Http\Controllers\Seller\Couriers\Settings;
use App\Models\ImportTrackingNumber;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourierSetting;
use App\Models\Courier;
use App\Models\Company;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ImportPincodeNumber;
class CourierListController extends Controller
{
    public function courierList()
    {
        $defaultCouriers = Courier::where(["status" => true, "company_id" => 0])
            ->whereNull("parent_id")
            ->get();
        // Retrieve the company ID from the session
        $companyId = session("company_id");
        $company_parent_id = session("parent_company_id");
        $company_role_id = session("role_id");
        $courier_company_id = ($company_role_id==1)?$companyId:$company_parent_id;
        // Fetch carriers associated with the company ID
        $companyCouriers = Courier::join(
            "courier_settings",
            "couriers.id",
            "=",
            "courier_settings.courier_id"
        )
            ->where("courier_settings.company_id", $courier_company_id)
            ->select(
                "couriers.id",
                "couriers.image_url",
                "courier_settings.courier_title",
                "courier_settings.courier_code",
                "courier_settings.status",
                "courier_settings.env_type"
            )
            ->get();

        return view("seller.couriers.settings.courier_list", [
            "defaultCouriers" => $defaultCouriers,
            "companyCouriers" => $companyCouriers,
        ]);
    }
   public function adminCourierList(Request $request)
    {
        // Retrieve the company IDs from the session
        $companyId        = session('company_id');  // admin / parent company
        $seller_companyId = $request->seller_company_id ?? 0; // selected seller company

        // Base query: Courier + CourierSetting
        $query = Courier::join(
                'courier_settings',
                'couriers.id',
                '=',
                'courier_settings.courier_id'
        );
        
        $query->leftJoin('sellers_couriers as sc', function ($join) use ($seller_companyId) {
            $join->on('sc.courier_id', '=', 'courier_settings.courier_id')
                ->where('sc.company_id', '=', $seller_companyId);
        });
        $query->where('courier_settings.status', 1);
        $companyCouriers = $query->where('courier_settings.company_id', $companyId)
            ->select(
                'couriers.id',
                'couriers.image_url',
                'courier_settings.courier_title',
                'courier_settings.courier_code',
                'courier_settings.status',
                'courier_settings.env_type',
                'sc.seller_courier_status',
                'sc.main_courier_status'
            )
            ->get(); // <-- FINAL get()
        // Fetch all sellers
        $sellers = Company::with('user')
            ->where('parent_id', $companyId)
            ->get();
        return view('seller.couriers.seller_courier_list', [
            'companyCouriers' => $companyCouriers,
            'sellers'         => $sellers,
        ]);
    }

    public function updateAdminCourierList(Request $request)
    {
        $data = $request->validate([
            'seller_company_id' => 'required|integer',
            'seller_couriers'   => 'nullable|array',
            'seller_couriers.*' => 'in:0,1',
        ]);

        $sellerId = $data['seller_company_id'];
        $couriers = $request->input('seller_couriers', []);

        foreach ($couriers as $courierId => $value) {

            $exists = DB::table('sellers_couriers')
                ->where('company_id', $sellerId)
                ->where('courier_id', $courierId)
                ->exists();

            if ($exists) {
                // ✅ UPDATE only
                DB::table('sellers_couriers')
                    ->where('company_id', $sellerId)
                    ->where('courier_id', $courierId)
                    ->update([
                        'main_courier_status' => (int) $value,
                        'updated_at'          => now(),
                    ]);
            } else {
                // ✅ INSERT with seller_courier_status = 1
                DB::table('sellers_couriers')->insert([
                    'company_id'           => $sellerId,
                    'courier_id'           => $courierId,
                    'main_courier_status'  => (int) $value,
                    'seller_courier_status'=> 1,   // 🔥 FORCE ACTIVE ON INSERT
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
            }
        }

        return back()->with('success', 'Seller courier list updated successfully');
    }
   public function sellerCourierList()
    {
        $companyId = session("company_id");
        $parent_company_id = session("parent_company_id");

        $companyCouriers = DB::table('sellers_couriers as sc')
            ->join('couriers as c', function ($join) use ($parent_company_id) {
                $join->on('c.id', '=', 'sc.courier_id')
                    ->where('c.status', 1)
                    ->where('c.company_id', $parent_company_id);
            })
            ->join('courier_settings as cs', function ($join) {
                $join->on('cs.courier_id', '=', 'c.id')->where('cs.status', 1);
            })
            ->where('sc.main_courier_status', 1)
            ->where('sc.company_id', $companyId)
            ->select(
                'c.*',
                'cs.courier_title',
                'cs.courier_code',
                'cs.env_type',
                'sc.seller_courier_status'
            )
            ->get();

        return view("seller.couriers.seller_courier_list", [
            "companyCouriers" => $companyCouriers,
        ]);
    }


    public function updateSellerCourierList(Request $request){
       // return $request->all();
        $data = $request->validate([
            'seller_couriers' => 'nullable|array', // keys are courier ids
            'seller_couriers.*' => 'in:0,1',
        ]);
        $couriers = $request->input('seller_couriers', []); // e.g. ['3' => '1', '4' => '0']
        $sellerId = session('company_id');
        // Example: loop and update DB (adjust to your schema)
        foreach ($couriers as $courierId => $value) {

            \DB::table('sellers_couriers')->updateOrInsert(
                [
                    'courier_id' => $courierId,
                    'company_id' => $sellerId,
                ],
                [
                    'seller_courier_status' => (int)$value,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        return redirect()->back()->with('success', 'couriers have been updated.');

    }
    public function uploadAWB(Request $request)
    {
        $companyId = session("company_id");
        $manualCourierCodes1 = config('app.manual_tracking_number',[]);  
        $manualCourierCodes2 = config('app.auto_tracking_number_fetch',[]);  
        $manualCourierCodes = array_merge($manualCourierCodes1,$manualCourierCodes2);       
        $couriers = Courier::join(
                'courier_settings',
                'couriers.id',
                '=',
                'courier_settings.courier_id'
            )
            ->leftJoin('couriers as parent', 'parent.id', '=', 'couriers.parent_id')
            ->where('courier_settings.company_id', $companyId)
            ->where('courier_settings.status', 1)
            ->whereIn('courier_settings.courier_code', $manualCourierCodes)
            ->groupBy('couriers.parent_id')
            ->selectRaw('
                couriers.parent_id as id,
                MAX(parent.name) as name,
                MAX(parent.image_url) as image_url,
                courier_settings.status
            ')
            ->get();
            // Fetch all counts grouped by courier_id and payment_type
            $trackingData = ImportTrackingNumber::select('courier_id', 'payment_type', DB::raw('COUNT(id) as total'))
                ->where('company_id', $companyId)
                ->where('used', 0)
                ->groupBy('courier_id', 'payment_type')
                ->get();
            // Organize the data for easy lookup
            $trackingCounts = [];
            foreach ($trackingData as $item) {
                $courierId = $item->courier_id;
                $paymentType = $item->payment_type;
                $total = $item->total;

                if (!isset($trackingCounts[$courierId])) {
                    $trackingCounts[$courierId] = [
                        'total' => 0,
                        'prepaid' => 0,
                        'cod' => 0,
                    ];
                }

                // Add to total count
                $trackingCounts[$courierId]['total'] += $total;

                // Add to payment type specific counts
                if ($paymentType === 'P') {
                    $trackingCounts[$courierId]['prepaid'] = $total;
                } elseif ($paymentType === 'C') {
                    $trackingCounts[$courierId]['cod'] = $total;
                }
            }

            // Attach counts to each courier
            foreach ($couriers as $courier) {
                $courierId = $courier->id;
                $counts = $trackingCounts[$courierId] ?? ['total' => 0, 'prepaid' => 0, 'cod' => 0];

                $courier->tracking_number_count = $counts['total'];
                $courier->tracking_number_count_prepaid = $counts['prepaid'];
                $courier->tracking_number_count_cod = $counts['cod'];
            }
        $courierId = $request->courier_id;
        $file = $request->file("csv_file");

        if ($file == null || !$file->isValid()) {
            return view("seller.couriers.settings.uploadAWB", compact("couriers"));
        }

        try {
            $path = $file->getRealPath();
            $data = array_map("str_getcsv", file($path));

            if (empty($data)) {
                session()->flash("error", "The CSV file is empty.");
                return;
            }

            $header = array_map('strtolower', array_map('trim', $data[0]));
            unset($data[0]);

            $trackingNumberKey = null;
            foreach ($header as $key => $columnName) {
                if (in_array($columnName, ["tracking number", "TRACKING NUMBER"])) {
                    $trackingNumberKey = $key;
                    break;
                }
            }

            if ($trackingNumberKey === null) {
                session()->flash("error", "Tracking number column is missing in the file.");
                return;
            }

            // Step 1: Prepare new tracking numbers
            $newRecords = [];
            foreach ($data as $row) {
                if (isset($row[$trackingNumberKey]) && !empty(trim($row[$trackingNumberKey]))) {
                    $newRecords[] = [
                        "courier_id" => $courierId,
                        "company_id" => $companyId,
                        "tracking_number" => trim($row[$trackingNumberKey]),
                        "used" => 0,
                        "created_at" => now(),
                        "updated_at" => now(),
                    ];
                }
            }

            if (empty($newRecords)) {
                session()->flash("error", "No valid tracking numbers found in the file.");
                return;
            }

            // Step 2: Use transaction for deletion & insertion
            DB::beginTransaction();

            try {
                // Delete all existing tracking numbers for this courier & company
                ImportTrackingNumber::where("courier_id", $courierId)
                    ->where("company_id", $companyId)
                    ->delete();

                // Insert new tracking numbers in chunks
                $chunkSize = 500;
                foreach (array_chunk($newRecords, $chunkSize) as $chunk) {
                    ImportTrackingNumber::insert($chunk);
                }

                // Commit transaction
                DB::commit();

                session()->flash("success", "Tracking numbers uploaded successfully.");
                return;
            } catch (\Exception $e) {
                // Rollback transaction if any error occurs
                DB::rollBack();
                session()->flash("error", "An error occurred while processing the file. " . $e->getMessage());
                return;
            }
        } catch (\Exception $e) {
            session()->flash("error", "An error occurred while reading the file. " . $e->getMessage());
            return;
        }
    }


    public function exportTrackingNumbers(Request $request)
    {
        $courierId = $request->courier_id;
        $companyId = session("company_id");
        $paymentType = $request->payment_type;
        // Fetch tracking numbers
        $trackingNumbers = ImportTrackingNumber::where("courier_id", $courierId)
            ->where("company_id", $companyId)
            ->where("used", 0)
            ->when($paymentType, function ($query, $paymentType) {
                return $query->where("payment_type", $paymentType);
            })
            ->get(["tracking_number"]);

        if ($trackingNumbers->isEmpty()) {
            return redirect()
                ->back()
                ->with("error", "No tracking numbers available for export.");
        }

        $headers = ["TRACKING NUMBER"];
        $data = $trackingNumbers->toArray();
        $filename = "tracking_numbers_{$courierId}.csv";

        // Generate and download CSV
        $handle = fopen("php://output", "w");
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename={$filename}");
        fputcsv($handle, $headers);

        foreach ($data as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
        exit();
    }

    public function deleteTrackingNumbers(Request $request)
    {
        $courierId = $request->courier_id;
        $paymentType = $request->payment_type;
        $companyId = session("company_id");

        try {
            ImportTrackingNumber::where("courier_id", $courierId)
                ->where("company_id", $companyId)
                ->when($paymentType, function ($query, $paymentType) {
                    return $query->where("payment_type", $paymentType);
                })
                ->delete();

            session()->flash("success", "Tracking Numbers deleted successfully.");
        } catch (\Exception $e) {
            session()->flash("error", "An error occurred while deleting the courier.");
        }

    }
    public function apiGetCouriers(Request $request)
{
    try {
        $user = Auth::user();
        $companyId = $user?->company_id;

        if (empty($companyId)) {
            return response()->json([
                "success" => false,
                "errors" => ['company_id' => 'Company ID not found for this user.'],
                "message" => "Invalid request details.",
            ], 400);
        }


        $courierId = $request->courier_id ?? null;
        $courier_code = $request->courier_code ?? '';

        $query = Courier::join(
            "courier_settings",
            "couriers.id",
            "=",
            "courier_settings.courier_id"
        )
        ->where("courier_settings.company_id", $companyId);

        if ($courier_code) {
            $query->where('courier_settings.courier_code', 'like', "%{$courier_code}%");
        }

        if (!empty($courierId)) {
            $query->where("courier_settings.courier_id", $courierId);
        }

        $companyCouriers = $query->select(
            "couriers.id",
            "couriers.image_url",
            "courier_settings.courier_title",
            "courier_settings.courier_code",
            "courier_settings.status"
        )->get();

        if ($companyCouriers->isEmpty()) {
            return response()->json([
                "success" => false,
                "message" => "No couriers found for this company."
            ], 404);
        }

        return response()->json([
            "success" => true,
            "result" => $companyCouriers,
            "message" => $courierId ? "Courier details found successfully." : "All integrated couriers details found successfully.",
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            "success" => false,
            "message" => "An unexpected error occurred",
            "error" => $e->getMessage(), 
        ], 500);
    }
}
    public function fetchTrackingNumbers(Request $request){
        $courierId = $request->courier_id;
        $paymentType = $request->payment_type;
        $companyId = session("company_id");
        // Validate incoming data
        $validatedData = $request->validate([
            'courier_id' => 'required',
            'payment_type' => 'required|string|in:C,P',
        ]);
        try {
            $courier_settings = CourierSetting::where(['courier_id'=>$courierId,'company_id'=>$companyId])->firstOrFail();
            $class = '\App\Http\Controllers\Seller\Couriers\Settings\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $courier_settings->courier_code))) . 'Controller';
            $controller = app()->make($class);
            switch($courier_settings->courier_code) {             
                case 'xpressbees_postpaid':
                    $server_output = $controller->fetch_awb($request,$courier_settings);
                    $couriertitle = $courier_settings->courier_title;
                    break;
                default:
                    $server_output = '';
                    $couriertitle = '';
            }
            if(isset($server_output['error'])){
                session()->flash('error', $server_output['error']??'Error while retrieving AWB numbers');
                return false;

            }else{
                $awbNumbers = $server_output['awb']??[];
                session()->flash('success', 'Tracking no fetch successfully');
                $sql ='';
                foreach (array_chunk($awbNumbers, 500) as $chunk) {
                    $dataToInsert = [];
                    foreach ($chunk as $awb) {
                        $dataToInsert[] = [
                            'company_id'   => $companyId,
                            'courier_id'   => $courierId,
                            'payment_type' => $request->payment_type,
                            'tracking_number'     => $awb,
                            'used'         => 0,
                            'created_at'  => date('Y-m-d H:i:s'),
                            'updated_at'=> date('Y-m-d H:i:s'),
                        ];
                    }
                    ImportTrackingNumber::insert($dataToInsert);
                }

            }
            return true;
        }catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'Error while retrieving AWB numbers');
            return false;
        }

    }
    public function pincodeList()
    {
        $companyId = session("company_id");

        if (!$companyId) {
            return redirect()
                ->route("loginForm")
                ->with("error", "Session time is out.");
        }
        $manual_pincode_numbers = config('app.manual_pincode_numbers',[]);  
        $couriers = CourierSetting::where('status', 1)
            ->where('company_id', $companyId)
            ->whereIn('courier_code',$manual_pincode_numbers)
            ->get();
        $zipcodes = CourierSetting::select('courier_settings.*')
            ->selectSub(function ($query) use ($companyId) {
                $query->from('import_pincode_numbers')
                    ->selectRaw('count(*)')
                    ->whereColumn('courier_settings.courier_id', 'import_pincode_numbers.courier_id')
                    ->where('payment_type', 'C')
                    ->where('company_id', $companyId);
            }, 'cod_count')
            ->selectSub(function ($query) use ($companyId) {
                $query->from('import_pincode_numbers')
                    ->selectRaw('count(*)')
                    ->whereColumn('courier_settings.courier_id', 'import_pincode_numbers.courier_id')
                    ->where('payment_type', 'P')
                    ->where('company_id', $companyId);
            }, 'prepaid_count')  
            ->where('status', 1)    
            ->where('company_id', $companyId)      
            ->whereIn('courier_code',$manual_pincode_numbers)
            ->paginate(10);
        return view("seller.couriers.zipcodes.import", [
            "couriers" => $couriers,
            "zipcodes" => $zipcodes,
        ]);
    }

    public function importPincodes(Request $request)
    {
        // Validate incoming data
        $validatedData = $request->validate([
            'courier_id' => 'required|exists:couriers,id',
            'payment_type' => 'required|string|in:C,P',
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $courierId = $validatedData['courier_id'];
        $paymentType = $validatedData['payment_type'];
        $companyId = $request->session()->get('company_id');
        $file = $request->file('csv_file');

        try {
            $path = $file->getRealPath();
            $data = array_map('str_getcsv', file($path));

            if (empty($data)) {
                return redirect()->route('pincode_list')->withErrors('The CSV file is empty.');
            }

            // Process header
            $header = array_map('strtolower', array_map('trim', $data[0]));
            unset($data[0]);

            $fileheader = null;
            foreach ($header as $key => $columnName) {
                if ($columnName == 'pincodes') {
                    $fileheader = $key;
                    break;
                }
            }

            if ($fileheader === null) {
                return redirect()->route('pincode_list')->withErrors('Pincodes column is missing in the file.');
            }

            // Prepare new records
            $newRecords = [];
            foreach ($data as $row) {
                if (isset($row[$fileheader]) && !empty(trim($row[$fileheader]))) {
                    $newRecords[] = [
                        'courier_id' => $courierId,
                        'company_id' => $companyId,
                        'zipcodes' => trim($row[$fileheader]),
                        'payment_type' => $paymentType,
                    ];
                }
            }

            if (empty($newRecords)) {
                return redirect()->route('pincode_list')->withErrors('No valid pincode numbers found in the file.');
            }

            // Transaction for deletion and insertion
            DB::beginTransaction();

            try {
                ImportPincodeNumber::where('courier_id', $courierId)
                    ->where('company_id', $companyId)
                    ->where('payment_type', $paymentType)
                    ->delete();

                $chunkSize = 500;
                foreach (array_chunk($newRecords, $chunkSize) as $chunk) {
                    ImportPincodeNumber::insert($chunk);
                }

                DB::commit();
                return redirect()->route('pincode_list')->with('success', 'Pincode numbers uploaded successfully.');

            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->route('pincode_list')->withErrors('An error occurred while processing the file. ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            return redirect()->route('pincode_list')->withErrors('An unexpected error occurred. ' . $e->getMessage());
        }
    }
    public function exportZipcodeNumbers(Request $request)
    {
        $courierId = $request->courier_id;
        $companyId = session("company_id");
        $paymentType = $request->payment_type;
        // Fetch tracking numbers
        $zipcodes = ImportPincodeNumber::where("courier_id", $courierId)
            ->where("company_id", $companyId)
            ->when($paymentType, function ($query, $paymentType) {
                return $query->where("payment_type", $paymentType);
            })
            ->get(["zipcodes"]);

        if ($trackingNumbers->isEmpty()) {
            return redirect()
                ->back()
                ->with("error", "No tracking numbers available for export.");
        }

        $headers = ["Pincodes"];
        $data = $trackingNumbers->toArray();
        $filename = "pincode_numbers_{$courierId}.csv";

        // Generate and download CSV
        $handle = fopen("php://output", "w");
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename={$filename}");
        fputcsv($handle, $headers);

        foreach ($data as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
        exit();
    }

}