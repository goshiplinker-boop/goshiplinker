<?php

namespace App\Http\Controllers\Seller\Channels\ManageOrders;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
use App\Models\Manifest;
use App\Models\CourierSetting;
use App\Models\ShipmentInfo;
use App\Models\PickupLocation;
use App\Models\ManifestOrder;
use App\Http\Controllers\Seller\Channels\ManageOrders\ShopifyOrderSyncController;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ChannelSetting;
class ManifestController extends Controller
{
    public function create(Request $request)
    {
        $company_id = session("company_id");
        $order_ids = $request->order_ids ?? [];
        if (empty($order_ids)) {
            session()->flash(
                "error",
                "Please select orders to create manifest"
            );
            return;
        }
        if ($order_ids && !is_array($order_ids)) {
            $order_ids = [$order_ids];
        }
        $orders = DB::table("shipment_info as si")
            ->join("orders as o", "o.id", "=", "si.order_id")
            ->join("couriers as co", "co.id", "=", "si.courier_id")
            ->leftJoin("channels as ch", "ch.id", "=", "o.channel_id")
            ->leftJoin("manage_tracking_page as mtp", "mtp.company_id", "=", "si.company_id")
            ->whereIn("si.order_id", $order_ids)
            ->where("si.manifest_created", 0)
            ->where("o.status_code", "P")
            ->when($company_id, function ($query) use ($company_id) {
                $query->where("o.company_id", $company_id);
            })
            ->groupBy(
                "o.id"
            )
            ->select([
                "si.order_id",
                "o.vendor_order_id",
                "si.courier_id",
                "si.payment_mode",
                "o.company_id",
                "si.tracking_id",
                "si.pickedup_location_id",
                "o.channel_id",
                "co.name as courier_title",
                "ch.name as channel_name",
                "ch.parent_id",
                "mtp.website_domain"
            ])
            ->get();

        if ($orders->isEmpty()) {
            session()->flash("error", "No orders found for creating manifest.");
            return;
        }
        $shopifyOrders = [];
        $manifestOrders = [];
        $orderIds = [];
        foreach ($orders as $order) {
            if($order->parent_id==2){
                $shopifyOrders[$order->channel_id][$order->courier_id][] = [
                    "order_id" => $order->order_id,
                    "company_id" => $order->company_id,
                    "vendor_order_id" => $order->vendor_order_id,
                    "courier_id" => $order->courier_id,
                    "tracking_number" => $order->tracking_id,
                    "courier" => $order->courier_title,
                    "channel_code" => strtolower($order->channel_name),
                    "website_domain" => $order->website_domain?:'',
                ];
            }
            
            $manifestOrders[$order->company_id][$order->courier_id][
                $order->payment_mode
            ][$order->pickedup_location_id][$order->order_id] = [
                "order_id" => $order->order_id,
                "vendor_order_id" => $order->vendor_order_id,
                "tracking_number" => $order->tracking_id,
                "courier" => $order->courier_title,
                "channel_code" => strtolower($order->channel_name),
                "channel_id" => $order->channel_id,
            ];

            $orderIds[] = $order->order_id;
        }

        $createdManifestIds = [];
        $orderIdsInManifest = [];

        DB::beginTransaction();
        try {
            foreach ($manifestOrders as $companyId => $courierOrders) {
                foreach ($courierOrders as $courierId => $paymentOrders) {
                    foreach ($paymentOrders as $paymentMode => $pickupOrders) {
                        foreach (
                            $pickupOrders
                            as $pickupLocationId => $allOrders
                        ) {
                            // Create a new manifest
                            $manifestId = DB::table("manifests")->insertGetId([
                                "courier_id" => $courierId,
                                "company_id" => $companyId,
                                "payment_mode" => $paymentMode,
                                "pickup_location_id" => $pickupLocationId,
                                "created_at" => NOW(),
                                "updated_at" => NOW(),
                            ]);

                            $createdManifestIds[] = $manifestId;

                            // Prepare data for batch insert into `manifest_order`
                            $insertManifestOrder = [];
                            $allOrdersChunks = array_chunk($allOrders, 250);

                            foreach ($allOrdersChunks as $chunk) {
                                foreach ($chunk as $order) {
                                    $insertManifestOrder[] = [
                                        "manifest_id" => $manifestId,
                                        "order_id" => $order["order_id"],
                                        "vendor_order_id" =>
                                            $order["vendor_order_id"],
                                        "tracking_number" =>
                                            $order["tracking_number"],
                                        "created_at" => NOW(),
                                        "updated_at" => NOW(),
                                    ];
                                    $orderIdsInManifest[] = $order["order_id"];
                                    Order::where(
                                        "id",
                                        $order["order_id"]
                                    )->update(["status_code" => "M"]);
                                }
                            }

                            if (!empty($insertManifestOrder)) {
                                DB::table("manifest_orders")->insert(
                                    $insertManifestOrder
                                );
                            }
                        }
                    }
                }
            }

            // Update the `shipment_info` table for orders that have been included in a manifest
            if (!empty($orderIdsInManifest)) {
                DB::table("shipment_info")
                    ->whereIn("order_id", $orderIdsInManifest)
                    ->update(["manifest_created" => 1]);
            }

            DB::commit();
            if($shopifyOrders){
                foreach ($shopifyOrders as $channel_id => $shopifyOrdeedata) {
                    $channel_settings = ChannelSetting::WHERE(
                        "channel_id",
                        $channel_id
                    )->first();
                    $shopifyController = new ShopifyOrderSyncController();
                    foreach ($shopifyOrdeedata as $courier_id => $shopifyOrder) {
                        $courier_settings = CourierSetting::WHERE(
                            "courier_id",
                            $courier_id
                        )->first();
                        foreach ($shopifyOrder as $trackingInfo) {
                            $trackingInfo["courier_code"] =
                                $courier_settings->courier_code;
                                $shopifyController->fulfillOrder(
                                    $channel_settings,
                                    $trackingInfo
                                );
                        }
                    }
                }
            }           

            session()->flash(
                "success",
                "Manifest has been created successfully."
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Manifest creation failed: " . $e->getMessage());
            session()->flash(
                "error",
                "Manifest creation failed. Please try again."
            );
        }

        return;
    }
    public function pickup_create(Request $request)
    {
        $company_id = session("company_id");
        $manifest_id = $request->manifest_id ?? 0;
        $courier_id = $request->courier_id ?? 0;
        $pickup_location_id = $request->pickup_location_id ?? 0;
        if (
            empty($manifest_id) ||
            empty($courier_id) ||
            empty($pickup_location_id)
        ) {
            session()->flash(
                "error",
                "manifest_id, courier_id and pickup_location_id all are mandatory"
            );
            return;
        }
        $courier_settings = CourierSetting::where("company_id", $company_id)
            ->where("courier_id", $courier_id)
            ->where("status", 1)
            ->first();

        if (empty($courier_settings)) {
            session()->flash("error", "Courier is invalid");
            return;
        }
        $pickup_address = DB::table("pickup_locations")
            ->join(
                "companies",
                "pickup_locations.company_id",
                "=",
                "companies.id"
            )
            ->where("pickup_locations.company_id", $company_id)
            ->where("pickup_locations.id", $pickup_location_id)
            ->where("pickup_locations.status", 1)
            ->select("pickup_locations.*")
            ->first();

        if (empty($pickup_address)) {
            session()->flash("error", "pickup address is invalid");
            return;
        }
        $pickup_time = $pickup_address->pickup_time;
        $pickup_day = $pickup_address->pickup_day;

        $orderIds = ManifestOrder::where("manifest_id", $manifest_id)->pluck(
            "order_id"
        );

        $class = '\App\Http\Controllers\Seller\Couriers\Fulfillment\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $courier_settings->courier_code))) . 'CourierController';
        $controller = app()->make($class, [
            "order_ids" => $orderIds,
            "courier_id" => $courier_id,
            "company_id" => $company_id,
            "courier_settings" => $courier_settings,
        ]);
        $controller->pickup_address = $pickup_address;
        $controller->return_address = $pickup_address;
        $res = $controller->createPickup($manifest_id, $pickup_day);
        if (isset($res['error']) && !empty($res['error'])) {
            $errors =is_array($res['error'])?implode("<br>", $res['error']):$res['error']; 
            session()->flash('error', $errors);
        }
        if (isset($res["success"])) {
            session()->flash("success", $res["success"]);
        }
        return;
    }
    public function viewManifest(Request $request)
    {
        $manifestids = $request->query("manifest_ids") ?? "";
        $manifest_ids = explode(",", $manifestids);
        if (empty($manifest_ids) || !is_array($manifest_ids)) {
            abort(400, "Invalid or missing manifest IDs.");
        }
        $allManifestData = [];
        foreach ($manifest_ids as $manifestId) {
            $manifest = Manifest::with([
                "manifestOrders.order.orderProducts",
                "company",
                "courierSettings",
                "pickupLocation",
            ])->findOrFail($manifestId);

            $manifestData = [
                "id" => $manifest->id,
                "seller_name" => $manifest->company->legal_registered_name,
                "courier_name" =>
                    $manifest->courierSettings->courier_title ?? "N/A",
                "pickup_location_name" =>
                    $manifest->pickupLocation->location_title ?? "N/A",
                "payment_mode" => $manifest->payment_mode ?? "N/A",
                "company_logo" =>
                    $manifest->company->brand_logo ?? "default_logo.png",
                "orders" => $manifest->manifestOrders
                    ->map(function ($manifestOrder) {
                        $order = $manifestOrder->order;
                        // Generate Barcode for tracking number
                        $generator = new BarcodeGeneratorPNG();
                        $trackingNumber = $manifestOrder->tracking_number ?? "N/A";
                        
                        $tracking_id_barcode = $generator->getBarcode(
                            $trackingNumber,
                            $generator::TYPE_CODE_128,
                            2,
                            50
                        ); // You can change the type here
            
                        // Encode the barcode as a base64 image
                        $tracking_id_barcode = "data:image/png;base64," . base64_encode($tracking_id_barcode);
                        return [
                            "order_number" =>
                                $order->vendor_order_number ?? "N/A",
                            "amount" => $order->order_total,
                            "qty" => $order->orderProducts->sum("quantity"),
                            "products" => $order->orderProducts
                                ->map(function ($product) {
                                    return [
                                        "name" => $product->product_name,
                                        "sku" => $product->sku,
                                    ];
                                })
                                ->toArray(),
                            "tracking_number" => $trackingNumber,
                            "tracking_id_barcode" => $tracking_id_barcode,
                            "currency_symbol" => getCurrencySymbol(
                                $order->currency_code
                            ), // Add currency symbol
                        ];
                    })
                    ->toArray(),
            ];

            $allManifestData[] = $manifestData;
        }

        $pdf = Pdf::loadView("seller.orders.manifest", [
            "allManifestData" => $allManifestData,
        ]);
        return $pdf->download("manifest_{$manifestids}.pdf");
    }

    public function manifestDelete(Request $request)
    {
        $manifestId = $request->input("manifest_id");
        $sessionCompanyId = session("company_id");

        $manifest = DB::table("manifests")
            ->where("id", $manifestId)
            ->where("company_id", $sessionCompanyId)
            ->first();

        // If manifest does not exist
        if (!$manifest) {
            session()->flash("error", "Manifest does not exist.");
            return;
        }

        // Check if pickup has already been created
        if ($manifest->pickup_created !== 0) {
            session()->flash(
                "error",
                "Manifest cannot be deleted because pickup has been created."
            );
            return;
        }

        DB::beginTransaction();

        try {
            // Retrieve associated order IDs
            $orderIds = DB::table("manifest_orders")
                ->where("manifest_id", $manifestId)
                ->pluck("order_id");

            // Update shipment_info table
            DB::table("shipment_info")
                ->whereIn("order_id", $orderIds)
                ->update(["manifest_created" => 0]);

            // Delete from manifest_orders table
            DB::table("manifest_orders")
                ->where("manifest_id", $manifestId)
                ->delete();

            // Delete from manifests table
            DB::table("manifests")
                ->where("id", $manifestId)
                ->delete();

            // Update orders table status to 'P'
            DB::table("orders")
                ->whereIn("id", $orderIds)
                ->update(["status_code" => "P"]);

            DB::commit();
            session()->flash("success", "Manifest deleted successfully.");
        } catch (\Exception $e) {
            session()->flash(
                "error",
                "Failed to delete manifest. Please try again."
            );
        }

        // Redirect back after successful or failed operation
        return;
    }

    public function manifestOrderDelete(Request $request)
    {
        $manifestId = $request->input("manifest_id");
        $orderId = $request->input("order_id");
        $sessionCompanyId = session("company_id");

        DB::beginTransaction();

        try {
            // Delete the manifest order
            DB::table("manifest_orders")
                ->where("order_id", $orderId)
                ->delete();

            // Update order status to 'P'
            DB::table("orders")
                ->where("id", $orderId)
                ->where("company_id", $sessionCompanyId)
                ->update(["status_code" => "P"]);

            // Check if no manifest_orders reference the manifests.id
            $isOrderLinked = DB::table("manifest_orders")
                ->where("manifest_id", $manifestId)
                ->exists();

            // If not linked, delete the manifest
            if (!$isOrderLinked) {
                DB::table("manifests")
                    ->where("id", $manifestId)
                    ->delete();
            }

            // Also update the shipment_info table
            DB::table("shipment_info")
                ->where("order_id", $orderId)
                ->update(["manifest_created" => 0]);

            DB::commit();

            session()->flash("success", "Manifest deleted successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash(
                "error",
                "Failed to delete manifest. Please try again."
            );

            \Log::error("Error deleting manifest:", [
                "error" => $e->getMessage(),
            ]);
        }

        // Redirect back after successful or failed operation
        return;
    }
    public function getManifestOrders(Request $request){
        $company_id = session('company_id')??0;
        $manifest_id = $request->manifest_id??0;
        $manifestOrders = DB::table('orders as o')
        ->select(
            [
                'si.order_id',
                'si.current_status',
                'o.order_total',
                'o.currency_code',
                'o.vendor_order_number',
                'm.payment_mode',
                'm.id as manifest_id',
                'm.courier_id',
                'm.pickup_location_id',
                'm.payment_mode',
                'm.pickup_created',
                'mo.tracking_number',
                DB::raw('(SELECT status_name FROM order_statuses WHERE status_code = o.status_code) as status_name'),
                DB::raw('(SELECT name FROM shipment_statuses WHERE code = si.current_status) as shipment_status'),
            ]
        )
        ->join('shipment_info as si', 'si.order_id', '=', 'o.id')
        ->join('manifest_orders as mo', 'mo.order_id', '=', 'si.order_id')
        ->join('manifests as m', 'm.id', '=', 'mo.manifest_id')
        ->where(['m.id'=>$manifest_id,'o.company_id'=>$company_id])
        ->get();
        return $manifestOrders;
    }
}