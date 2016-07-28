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

Route::get('/', function () {
    //return view('welcome');
    return view('pages.login.login');
});

Route::get('/dashboard', function () {
    //return view('welcome');
    return view('pages.dashboard.index');
});

Route::get('/test404', function(){
   return abort(404);
});
