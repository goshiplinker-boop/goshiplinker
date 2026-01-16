<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Import Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Seller\Auth\SellerAuthController;
use App\Http\Controllers\Seller\CourierController;
use App\Http\Controllers\Admin\PincodeMasterController;
use App\Http\Controllers\Seller\Couriers\Settings\CourierListController;
use App\Http\Controllers\Seller\Couriers\Fulfillment\AssignTrackingNumber;

use App\Http\Controllers\Seller\Channels\Settings\ChannelListController;

use App\Http\Controllers\Seller\Channels\ManageOrders\{
    OrderSyncController, ShopifyOrderSyncController, WoocommerceOrderSyncController,
    ManifestController
};

use App\Http\Controllers\Seller\{
    PickupLocationController, OrderController, CompanyController,
    SellerPayment\SubscriptionController, SellerPaymentController,
    Tracking\TrackShipmentController, Tracking\SettingsController,
    SellerPayment\RazorpayController, SellerPayment\ShopifyPaymentController,
    Notifications\NotificationTemplateController,
    Api\ApiController, Dashboard\DashboardController,
    Notifications\Sms\SmsController, Notifications\Sms\SmsGatewayController
};

use App\Models\Country;



use App\Http\Controllers\Seller\Auth\ImpersonationController;
use App\Http\Controllers\Seller\Couriers\ShippingRateCalculaterController;
use App\Http\Controllers\Admin\SellerRateCardController;
use App\Http\Controllers\Seller\WalletController;
/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/register', function () {
    return view('seller.auth.register', ['countries' => Country::all()]);
})->name('registerForm');

Route::post('/register', [SellerAuthController::class, 'signUp'])->name('register');
Route::get('/thankyou', [SellerAuthController::class, 'welcome'])->name('welcome_user');

Route::get('/seller/login', [SellerAuthController::class, 'showLoginForm'])->name('loginForm');
Route::post('/seller/login', [SellerAuthController::class, 'login'])->name('login');
Route::get('/seller/logout', [SellerAuthController::class, 'logout'])->name('logout');

Route::get('/seller/404', fn () => response()->view('errors.404', [], 404))->name('seller.custom.404');





