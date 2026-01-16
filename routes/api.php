<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Seller\Auth\SellerAuthController;
use App\Http\Controllers\Seller\OrderController;
use App\Http\Controllers\Seller\PickupLocationController;
use App\Http\Controllers\Seller\CustomerController;
use App\Http\Controllers\Seller\Couriers\Settings\CourierListController;
use App\Http\Controllers\Seller\Channels\Settings\ShopifyWebhookController;
use App\Http\Controllers\Seller\Channels\Settings\WoocommerceController;
use App\Http\Controllers\Seller\Channels\Settings\ChannelListController;
use App\Http\Controllers\Seller\CompanyController;
use App\Http\Controllers\Seller\Api\ApiController;
use App\Http\Controllers\Seller\Channels\Settings\WixWebhookController;
use App\Http\Controllers\Seller\Tracking\TrackShipmentController;
use App\Http\Controllers\Seller\Couriers\ShippingRateCalculaterController;
// Public Routes
Route::post('/api-token', [ApiController::class, 'issueToken']);
Route::post('signup', [SellerAuthController::class, 'signUp']);
Route::post('login', [SellerAuthController::class, 'login']);
Route::post('refreshToken', [SellerAuthController::class, 'refreshToken']);
Route::get('getcompany', [CompanyController::class, 'getCompany']);
Route::get('login', [SellerAuthController::class, 'login'])->name('apilogin');
// Routes requiring Sanctum authentication
Route::post('/webhooks/customer_data', [ShopifyWebhookController::class, 'customerData']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
   Route::post('/add/channel', [WoocommerceController::class, 'store'])->name('api.add_channel');
   Route::post('/updatechannel', [WoocommerceController::class, 'updateChannel']);
   Route::get('/getChannels', [ChannelListController::class, 'channelList'])->name('api.getchannels');
   Route::get('/orders', [OrderController::class, 'index']);
   Route::post('/customer/create', [CustomerController::class, 'createCustomer']);
   Route::get('/customer/getCustomer', [CustomerController::class, 'getCustomer']); 
   // new api
   Route::get('/get_orders', [OrderController::class, 'apiGetOrders']);
    Route::post('/orders/create', [OrderController::class, 'createOrderApi'])->name('api.orders.create');
    Route::post('/pickup_locations', [PickupLocationController::class, 'CreatePickupLocation']);
    Route::get('/get_locations', [PickupLocationController::class, 'index']);
    Route::get('/get_Channels', [ChannelListController::class, 'apiGetChannels'])->name('getchannels');
    Route::get('/get_couriers', [CourierListController::class, 'apiGetCouriers'])->name('GetCouriers');
  
});
Route::post('/calculate-shipping', [ShippingRateCalculaterController::class, 'calculate']);
Route::post('/shopify/webhooks/{topic}', [ShopifyWebhookController::class, 'handleWebhook'])->name('shopify.webhook');
Route::post('/wix/webhook', [WixWebhookController::class, 'handleWebhook'])->name('wix.webhook');
Route::post('/{website_domain}/trackingWidgetShipment', [TrackShipmentController::class, 'trackShipment'])->name('widget_shipment_history');


