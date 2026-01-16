<?php

namespace App\Http\Controllers\Seller\Tracking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManageTrackingPage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrackShipmentController extends Controller
{
    // Display the tracking page
    public function index(Request $request, string $website_domain)
    {
        $manageTrackingPage = ManageTrackingPage::where('website_domain', $website_domain)->first();
        $path = $request->path();
        $requestpath=$website_domain.'/tracking_widget';
        if($path==$requestpath){
            $view_file = 'seller.tracking.widget_index';
        }else{
            $view_file='seller.tracking.index';
        }
        if (is_null($manageTrackingPage) || $manageTrackingPage->status == 0) {
            return response()->view('errors.500', ['error' => 'Tracking page not found'], 500);
        }

        $jsonData = json_decode($manageTrackingPage->json_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->view('errors.500', ['error' => 'Invalid JSON data'], 500);
        }

        return view($view_file, [
            'manageTrackingPage' => $manageTrackingPage,
            'jsonData' => $jsonData
        ]);
    }

    // Handle shipment tracking requests
    public function trackShipment(Request $request, $website_domain)
    {
        $trackingType = $request->input('tracking_type');
        $trackingValue = $request->input('tracking_value');
        $company_id = $request->input('cid');
        $path = $request->path();
        $requestpath='api/'.$website_domain.'/trackingWidgetShipment';
        if($path==$requestpath){
            $redirect = 'widget_track_details';
            $route ='tracking_widget';
        }else{
            $redirect='track_details';
            $route ='track';
        }
        $results = DB::table('orders as o')
            ->leftJoin('shipment_info as si', 'si.order_id', '=', 'o.id')
            ->select('o.*', 'si.tracking_id', 'si.courier_id')
            ->where('o.company_id', $company_id);

        if ($trackingType === 'order_number') {
            $results->where('o.vendor_order_number', $trackingValue);
        } else {
            $results->where('si.tracking_id', $trackingValue);
        }

        $results = $results->first();

        if (!empty($results)) {
            return redirect()->route($redirect, [
                'website_domain' => $website_domain,
                'tracking_number' => $results->tracking_id ?? $results->vendor_order_number
            ]);
        } else {
            return redirect()
                ->route($route, $website_domain)
                ->with('error', 'Invalid ' . $trackingType)
                ->withInput();
        }
    }

    // Display shipment tracking details
    public function trackingDetails(Request $request, string $website_domain, string $tracking_number)
    {
        $path = $request->path();
        $requestpath=$website_domain."/tracking_widget/".$tracking_number;
        if($path==$requestpath){
            $route = 'tracking_widget';
            $view_file ='seller.tracking.widget_tracking_details';
        }else{
            $route ='track';
            $view_file='seller.tracking.tracking_details';
        }
        $manageTrackingPage = ManageTrackingPage::where('website_domain', $website_domain)->first();

        if (is_null($manageTrackingPage) || $manageTrackingPage->status == 0) {
            return response()->view('errors.500', ['error' => 'Tracking page not found'], 500);
        }

        $company_id = $manageTrackingPage->company_id;

        $results = DB::table('orders as o')
        ->join('order_statuses as os', 'os.status_code', '=', 'o.status_code')
        ->leftJoin('shipment_info as si', 'si.order_id', '=', 'o.id')
        ->leftJoin('courier_settings as cs', 'cs.courier_id', '=', 'si.courier_id')
        ->leftJoin('tracking_history as trh', 'trh.order_id', '=', 'si.order_id')
        ->leftJoin('shipment_statuses as ss', 'ss.code', '=', 'si.current_status')
        ->leftJoin('order_packages as op', 'op.order_id', '=', 'o.id') // 🟢 join packages
        ->select(
            'o.*',
            'si.tracking_id',
            'si.courier_id',
            'si.current_status',
            'si.edd',
            'cs.courier_title',
            'os.status_name',
            'trh.current_shipment_status',
            'trh.current_shipment_status_code',
            'trh.current_shipment_status_date',
            'trh.current_shipment_location',
            'ss.name as shipment_status',
            DB::raw('COUNT(op.id) as package_count') // 🟢 count total packages
        );

        // 🟢 Filter by company (if not admin)
        if ($company_id != 1) {
            $results = $results->where('o.company_id', $company_id);
        }

        // 🟢 Filter by tracking ID or vendor order number
        $results = $results
            ->where(function ($query) use ($tracking_number) {
                $query->where('si.tracking_id', $tracking_number)
                    ->orWhere('o.vendor_order_number', $tracking_number);
            })
        ->groupBy(
            'o.id',
            'si.tracking_id',
            'si.courier_id',
            'si.current_status',
            'cs.courier_title',
            'os.status_name',
            'trh.current_shipment_status',
            'trh.current_shipment_status_code',
            'trh.current_shipment_status_date',
            'trh.current_shipment_location',
            'ss.name'
        ) // 🟢 group by non-aggregated columns
        ->orderBy('trh.current_shipment_status_date', 'desc')
        ->get();

        if ($results->isEmpty()) {
            return redirect()
                ->route($route, $website_domain)
                ->with('error', 'Invalid order number/tracking number')
                ->withInput();
        }

        $scans = [];
        $order = [];

        foreach ($results as $k => $result) {
            $scans_data = [];

            if ($k === 0) {
                $result->channel_order_date = Carbon::parse($result->channel_order_date)->format('d M Y');
                
                if($result->current_status=='DEL'){
                    $result->edd = '';
                }else{
                    $result->edd = (!empty($result->edd) && $result->edd !='0000-00-00 00:00:00')?Carbon::parse($result->edd)->format('d M Y'):'';
                }
                $order = $result;
            }

            if (is_null($result->current_status)) {
                $formattedDate = Carbon::parse($result->channel_order_date)->format('l, d F');
                $scans_data['current_status'] = $result->shipment_status ?? $result->status_name;
                $scans_data['current_location'] = $result->current_shipment_location ?? '';
                $scans_data['current_time'] = 'Order #' . $result->vendor_order_number;
                $scans[$formattedDate][] = $scans_data;
                break;
            }

            $formattedDate = Carbon::parse($result->current_shipment_status_date)->format('l, d F');
            $formattedTime = Carbon::parse($result->current_shipment_status_date)->format('g:ia');

            $scans_data['current_status'] = $result->current_shipment_status ?? $result->status_name;
            $scans_data['current_location'] = $result->current_shipment_location ?? '';
            $scans_data['current_time'] = $formattedTime;
            $scans[$formattedDate][] = $scans_data;
        }

        $jsonData = json_decode($manageTrackingPage->json_data, true);

        return view($view_file, [
            'manageTrackingPage' => $manageTrackingPage,
            'jsonData' => $jsonData,
            'scans' => $scans,
            'order' => $order
        ]);
    }
}