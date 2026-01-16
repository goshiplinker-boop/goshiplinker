<?php

return [    
    'delhivery' => \App\Http\Controllers\Seller\Couriers\Settings\DelhiveryController::class,
    'dtdc' => \App\Http\Controllers\Seller\Couriers\Settings\DtdcController::class,
    'dtdc_ltl' => \App\Http\Controllers\Seller\Couriers\Settings\DtdcLtlController::class,
    'bluedart' => \App\Http\Controllers\Seller\Couriers\Settings\BluedartController::class,
    'selfship' => \App\Http\Controllers\Seller\Couriers\Settings\SelfshipCourierController::class,
    'ekart' => \App\Http\Controllers\Seller\Couriers\Settings\EkartController::class,
    'xpressbees_postpaid' =>
        \App\Http\Controllers\Seller\Couriers\Settings\XpressbeesPostpaidController::class,

    'xpressbees_prepaid' =>
        \App\Http\Controllers\Seller\Couriers\Settings\XpressbeesPrepaidController::class,

    'shiprocket' =>
        \App\Http\Controllers\Seller\Couriers\Settings\ShiprocketController::class,

    'shipway' =>
        \App\Http\Controllers\Seller\Couriers\Settings\ShipwayController::class,

    'nimbus_post' =>
        \App\Http\Controllers\Seller\Couriers\Settings\NimbusPostController::class,

    'rapidshyp' =>
        \App\Http\Controllers\Seller\Couriers\Settings\RapidshypController::class,

    'shipshopy' =>
        \App\Http\Controllers\Seller\Couriers\Settings\ShipshopyController::class,
];
