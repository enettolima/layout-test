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
    return view('pages.dashboard.index');
});

//Orders
Route::get('/requests', function () {
    return view('pages.procurement.order.requests', ['page_title' => 'Orders', 'page_breadcrumb' => 'Requests']);
});
Route::get('/order-details', function () {
    return view('pages.procurement.order.order-detail', ['page_title' => 'Order Details', 'page_breadcrumb' => 'Order Details']);
});

//Vendors
Route::get('/vendors', function () {
    return view('pages.procurement.vendor.vendor', ['page_title' => 'Vendors', 'page_breadcrumb' => 'Vendors']);
});

//Catalog
Route::get('/catalog', function () {
    return view('pages.procurement.catalog.catalog', ['page_title' => 'Catalog', 'page_breadcrumb' => 'Itmes List']);
});

//Tests
Route::get('/test404', function(){
   return abort(404);
});
