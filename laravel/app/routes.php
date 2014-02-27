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

Route::get('/', function()
{
	return View::make('hello');
    $results = DB::connection('sqlsrv')->select("select * from SCHED_BUDGET_PER_HOURS_FINAL_TABLE WHERE Store = '311'");
    var_dump($results);
});

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
