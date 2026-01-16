<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Http\Controllers\Seller\Channels\Settings\ShopifyController;
use App\Http\Controllers\Seller\Channels\Settings\WixController;
use App\Http\Controllers\Seller\Tracking\TrackShipmentController;
use App\Http\Controllers\Seller\Notifications\NotificationTemplateController;
use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Seller\Auth\ImpersonationController;
use Illuminate\Support\Facades\Cache;
require_once __DIR__.'/admin_routes.php';
require_once __DIR__.'/seller_routes.php';
// ********** Public Routes *********
Route::get('/', function (Request $request) {
    return redirect()->route('loginForm');
})->middleware('captureUtm')->name('welcome');
// Privacy Policy Route
Route::get('/privacy-policy', function () {
    return view('website.privacy_policy');
})->name('policy');

// Shopify Integration Routes
Route::get('/shopify/install', [ShopifyController::class, 'begin'])->name('shopify.install');
Route::get('/shopify/callback', [ShopifyController::class, 'callback'])->name('shopify.callback');

// Route Wix redirects to when a user starts installing the app
Route::get('/wix/install', [WixController::class, 'handleInstall'])->name('wix.install');
// Route Wix redirects to after user authorization (your Redirect URL)
Route::get('/wix/callback', [WixController::class, 'handleCallback'])->name('wix.callback');
Route::get('/wix/dashboard', [WixController::class, 'app'])->name('wix.app');//wix app redirection
// Shipment Tracking Routes
Route::get('/{website_domain}/track', [TrackShipmentController::class, 'index'])->name('track');
Route::get('/{website_domain}/tracking_widget', [TrackShipmentController::class, 'index'])->name('tracking_widget');
Route::post('/{website_domain}/trackShipment', [TrackShipmentController::class, 'trackShipment'])->name('shipment_history');
Route::get('/{website_domain}/track/{tracking_number}', [TrackShipmentController::class, 'trackingDetails'])->name('track_details');
Route::get('/{website_domain}/tracking_widget/{tracking_number}', [TrackShipmentController::class, 'trackingDetails'])->name('widget_track_details');

// Custom Error Pages
Route::fallback(function () {
    // If user is not authenticated, show 500 error page
    if (!Auth::guard('admin')->check() && !Auth::guard('web')->check()) {
        return response()->view('errors.500', [], 500); // Show 500 error page for unauthenticated users
    }
    if (request()->segment(1)=='admin' || (Auth::guard('admin')->check() && !Auth::guard('web')->check())) {
        return redirect()->route('admin.custom.404');
    }
    if (request()->segment(1)=='admin' || (Auth::guard('web')->check() && !Auth::guard('admin')->check())) {
        return redirect()->route('seller.custom.404');
    }
    return view('errors.500');
    
});
Route::get('/admin/login-as-vendor/{user_id}', 
    [AdminAuthController::class, 'loginAsVendor']
)->name('admin.loginAsVendor');




