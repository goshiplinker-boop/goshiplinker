<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;


// Home
if (!Breadcrumbs::exists('home')) {
    Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
        $trail->push('Home', route('dashboard')); // Update to your route
    });
}

// Orders
if (!Breadcrumbs::exists('order_list')) {
    Breadcrumbs::for('order_list', function (BreadcrumbTrail $trail) { // Home > Orders
        $trail->push('Orders', route('order_list'));
    });
}
if (!Breadcrumbs::exists('order_view')) {
    Breadcrumbs::for('order_view', function (BreadcrumbTrail $trail,$edit) {
        $trail->parent('order_list');
        $trail->push('View', route('order_view',$edit->id));
    });
}
//Orderedit
if (!Breadcrumbs::exists('order_edit')) {
    Breadcrumbs::for('order_edit', function (BreadcrumbTrail $trail,$edit) {
        $trail->parent('order_view',$edit);
        $trail->push('Edit', route('order_edit',$edit->id));
    });
}
//bulk orders
if (!Breadcrumbs::exists('add_orders')) {
    Breadcrumbs::for('add_orders', function (BreadcrumbTrail $trail) {
        $trail->parent('order_list');
        $trail->push(' Import Bulk Orders', route('add_orders'));
    });
}

// Vendors
if (!Breadcrumbs::exists('vendors')) {
    Breadcrumbs::for('vendors', function (BreadcrumbTrail $trail) { // Home > Vendors
        $trail->push('Vendors', route('vendors_list'));
    });
}
//company
if (!Breadcrumbs::exists('profile')) {
    Breadcrumbs::for('profile', function (BreadcrumbTrail $trail) { // Home > Vendors
        $trail->push('Company', route('profile'));
    });
}
//invoice
if (!Breadcrumbs::exists('order_invoice')) {
    Breadcrumbs::for('order_invoice', function (BreadcrumbTrail $trail) { // Home > Vendors
        $trail->push('Invoice', route('order_invoice'));
    });
}
//subscription
if (!Breadcrumbs::exists('subscription_plans')) {
    Breadcrumbs::for('subscription_plans', function (BreadcrumbTrail $trail) { // Home > Vendors
        $trail->push('Subscription', route('subscription_plans'));
    });
}
//tracking number
if (!Breadcrumbs::exists('courier.uploadAWB')) {
    Breadcrumbs::for('courier.uploadAWB', function (BreadcrumbTrail $trail) { // Home > Vendors
        $trail->push('Upload Tracking', route('courier.uploadAWB'));
    });
}
//pincode number
if (!Breadcrumbs::exists('pincode_list')) {
    Breadcrumbs::for('pincode_list', function (BreadcrumbTrail $trail) { // Home > Vendors
        $trail->push('Upload Zipcodes', route('pincode_list'));
    });
}

// couriers 
if (!Breadcrumbs::exists('couriers_list')) {
    Breadcrumbs::for('couriers_list', function (BreadcrumbTrail $trail) {
        $trail->push('Couriers', route(panelPrefix().'.couriers_list'));
    });
}

if (!Breadcrumbs::exists('bluedart.create')) {
    Breadcrumbs::for('bluedart.create', function (BreadcrumbTrail $trail) {
        $trail->parent('couriers_list');
        $trail->push('Connect', route(panelPrefix().'.bluedart.create'));
    });
}

if (!Breadcrumbs::exists('bluedart.edit')) {
    Breadcrumbs::for('bluedart.edit', function ($trail, $bluedart) {
        $trail->parent('couriers_list');
        $trail->push('Edit '.$bluedart->courier_title, route(panelPrefix().'.bluedart.edit', $bluedart->id));
    });
}

if (!Breadcrumbs::exists('delhivery.create')) {
    Breadcrumbs::for('delhivery.create', function (BreadcrumbTrail $trail) {
        $trail->parent('couriers_list');
        $trail->push('Connect', route(panelPrefix().'.delhivery.create'));
    });
}

if (!Breadcrumbs::exists('delhivery.edit')) {
    Breadcrumbs::for('delhivery.edit', function ($trail, $delhivery) {
        $trail->parent('couriers_list');
        $trail->push('Edit '.$delhivery->courier_title, route(panelPrefix().'.delhivery.edit', $delhivery->id));
    });
}

if (!Breadcrumbs::exists('ekart.create')) {
    Breadcrumbs::for('ekart.create', function (BreadcrumbTrail $trail) {
        $trail->parent('couriers_list');
        $trail->push('Connect', route(panelPrefix().'.ekart.create'));
    });
}

if (!Breadcrumbs::exists('ekart.edit')) {
    Breadcrumbs::for('ekart.edit', function ($trail, $ekart) {
        $trail->parent('couriers_list');
        $trail->push('Edit '.$ekart->courier_title, route(panelPrefix().'.ekart.edit', $ekart->id));
    });
}

if (!Breadcrumbs::exists('selfship.create')) {
    Breadcrumbs::for('selfship.create', function (BreadcrumbTrail $trail) {
        $trail->parent('couriers_list');
        $trail->push('Connect', route(panelPrefix().'.selfship.create'));
    });
}

