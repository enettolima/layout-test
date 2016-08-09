<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
//Login Page
Route::get('/', function () {
    //return view('welcome');
    return view('pages.login.login');
});
//Dashboard
Route::get('/dashboard', function () {
    return view('pages.dashboard.index',[
      'extra_js' => array(
        'js/passport/dashboard/dashboard.js'
      )
    ]);
});

//Orders
Route::get('/order/requests', function () {
    return view('pages.procurement.order.requests', ['order'=>'request', 'page_title' => 'Orders', 'page_breadcrumb' => 'Requests']);
});
Route::get('/order/approval', function () {
    return view('pages.procurement.order.requests', ['order'=>'approval','page_title' => 'Orders', 'page_breadcrumb' => 'Approval']);
});
Route::get('/order/importing', function () {
    return view('pages.procurement.order.requests', ['order'=>'importing','page_title' => 'Orders', 'page_breadcrumb' => 'Importing']);
});
Route::get('/order/payment', function () {
    return view('pages.procurement.order.requests', ['order'=>'payment','page_title' => 'Orders', 'page_breadcrumb' => 'Payment']);
});
Route::get('/order/receive', function () {
    return view('pages.procurement.order.requests', ['order'=>'receive', 'page_title' => 'Orders', 'page_breadcrumb' => 'Receive']);
});

Route::get('/order/details', function () {
    return view('pages.procurement.order.order-detail', ['page_title' => 'Order Details', 'page_breadcrumb' => 'Order Details']);
});
Route::get('/order/edit', function () {
    return view('pages.procurement.order.order-edit', ['page_title' => 'Order Edit', 'page_breadcrumb' => 'Edit Order']);
});

//Vendors
Route::get('/vendors', function () {
    return view('pages.procurement.vendor.vendor', ['page_title' => 'Vendors', 'page_breadcrumb' => 'Vendors']);
});

//Catalog
Route::get('/catalog', function () {
    return view('pages.procurement.catalog.catalog', ['page_title' => 'Catalog', 'page_breadcrumb' => 'Items List']);
});

Route::get('/catalog-details', function () {
    return view('pages.procurement.catalog.catalog-detail', [
      'page_title' => 'Catalog',
      'page_breadcrumb' => 'Items List',
      'extra_js' => array(
        'js/plugins/slick/slick.min.js',
        'js/passport/procurement/catalog.js'
      )
    ]);
});

//Tests
Route::get('/test404', function(){
   return abort(404);
});
