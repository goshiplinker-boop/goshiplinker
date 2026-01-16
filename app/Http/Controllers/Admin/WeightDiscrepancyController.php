<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourierWeightUpload;
use Illuminate\Http\Request;
use App\Jobs\ProcessCourierWeightSheetJob;
use App\Models\Courier;
use App\Models\WeightDiscrepancy;
use Illuminate\Support\Facades\DB;

class WeightDiscrepancyController extends Controller
{
    public function index(Request $request)
    {
        $query = WeightDiscrepancy::with([
            'order',
            'shipment.courier',
            'shipment.order.orderProducts',
        ])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $discrepancies = $query->paginate(20);

        
        $statusColors = [
            'new' => 'warning',
            'auto_accepted' => 'success',
            'accepted' => 'primary',
            'dispute_raised' => 'info',
            'dispute_accepted' => 'success',
            'dispute_rejected' => 'danger',
            'credited' => 'success',
            'debited' => 'danger',
            'closed' => 'secondary',
        ];
        return view('admin.weight_discrepancies.index', compact('discrepancies', 'statusColors'));
    }
    public function showUploadForm()
    {
        $companyId = session('company_id');
        $couriers = DB::table('couriers as c')
            ->join('couriers as p', 'p.id', '=', 'c.parent_id')
            ->where('c.company_id', $companyId)
            ->where('c.status', 1)
            ->selectRaw('DISTINCT p.id as id, p.name')
            ->get();

        return view('admin.weight_discrepancies.upload', compact('couriers'));
    }


    public function uploadCourierSheet(Request $request)
    {
        $request->validate([
            'courier_id' => 'required|exists:couriers,id',
            'file'       => 'required|file|mimes:csv,xlsx',
        ]);

        // ✅ Store file
        $path = $request->file('file')->store('courier-weight-sheets');

        // ✅ Save upload record
        $upload = CourierWeightUpload::create([
            'courier_id' => $request->courier_id,
            'file_path'  => $path,
            'status'     => 'pending',
        ]);

        // ✅ Dispatch background job
        ProcessCourierWeightSheetJob::dispatch($upload->id);

        return redirect()
            ->route('admin.weight-discrepancies.upload-form')
            ->with('success', 'Courier weight sheet uploaded and processing started.');
    }
}