if (!Breadcrumbs::exists('selfship.edit')) {
    Breadcrumbs::for('selfship.edit', function ($trail, $selfship) {
        $trail->parent('couriers_list');
        $trail->push('Edit', route(panelPrefix().'.selfship.edit', $selfship->id));
    });
}

//channels settings
if (!Breadcrumbs::exists('channels_list')) {
    Breadcrumbs::for('channels_list', function (BreadcrumbTrail $trail) {
        $trail->push('Channels', route('channels_list'));
    });
}
if (!Breadcrumbs::exists('custom.create')) {
    Breadcrumbs::for('custom.create', function (BreadcrumbTrail $trail) {
        $trail->parent('channels_list');
        $trail->push('Connect', route('custom.create'));
    });
}
if (!Breadcrumbs::exists('custom.edit')) {
    Breadcrumbs::for('custom.edit', function ($trail, $custom) {
        $trail->parent('channels_list');
        $trail->push('Edit ' . $custom->channel_title, route('custom.edit', $custom->id));
    });
}
if (!Breadcrumbs::exists('woocommerce.create')) {
    Breadcrumbs::for('woocommerce.create', function (BreadcrumbTrail $trail) {
        $trail->parent('channels_list');
        $trail->push('Connect', route('woocommerce.create'));
    });
}
if (!Breadcrumbs::exists('woocommerce.edit')) {
    Breadcrumbs::for('woocommerce.edit', function ($trail, $woocommerce) {
        $trail->parent('channels_list');
        $trail->push('Edit ' . $woocommerce->channel_title, route('woocommerce.edit', $woocommerce->id));
    });
}
if (!Breadcrumbs::exists('shopify.create')) {
    Breadcrumbs::for('shopify.create', function (BreadcrumbTrail $trail) {
        $trail->parent('channels_list');
        $trail->push('Connect', route('shopify.create'));
    });
}
if (!Breadcrumbs::exists('shopify.edit')) {
    Breadcrumbs::for('shopify.edit', function ($trail, $shopify) {
        $trail->parent('channels_list');
        $trail->push('Edit ' . $shopify->channel_title, route('shopify.edit', $shopify->id));
    });
}
if (!Breadcrumbs::exists('shopbase.create')) {
    Breadcrumbs::for('shopbase.create', function (BreadcrumbTrail $trail) {
        $trail->parent('channels_list');
        $trail->push('Connect', route('shopbase.create'));
    });
}
if (!Breadcrumbs::exists('shopbase.edit')) {
    Breadcrumbs::for('shopbase.edit', function ($trail, $shopbase) {
        $trail->parent('channels_list');
        $trail->push('Edit ' . $shopbase->channel_title, route('shopbase.edit', $shopbase->id));
    });
}
if (!Breadcrumbs::exists('pickup_locations.index')) {
    Breadcrumbs::for('pickup_locations.index', function (BreadcrumbTrail $trail) {
       $trail->push('Locations', route('pickup_locations.index'));
    });
}
if (!Breadcrumbs::exists('pickup_locations.create')) {
    Breadcrumbs::for('pickup_locations.create', function (BreadcrumbTrail $trail) {
        $trail->parent('pickup_locations.index');
        $trail->push('Create Locations', route('pickup_locations.create'));
    });
}
if (!Breadcrumbs::exists('pickup_locations.edit')) {
    Breadcrumbs::for('pickup_locations.edit', function ($trail, $pickup_locations) {
        $trail->parent('pickup_locations.index');
        $trail->push('Edit Location' . $pickup_locations->courier_title, route('pickup_locations.edit', $pickup_locations->id));
    });
}
// Admin template 
if (!Breadcrumbs::exists('seller_notification_list')) {
    Breadcrumbs::for('seller_notification_list', function (BreadcrumbTrail $trail) { // Home > Orders
        $trail->push('Notifications', route('seller_notification_list'));
    });
}
if (!Breadcrumbs::exists('seller_notification_edit')) {
    Breadcrumbs::for('seller_notification_edit', function ( $trail , $edit) {
        $trail->parent('seller_notification_list');
        $trail->push('Edit Notifications', route('seller_notification_edit',$edit->id));
    });
}
//admin template
if (!Breadcrumbs::exists('notification_list')) {
    Breadcrumbs::for('notification_list', function (BreadcrumbTrail $trail) { 
        $trail->push('Notifications', route('notification_list'));
    });
}
if (!Breadcrumbs::exists('notification_edit')) {
    Breadcrumbs::for('notification_edit', function ( $trail , $edit) {
        $trail->parent('notification_list');
        $trail->push('Edit Notifications', route('notification_edit',$edit->id));
    });
}
if (!Breadcrumbs::exists('api.credentials.show')) {
    Breadcrumbs::for('api.credentials.show', function (BreadcrumbTrail $trail) { 
        $trail->push('API', route('api.credentials.show'));
    });
}
//vendor list
if (!Breadcrumbs::exists('vendors_list')) {
    Breadcrumbs::for('vendors_list', function (BreadcrumbTrail $trail) { 
        $trail->push('Sellers', route('vendors_list'));
    });
}
if (!Breadcrumbs::exists('followup_activities.show')) {
    Breadcrumbs::for('followup_activities.show', function (BreadcrumbTrail $trail, $edit) { 
        $trail->parent('vendors_list');
        $trail->push('Follow-up Activities', route('followup_activities.show',$edit->id));
    });
}