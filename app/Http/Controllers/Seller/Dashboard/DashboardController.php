<?php
namespace App\Http\Controllers\Seller\Dashboard;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\ShipmentInfo;
use App\Models\CourierSetting;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Customer;
use Carbon\Carbon;
class DashboardController extends Controller
    {
    public function index(Request $request)
    {   
        $companyId = session('company_id');
        $orderStats = $this->getOrderStats();
        $salesStats = $this->getSalesStats();
        $chartData = $this->getDailyChartData();
        $codData = $this->getPaymentModeData('COD');
        $prepaidData = $this->getPaymentModeData('Prepaid');
        $deliveredTotals = $this->getDeliveredTotals();
        [$orderCounts, $deliveredCounts] = $this->getMonthlyOrdersDeliveries();
        $months = ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'];
        $ordersGrouped = DB::table('orders')
        ->select('channel_id', 'status_code', DB::raw('COUNT(*) as total'))
        ->where('company_id', $companyId)
        ->groupBy('channel_id', 'status_code')
        ->get();
        $orderStatuses = DB::table('order_statuses')
        ->pluck('status_name', 'status_code')
        ->toArray();
        $channels = DB::table('channels')
        ->pluck('name', 'id')
        ->toArray();
        // Initialize all combinations with 0
        $groupedData = [];
        foreach ($ordersGrouped as $order) {
        $channel = $channels[$order->channel_id] ?? 'Unknown Channel';
        if (!isset($groupedData[$channel])) {
        $groupedData[$channel] = array_fill_keys(array_keys($orderStatuses), 0);
        }
        if (array_key_exists($order->status_code, $orderStatuses)) {
        $groupedData[$channel][$order->status_code] = $order->total;
        }
        }
        // Add total per channel
        foreach ($groupedData as &$statusCounts) {
        $statusCounts['total'] = array_sum($statusCounts);
        }
        //states overview
        $stateStatsRaw = DB::table('orders')
        ->select(
        'orders.s_state_code',
        'states.name as state_name',
        DB::raw("SUM(CASE WHEN orders.payment_mode = 'COD' THEN 1 ELSE 0 END) AS cod_count"),
        DB::raw("SUM(CASE WHEN orders.payment_mode != 'COD' THEN 1 ELSE 0 END) AS prepaid_count"),
        DB::raw("COUNT(*) as total_orders"),
        DB::raw("SUM(orders.order_total) as total_revenue")
        )
        ->leftJoin('states', 'orders.s_state_code', '=', 'states.state_code')
        ->whereNotNull('orders.s_state_code')
        ->groupBy('orders.s_state_code', 'states.name')
        ->orderByDesc('total_orders') // Order by total_orders in descending order
        ->get();
        $totalRevenue = $stateStatsRaw->sum('total_revenue');
        $stateStatsRaw = DB::table('orders')
        ->select(
        'orders.s_state_code',
        'states.name as state_name',
        DB::raw("SUM(CASE WHEN orders.payment_mode = 'COD' THEN 1 ELSE 0 END) AS cod_count"),
        DB::raw("SUM(CASE WHEN orders.payment_mode != 'COD' THEN 1 ELSE 0 END) AS prepaid_count"),
        DB::raw("COUNT(*) as total_orders"),
        DB::raw("SUM(orders.order_total) as total_revenue")
        )
        ->where('orders.company_id', $companyId) 
        ->leftJoin('states', 'orders.s_state_code', '=', 'states.state_code')
        ->whereNotNull('orders.s_state_code')
        ->groupBy('orders.s_state_code', 'states.name')
        ->orderByDesc('total_orders')
        ->get();
        
        $totalRevenue = $stateStatsRaw->sum('total_revenue');
        $stateStatsCollection = $stateStatsRaw->map(function ($row) use ($totalRevenue) {
        return [
        'state_name' => $row->state_name ?? $row->s_state_code,
        'cod' => (int) $row->cod_count,
        'prepaid' => (int) $row->prepaid_count,
        'total_orders' => (int) $row->total_orders,
        'revenue' => round($row->total_revenue, 2),
        'revenue_percent' => $totalRevenue > 0
        ? round(($row->total_revenue / $totalRevenue) * 100, 0)
        : 0
        ];
        });
        //top 10 customer
        $topCustomers = DB::table('orders')
        ->select(
        'fullname',
        DB::raw('COUNT(*) as order_count'),
        DB::raw('SUM(order_total) as total_revenue')
        )
        ->where('orders.company_id', $companyId)
        ->whereNotNull('fullname')
        ->groupBy('fullname')
        ->orderByDesc('order_count')
        ->limit(10)
        ->get()
        ->map(fn($row) => [
        'customer_name' => $row->fullname,
        'order_count' => (int) $row->order_count,
        'revenue' => round($row->total_revenue, 0), 
        ])
        ->toArray();
        //top 10 products
       $topProducts = DB::table('order_products')
    ->join('orders', 'order_products.order_id', '=', 'orders.id')
    ->select(
        'order_products.product_name',
        DB::raw('SUM(order_products.quantity) as units_sold'),
        DB::raw('SUM(order_products.total_price) as total_revenue')
    )
    ->where('orders.company_id', $companyId)
    ->groupBy('order_products.product_name')
    ->orderByDesc('units_sold')
    ->limit(10)
    ->get()
    ->map(fn($row) => [
        'product_name' => $row->product_name,
        'units_sold' => (int) $row->units_sold,
        'revenue' => round($row->total_revenue, 0),
    ])
    ->toArray();
        //states overview
        $statuses = [
        'PKP' => ' Pick up',
        'INT' => ' In transit',
        'UND' => ' Undelivered',
        'OFD' => ' Out for delivery',
        'RTO' => ' RTO',
        'DEL' => ' Delivered',
        'RTOD' => ' RTO Delivered',
        ];
        $Piechart = $this->getWeeklyShipmentStatusCounts($statuses);
        // Get the start and end of this month and last month
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        // Fetching data for the current month
        $thisMonth = DB::table('shipment_info')
        ->select(
        'courier_settings.courier_code',
        DB::raw("COUNT(shipment_info.id) as total_orders"),
        DB::raw("SUM(CASE WHEN shipment_info.current_status = 'DEL' THEN 1 ELSE 0 END) as delivered_orders"),
        DB::raw("ROUND((SUM(CASE WHEN shipment_info.current_status = 'DEL' THEN 1 ELSE 0 END) / COUNT(shipment_info.id)) * 100, 2) as delivery_percentage")
        )
        ->join('courier_settings', 'shipment_info.courier_id', '=', 'courier_settings.courier_id')
        ->where('shipment_info.company_id', '=', $companyId)
        ->whereBetween('shipment_info.created_at', [$thisMonthStart, $thisMonthEnd])
        ->groupBy('courier_settings.courier_code')
        ->get()
        ->map(function ($item) {
        return (array) $item;
        })
        ->toArray();
        // Fetching data for the last month
        $lastMonth = DB::table('shipment_info')
        ->select(
        'courier_settings.courier_code',
        DB::raw("COUNT(shipment_info.id) as total_orders"),
        DB::raw("SUM(CASE WHEN shipment_info.current_status = 'DEL' THEN 1 ELSE 0 END) as delivered_orders"),
        DB::raw("ROUND((SUM(CASE WHEN shipment_info.current_status = 'DEL' THEN 1 ELSE 0 END) / COUNT(shipment_info.id)) * 100, 2) as delivery_percentage")
        )
        ->join('courier_settings', 'shipment_info.courier_id', '=', 'courier_settings.courier_id')
        ->where('shipment_info.company_id', '=', $companyId)
        ->whereBetween('shipment_info.created_at', [$lastMonthStart, $lastMonthEnd])
        ->groupBy('courier_settings.courier_code')
        ->get()
        ->map(function ($item) {
        return (array) $item;
        })
        ->toArray();
        //dd ($thisMonth);
        return view('seller.dashboard.index', compact(
        'orderStats',
        'salesStats',
        'chartData',
        'codData',
        'prepaidData',
        'deliveredTotals',
        'orderCounts',
        'deliveredCounts',
        'months',
        'groupedData',
        'stateStatsCollection',
        'topCustomers',
        'topProducts',
        'Piechart',
        'thisMonth',
        'lastMonth'
        ));
    }
    private function getOrderStats()
    {   
        $companyId = session('company_id');
        $today = Carbon::today();
        $last30 = [$today->copy()->subDays(29), $today];
        $prev30 = [$today->copy()->subDays(59), $today->copy()->subDays(30)];
        $current = round(Order::where('company_id', $companyId)
        ->whereBetween('channel_order_date', $last30)
        ->count(),2);
        $previous = round(Order::where('company_id', $companyId)
        ->whereBetween('channel_order_date', $prev30)
        ->count(),2);
        $percent = $previous > 0 ? round((($current - $previous) / $previous) * 100, 2) : 0;
        return compact('current', 'previous', 'percent');
    }
    private function getSalesStats()
    {
        $companyId = session('company_id');
        $today = Carbon::today();
        $last30 = [$today->copy()->subDays(29), $today];
        $prev30 = [$today->copy()->subDays(59), $today->copy()->subDays(30)];
        $current = round(Order::where('company_id', $companyId)
        ->whereBetween('channel_order_date', $last30)
        ->sum('order_total'), 0);
        $previous = round(Order::where('company_id', $companyId)
        ->whereBetween('channel_order_date', $prev30)
        ->sum('order_total'), 0);
        $percent = $previous > 0
        ? round((($current - $previous) / $previous) * 100, 2)
        : 0;
        return compact('current', 'previous', 'percent');
    }
    private function getDailyChartData()
    {
        $companyId = session('company_id');
        $start = Carbon::today()->copy()->subDays(29);
        $end = Carbon::today();
        $orders = Order::where('company_id', $companyId)
        ->whereBetween('channel_order_date', [$start, $end])
        ->selectRaw('DATE(channel_order_date) as date, COUNT(*) as count')
        ->groupBy('date')->orderBy('date')->pluck('count', 'date');
        $sales = Order::where('company_id', $companyId)
        ->whereBetween('channel_order_date', [$start, $end])
        ->selectRaw('DATE(channel_order_date) as date, SUM(order_total) as total')
        ->groupBy('date')->orderBy('date')->pluck('total', 'date');
        $labels = [];
        $orderData = [];
        $salesData = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
        $key = $date->format('Y-m-d');
        $labels[] = $date->format('j M');
        $orderData[] = $orders->get($key, 0);
        $salesData[] = round($sales->get($key, 0), 2);
        }
        return compact('labels', 'orderData', 'salesData');
    }
    private function getPaymentModeData($mode)
    {
        $companyId = session('company_id');
        $today = Carbon::today();
        $last30 = [$today->copy()->subDays(29), $today];
        $prev30 = [$today->copy()->subDays(59), $today->copy()->subDays(30)];
        $current = round(Order::where('payment_mode', $mode)
        ->where('company_id', $companyId)
        ->whereBetween('channel_order_date', $last30)
        ->sum('order_total'), 0);
        $previous = round(Order::where('payment_mode', $mode)
        ->where('company_id', $companyId)
        ->whereBetween('channel_order_date', $prev30)
        ->sum('order_total'), 0);
        $percent = $previous > 0
        ? round((($current - $previous) / $previous) * 100, 2)
        : 0;
        $sales = Order::where('payment_mode', $mode)
        ->where('company_id', $companyId)
        ->whereBetween('channel_order_date', $last30)
        ->selectRaw('DATE(channel_order_date) as date, SUM(order_total) as total')
        ->groupBy('date')->orderBy('date')
        ->pluck('total', 'date');
        $labels = [];
        $chartData = [];
        for ($date = $last30[0]->copy(); $date->lte($last30[1]); $date->addDay()) {
        $key = $date->format('Y-m-d');
        $labels[] = $date->format('j M');
        $chartData[] = round($sales->get($key, 0), 0); // <-- No decimals here
        }
        return compact('current', 'previous', 'percent', 'labels', 'chartData');
    }
    private function getDeliveredTotals()
    {
        $companyId = session('company_id');
        $total = Order::join('shipment_info', 'orders.id', '=', 'shipment_info.order_id')
        ->where('orders.company_id', $companyId) 
        ->where('shipment_info.current_status', 'DEL')
        ->sum('orders.order_total');
        return round($total, 0); 
        }
        private function getMonthlyOrdersDeliveries()
        { 
        $companyId = session('company_id');
        $now = Carbon::now();
        $year = $now->month < 4 ? $now->year - 1 : $now->year;
        $start = Carbon::create($year, 4, 1);
        $end = Carbon::create($year + 1, 3, 31);
        $orders = DB::table('orders')
        ->select(DB::raw("MONTH(created_at) as month"), DB::raw("COUNT(*) as count"))
        ->where('company_id', $companyId)
        ->where('status_code', 'S')
        ->whereBetween('created_at', [$start, $end])
        ->groupBy(DB::raw("MONTH(created_at)"))
        ->pluck('count', 'month');
        $deliveries = DB::table('shipment_info')
        ->select(DB::raw("MONTH(created_at) as month"), DB::raw("COUNT(*) as count"))
        ->where('company_id', $companyId)
        ->where('current_status', 'DEL')
        ->whereBetween('created_at', [$start, $end])
        ->groupBy(DB::raw("MONTH(created_at)"))
        ->pluck('count', 'month');
        $orderCounts = collect([4,5,6,7,8,9,10,11,12,1,2,3])->map(fn($m) => $orders->get($m, 0));
        $deliveredCounts = collect([4,5,6,7,8,9,10,11,12,1,2,3])->map(fn($m) => $deliveries->get($m, 0));
        return [$orderCounts, $deliveredCounts];
    }
    private function getWeeklyShipmentStatusCounts(array $statuses)
    {
        $companyId = session('company_id'); 
        $thisWeekStart = Carbon::now()->startOfWeek();
        $thisWeekEnd = Carbon::now()->endOfWeek();
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();
        $thisWeekCounts = DB::table('shipment_info')
        ->select('current_status', DB::raw('count(*) as total'))
        ->whereIn('current_status', array_keys($statuses))
        ->where('company_id', '=', $companyId)
        ->whereBetween('created_at', [$thisWeekStart, $thisWeekEnd])
        ->groupBy('current_status')
        ->pluck('total', 'current_status')
        ->toArray();
        $lastWeekCounts = DB::table('shipment_info')
        ->select('current_status', DB::raw('count(*) as total'))
        ->whereIn('current_status', array_keys($statuses))
        ->where('company_id', '=', $companyId) 
        ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
        ->groupBy('current_status')
        ->pluck('total', 'current_status')
        ->toArray();

        $labels = [];
        $thisWeekData = [];
        $lastWeekData = [];
        foreach ($statuses as $code => $label) {
        $labels[] = $label;
        $thisWeekData[] = $thisWeekCounts[$code] ?? 0;
        $lastWeekData[] = $lastWeekCounts[$code] ?? 0;
        }
       
        return [
        'labels' => $labels,
        'thisWeekData' => $thisWeekData,
        'lastWeekData' => $lastWeekData,
        ];
    }
    }