/*
|--------------------------------------------------------------------------
| SELLER PROTECTED ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/seller/impersonate/{token}', [ImpersonationController::class, 'impersonate'])
    ->name('seller.impersonate')
    // ensure no auth or CSRF blocks it
    ->withoutMiddleware([
        'auth',
        'auth:web',
        'isSeller',
        \App\Http\Middleware\VerifyCsrfToken::class
    ]);
Route::group(['prefix' => 'seller', 'middleware' => ['isSeller']], function () {

    /*
    |--------------------------------------------------------------------------
    | Shopify Payments
    |--------------------------------------------------------------------------
    */
    Route::get('/shopify/payment/create', [ShopifyPaymentController::class, 'createApplicationCharge'])->name('shopify.payment.create');
    Route::get('/shopify/payment/callback', [ShopifyPaymentController::class, 'paymentCallback'])->name('shopify.payment.callback');


    /*
    |--------------------------------------------------------------------------
    | States API
    |--------------------------------------------------------------------------
    */
    Route::get('/states/{country_code}', [PickupLocationController::class, 'getStates'])->name('states');


    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::view('/home', 'seller.dashboard')->name('dashboard');



    /*
    |--------------------------------------------------------------------------
    | CHANNELS MANAGEMENT (HIGHLY OPTIMIZED)
    |--------------------------------------------------------------------------
    */

    Route::prefix('channels')->group(function () {

        Route::get('/', [ChannelListController::class, 'channelList'])->name('channels_list');

        Route::get('/ordersSync/{companyId?}/{syncType?}', [OrderSyncController::class, 'syncAllOrders'])
            ->name('syncOrders');

        // ALL CHANNEL ROUTES VIA CONFIG
        foreach (config('channels') as $slug => $controller) {
            Route::prefix($slug)->name("$slug.")->group(function () use ($controller) {
                Route::get('connect', [$controller, 'create'])->name('create');
                Route::post('store', [$controller, 'store'])->name('store');

                Route::get('{channel_id}/edit', [$controller, 'edit'])->name('edit');
                Route::put('{channel_id}/update', [$controller, 'update'])->name('update');
            });
        }

    });




   /*
    |--------------------------------------------------------------------------
    | COURIER EXTRA ROUTES
    |--------------------------------------------------------------------------
    */

    // Route::post('/couriers/uploadAWB', [CourierListController::class, 'uploadAWB'])->name('uploadAWB');
    // Route::get('/couriers/uploadAWB', [CourierListController::class, 'uploadAWB'])->name('courier.uploadAWB');

    // Route::post('/courier/delete', [CourierListController::class, 'deleteTrackingNumbers'])->name('courier.delete');
    // Route::post('/courier/export-csv', [CourierListController::class, 'exportTrackingNumbers'])->name('courier.export.csv');
    // Route::post('/courier/fetch_awb', [CourierListController::class, 'fetchTrackingNumbers'])->name('courier.fetch_awb');

    // Route::post('/couriers/pincode/upload', [CourierListController::class, 'importPincodes'])->name('import_pincode');
    // Route::get('/couriers/pincode/upload', [CourierListController::class, 'pincodeList'])->name('pincode_list');
    // Route::get('/couriers/pincode/export', [CourierListController::class, 'exportZipcodeNumbers'])->name('pincodeExport');




    /*
    |--------------------------------------------------------------------------
    | PICKUP LOCATIONS
    |--------------------------------------------------------------------------
    */

    Route::prefix('locations')->name('pickup_locations.')->group(function () {
        Route::get('/', [PickupLocationController::class, 'index'])->name('index');
        Route::get('/create', [PickupLocationController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [PickupLocationController::class, 'edit'])->name('edit');
        Route::post('/store', [PickupLocationController::class, 'store'])->name('store');
        Route::put('/{id}/update', [PickupLocationController::class, 'update'])->name('update');
    });




    /*
    |--------------------------------------------------------------------------
    | ORDERS ROUTES
    |--------------------------------------------------------------------------
    */

    Route::prefix('orders')->group(function () {

        Route::get('/shipping', [OrderController::class, 'shippingLabel'])->name('lableDownload');
        Route::get('/invoice', [OrderController::class, 'invoice'])->name('invoiceDownload');
        Route::get('/labelInvoice', [OrderController::class, 'combinedInvoiceLabel'])->name('downloadCombinedPdf');

        Route::get('/unfulfilled_orders', [OrderController::class, 'unfulfilled_orders'])->name('unfulfilled_orders');

        Route::post('/create-order', [OrderController::class, 'createOrder'])->name('createOrder');

        Route::post('/cancel', [OrderController::class, 'cancelOrders'])->name('cancelOrders');
        Route::post('/completed', [OrderController::class, 'completedorders'])->name('completedOrders');
        Route::post('/archive', [OrderController::class, 'archiveOrders'])->name('archiveOrders');
        Route::post('/shipped', [OrderController::class, 'shippedOrders'])->name('shippedOrders');
        Route::post('/onhold', [OrderController::class, 'onholdOrders'])->name('onholdOrders');

        Route::match(['get', 'post'], '/', [OrderController::class, 'index'])->name('order_list');

        Route::get('/create', [OrderController::class, 'create'])->name('order_create');
        Route::post('/add', [OrderController::class, 'addOrder'])->name('order_add');

        Route::get('/{order}/edit', [OrderController::class, 'editOrder'])->name('order_edit');
        Route::put('/{order}', [OrderController::class, 'updateOrder'])->name('orders.update');
        Route::get('/{id}/view', [OrderController::class, 'view'])->name('order_view');
        Route::post('/store', [OrderController::class, 'store'])->name('order_store');
        Route::patch('/update/{id}', [OrderController::class, 'update'])->name('order_update');
        Route::get('/invoice/buyer', [OrderController::class, 'buyer_invoice'])->name('order_invoice');
        Route::post('/invoice/buyer', [OrderController::class, 'store_buyer_invoice'])->name('store_order_invoice');
        Route::get('/label', [OrderController::class, 'label'])->name('order_label');
        Route::get('/shipping_label', [OrderController::class, 'buyer_shipping_label'])->name('label');
        Route::post('/buyer/shipping_label', [OrderController::class, 'store_buyer_shipping_label'])->name('shipping_label');
        Route::post('/clone', [OrderController::class, 'cloneOrder'])->name('order_clone');

        Route::post('/order_product_update', [OrderController::class, 'updateOrderProducts'])->name('order_product_update');
        Route::post('/deleteProduct', [OrderController::class, 'deleteOrderProduct'])->name('order_products_delete');

        Route::post('/update_package_details', [OrderController::class, 'updatePackage'])->name('update_package');

        Route::post('/mark_paid', [OrderController::class, 'markPaidOrder'])->name('mark_paid_Order');

        Route::get('/{order}/packages-json', [OrderController::class, 'getPackagesJson'])->name('orders.packages.json');
        Route::post('/{order}/packages', [OrderController::class, 'storePackages'])->name('orders.packages.store');

        Route::post('/filter', [OrderController::class, 'filter'])->name('orders.filter');
        Route::post('/export-orders', [OrderController::class, 'exportOrders'])->name('export.orders');

        Route::get('/add/bulk', [OrderController::class, 'addOrders'])->name('add_orders');
        Route::post('/import-csv', [OrderController::class, 'import'])->name('import.csv');

        Route::get('/track/{tracking_number}/{courier_id}', [AssignTrackingNumber::class, 'track'])->name('shipment_track');
        Route::post('/unassign', [AssignTrackingNumber::class, 'unassign'])->name('unassign_order');
        Route::post('/calculate_shipping', [AssignTrackingNumber::class, 'calculateShipping'])->name('calculate_shipping');

        Route::get('/download-error-csv/{filename}', [OrderController::class, 'downloadErrorCsv'])->name('download_error_csv');

    });




    /*
    |--------------------------------------------------------------------------
    | MANIFEST
    |--------------------------------------------------------------------------
    */

    Route::prefix('manifest')->group(function () {
        Route::post('/orders', [ManifestController::class, 'getManifestOrders'])->name('manifest_orders');
        Route::post('/create', [ManifestController::class, 'create'])->name('create_manifest');
        Route::post('/pickup_create', [ManifestController::class, 'pickup_create'])->name('pickup_create');
        Route::get('/view_manifest', [ManifestController::class, 'viewManifest'])->name('view_manifest');
        Route::post('/delete', [ManifestController::class, 'manifestDelete'])->name('manifest.delete');
        Route::post('/delete_manifest_order', [ManifestController::class, 'manifestOrderDelete'])->name('delete_manifest_order');
    });




    /*
    |--------------------------------------------------------------------------
    | COMPANY PROFILE
    |--------------------------------------------------------------------------
    */

    Route::get('company/edit', [CompanyController::class, 'edit'])->name('profile');
    Route::put('company/{company_id}/update', [CompanyController::class, 'update'])->name('companies.update');





    /*
    |--------------------------------------------------------------------------
    | NOTIFICATIONS
    |--------------------------------------------------------------------------
    */

    Route::prefix('notifications')->group(function () {

        Route::get('/', [NotificationTemplateController::class, 'index'])->name('seller_notification_list');
        Route::get('/{notification_id}/edit', [NotificationTemplateController::class, 'edit'])->name('seller_notification_edit');
        Route::put('/{id}/update', [NotificationTemplateController::class, 'update'])->name('seller_notifications_update');

        // --- SMS SETTINGS ---
        Route::prefix('sms')->group(function () {

            Route::get('/', [SmsController::class, 'index'])->name('sms_list');
            Route::post('/store', [SmsController::class, 'storeSetting'])->name('sms_store');

            Route::get('/templates', [SmsController::class, 'sms_templates'])->name('sms_templates');
            Route::get('/templates/create', [SmsController::class, 'createTemplate'])->name('sms_template_create');
            Route::get('/templates/edit', [SmsController::class, 'editTemplate'])->name('sms_template_edit');
            Route::post('/templates/store', [SmsController::class, 'storeTemplate'])->name('sms_template_store');
            Route::put('/templates/update', [SmsController::class, 'updateTemplate'])->name('sms_template_update');

            // SMS Gateways
            Route::prefix('gateways')->group(function () {
                Route::get('/', [SmsGatewayController::class, 'index'])->name('gateway_list');
                Route::get('/create', [SmsGatewayController::class, 'create'])->name('gateway_create');
                Route::get('/edit', [SmsGatewayController::class, 'edit'])->name('gateway_edit');
                Route::post('/store', [SmsGatewayController::class, 'store'])->name('gateway_store');
                Route::put('/update', [SmsGatewayController::class, 'update'])->name('gateway_update');
            });

            Route::post('/generatesms', [SmsController::class, 'Testsms'])->name('Test_sms');

        });

    });




    /*
    |--------------------------------------------------------------------------
    | TRACKING SETTINGS
    |--------------------------------------------------------------------------
    */

    Route::get('/tracking/settings/create', [SettingsController::class, 'create'])->name('tracking_create');
    Route::post('/manage/track', [SettingsController::class, 'store'])->name('tracking_store');




    /*
    |--------------------------------------------------------------------------
    | PAYMENTS
    |--------------------------------------------------------------------------
    */

    Route::get('/payment/seller/{id}', [SubscriptionController::class, 'generate'])->name('seller_invoice');
    Route::get('payment/history', [SubscriptionController::class, 'index'])->name('payment_history');
    Route::get('payment/subscription', [SellerPaymentController::class, 'index'])->name('subscription_plans');
    Route::get('/payment/trial', [SellerPaymentController::class, 'trial'])->name('trial_subscription');
    Route::get('/payment/free', [SellerPaymentController::class, 'free'])->name('free_subscription');

    Route::get('/payment', [RazorpayController::class, 'index'])->name('payment.index');
    Route::post('/payment/order', [RazorpayController::class, 'createOrder'])->name('payment.order');
    Route::post('/payment/callback', [RazorpayController::class, 'paymentCallback'])->name('payment.callback');
    Route::post('/payment/failed', [RazorpayController::class, 'paymentFailed']);




    /*
    |--------------------------------------------------------------------------
    | API CREDENTIALS
    |--------------------------------------------------------------------------
    */

    Route::get('/api_credentials', [ApiController::class, 'show'])->name('api.credentials.show');
    Route::post('/api_credentials/generate', [ApiController::class, 'generate'])->name('api.credentials.generate');




    /*
    |--------------------------------------------------------------------------
    | ANALYTICS DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get('/analytic', [DashboardController::class, 'index'])->name('analytic');

    Route::post('/shipOrders', [AssignTrackingNumber::class, 'assign'])->name('shiporders');
    Route::get('/orders/{id}/ship-modal', [ShippingRateCalculaterController::class, 'loadShipModal']);
    Route::post('/orders/{id}/assign-courier', [ShippingRateCalculaterController::class, 'assignCourier']);

});
Route::group(['prefix' => 'seller' ,'as' => 'seller.','middleware'=>['isSeller']],function(){
    Route::get('/couriers/pincode_master/export', [PincodeMasterController::class, 'masterPincodeExport'])->name('masterPincodeExport');
    Route::get('/shipping/rate_calculator', [ShippingRateCalculaterController::class, 'showRateCalculator'])->name('shipping.rate_calculator');
    Route::post('/shipping/rate_comparison', [ShippingRateCalculaterController::class, 'compareCouriers'])->name('shipping.rate_comparison');
    Route::prefix('couriers')->group(function () {
        
        Route::get('/manage_seller_couirer', [CourierListController::class, 'sellerCourierList'])
            ->name('couriers_list');
        Route::post('/manage_seller_couriers', [CourierListController::class, 'updateSellerCourierList'])
            ->name('seller_couriers_update');
        foreach (config('couriers') as $slug => $controller) {
            Route::prefix($slug)->name("$slug.")->group(function () use ($controller) {
                Route::get('connect', [$controller, 'create'])->name('create');
                Route::post('store', [$controller, 'store'])->name('store');
                Route::get('{courier_id}/edit', [$controller, 'edit'])->name('edit');
                Route::put('{courier_id}/update', [$controller, 'update'])->name('update');
            });
        }

    });
    
    Route::get('/seller_rate_card',[SellerRateCardController::class,'index'])->name('rate_card');
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
});


