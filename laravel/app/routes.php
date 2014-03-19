<?php

Route::get('/', array('before'=>'auth', function()
{
    return Redirect::to('/home');
}));

Route::get('/home', array('before'=>'auth', function()
{
    return View::make('pages.home.home');
}));

Route::controller('/dev', 'DevController');

Route::controller('/lsvc', 'LSvcController');

Route::controller('/users', 'UsersController');

Route::controller('/scheduler', 'SchedulerController');

Route::controller('/settings', 'SettingsController');
    
/*
Route::get('/weborder', array('before'=>'auth', function()
{
    return View::make('pages.weborder.comingsoon');
}));
*/

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
