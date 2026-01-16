<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AdminAuthController;  
use App\Http\Controllers\Admin\FollowupActivitiesController;
use App\Http\Controllers\Admin\PincodeMasterController;
use App\Http\Controllers\Admin\SellerPincodeController;
use App\Http\Controllers\Seller\Couriers\Settings\CourierListController;
use App\Http\Controllers\Seller\Notifications\NotificationTemplateController;
use App\Http\Controllers\Admin\CourierRateCardController;
use App\Http\Controllers\Admin\SellerRateCardController;
use Illuminate\Http\Request;
use App\Http\Controllers\Seller\Couriers\ShippingRateCalculaterController;
use App\Http\Controllers\Admin\WeightDiscrepancyController;
use App\Http\Controllers\Admin\LogController;
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login');
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('adminForm');
Route::get('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
Route::get('admin/404', function () {
    return response()->view('errors.404', [], 404); // Custom 404 page
})->name('admin.custom.404');
// ********** Super Admin Routes *********
Route::group(['prefix' => 'admin','middleware'=>['isAdmin']],function(){
    Route::get('/manage_seller', [AdminAuthController::class, 'getUsers'])->name('vendors_list');
   // Route::get('/login_as_seller/{user_id}', [AdminAuthController::class, 'loginAsVendor'])->name('login_as_seller');
    Route::get('/followup_activities/show/{company_id}', [FollowupActivitiesController::class, 'show'])->name('followup_activities.show');
    Route::get('/user/{user_id}/lead-activity', [AdminAuthController::class, 'showLeadActivityForm'])->name('lead-activities.form');
    Route::post('/lead-activities', [AdminAuthController::class, 'storeLeadActivity'])->name('lead-activities.store');
    // Notifications
    Route::get('/notifications', [NotificationTemplateController::class, 'index'])->name('notification_list');
    Route::get('/notifications/create', [NotificationTemplateController::class, 'create'])->name('notification_create');
    Route::get('/notifications/{notification_id}/edit', [NotificationTemplateController::class, 'edit'])->name('notification_edit');
    Route::put('/notifications/{id}/update', [NotificationTemplateController::class, 'update'])->name('notifications_update');
    Route::get('/manage_pincode/import', [PincodeMasterController::class, 'index'])->name('pincode.import');
    Route::post('/manage_pincode/import', [PincodeMasterController::class, 'import'])->name('pincode.upload');
    
    // Upload form
    Route::get('manage_seller_pincode/import', [SellerPincodeController::class, 'importForm'])
        ->name('seller.pincodes.import.form');

    // Handle bulk import (UPSERT)
    Route::post('manage_seller_pincode/import', [SellerPincodeController::class, 'import'])
        ->name('seller.pincodes.import');
        
     // 1) IMPORT ROUTES FIRST
    Route::get('manage_rate_card/import', [CourierRateCardController::class, 'showImportForm'])
            ->name('manage_rate_card.import.form');

    Route::post('manage_rate_card/import', [CourierRateCardController::class, 'import'])
            ->name('manage_rate_card.import');

        // 2) RESOURCE ROUTE WITHOUT show
    Route::resource('manage_rate_card', CourierRateCardController::class)
            ->except(['show']); 

    //Seller rate card routes
    Route::get('manage_seller_rate_card/import', [SellerRateCardController::class, 'showImportForm'])
            ->name('manage_seller_rate_card.import.form');

    Route::post('manage_seller_rate_card/import', [SellerRateCardController::class, 'import'])
            ->name('manage_seller_rate_card.import');

    // 2) RESOURCE ROUTE WITHOUT show
    Route::resource('manage_seller_rate_card', SellerRateCardController::class)
            ->except(['show']); 
    // System Logs
    Route::get('system-logs', [LogController::class, 'index'])->name('system.logs');
    Route::get('system-logs/fetch', [LogController::class, 'fetch'])->name('system.logs.fetch');


   /*
    |--------------------------------------------------------------------------
    | COURIER EXTRA ROUTES
    |--------------------------------------------------------------------------
    */

    Route::post('/couriers/uploadAWB', [CourierListController::class, 'uploadAWB'])->name('uploadAWB');
    Route::get('/couriers/uploadAWB', [CourierListController::class, 'uploadAWB'])->name('courier.uploadAWB');

    Route::post('/courier/delete', [CourierListController::class, 'deleteTrackingNumbers'])->name('courier.delete');
    Route::post('/courier/export-csv', [CourierListController::class, 'exportTrackingNumbers'])->name('courier.export.csv');
    Route::post('/courier/fetch_awb', [CourierListController::class, 'fetchTrackingNumbers'])->name('courier.fetch_awb');

    Route::post('/couriers/pincode/upload', [CourierListController::class, 'importPincodes'])->name('import_pincode');
    Route::get('/couriers/pincode/upload', [CourierListController::class, 'pincodeList'])->name('pincode_list');
    Route::get('/couriers/pincode/export', [CourierListController::class, 'exportZipcodeNumbers'])->name('pincodeExport');
  
});
Route::group(['prefix' => 'admin' ,'as' => 'admin.','middleware'=>['isAdmin']],function(){
    Route::get('/weight-discrepancies', [WeightDiscrepancyController::class, 'index'])->name('weight-discrepancies.index');
     Route::get('weight-discrepancies/upload',[WeightDiscrepancyController::class, 'showUploadForm'])->name('weight-discrepancies.upload-form');
    Route::post('/weight-discrepancies/upload', [WeightDiscrepancyController::class, 'uploadCourierSheet'])->name('weight-discrepancies.upload');
    Route::get('/couriers/pincode_master/export', [PincodeMasterController::class, 'masterPincodeExport'])->name('masterPincodeExport');
    Route::get('/shipping/rate_calculator', [ShippingRateCalculaterController::class, 'showRateCalculator'])->name('shipping.rate_calculator');
    Route::post('/shipping/rate_comparison', [ShippingRateCalculaterController::class, 'compareCouriers'])->name('shipping.rate_comparison');
    Route::prefix('couriers')->group(function () {
        Route::get('/manage_courier', [CourierListController::class, 'courierList'])
            ->name('couriers_list');
        Route::get('/manage_seller_courier', [CourierListController::class, 'adminCourierList'])
            ->name('manage_seller_couriers');
        Route::post('/manage_admin_couriers', [CourierListController::class, 'updateAdminCourierList'])
            ->name('admin_couriers_update');

        foreach (config('couriers') as $slug => $controller) {
            Route::prefix($slug)->name("$slug.")->group(function () use ($controller) {
                Route::get('connect', [$controller, 'create'])->name('create');
                Route::post('store', [$controller, 'store'])->name('store');
                Route::get('{courier_id}/edit', [$controller, 'edit'])->name('edit');
                Route::put('{courier_id}/update', [$controller, 'update'])->name('update');
            });
        }

    });
});

