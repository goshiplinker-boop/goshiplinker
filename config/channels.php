<?php

return [
    'custom'        => \App\Http\Controllers\Seller\Channels\Settings\CustomController::class,
    'shopify'       => \App\Http\Controllers\Seller\Channels\Settings\ShopifyController::class,
    'woocommerce'   => \App\Http\Controllers\Seller\Channels\Settings\WoocommerceController::class,
    'shopbase'      => \App\Http\Controllers\Seller\Channels\Settings\ShopbaseController::class,   
    'opencart'      => \App\Http\Controllers\Seller\Channels\Settings\OpenCartController::class,
    'wix'           => \App\Http\Controllers\Seller\Channels\Settings\WixController::class,
];
