<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', array('before'=>'auth', function()
{
    return Redirect::to('/home');
}));

Route::controller('/users', 'UsersController');

Route::controller('/scheduler', 'SchedulerController');

Route::get('/home', array('before'=>'auth', function()
{
    return View::make('pages.home.home');
}));
    
Route::get('/weborder', array('before'=>'auth', function()
{
    return View::make('pages.weborder.comingsoon');
}));

Route::get('/settings', array('before'=>'auth', function()
{
    return View::make('pages.settings.home');
}));

Route::get('/dbtest', function()
{
    $mysqlResults  = DB::connection('mysql')->select("select * from scheduled_inout limit 2");
    $sqlsrvResults = DB::connection('sqlsrv')->select("select top 2 * from SCHED_BUDGET_PER_HOURS_FINAL_TABLE WHERE Store = '311'");
    return View::make(
        'dbtest', 
        array(
            'mysqlResults' => $mysqlResults,
            'sqlsrvResults' => $sqlsrvResults,
        )
    );
});
