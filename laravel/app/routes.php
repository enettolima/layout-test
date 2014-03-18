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

Route::get('roles', function()
{

    echo get_class(Role::where('name', '=', 'Store200')->firstOrFail());

    //Auth::login(User::find(1));

    // var_dump(Auth::user());

    // foreach (Auth::user()->roles as $role) {
    //     echo $role->name . "\n";
    // }

    /*
    var_dump(User::all()->toArray());
    var_dump(Role::all()->toArray());
    var_dump(Permission::all()->toArray());
     */

    /*
    foreach (User::all() as $user) {
        echo $user->roles;
        echo $user->permissions;
    }
    */

    // var_dump($user);

    // var_dump($user->roles->toArray());

    /*
    $User = new User;
    $User->email = 'chad.davis@gmail.com';
    $User->username = 'chuyzero';
    $User->fname = 'Chuy';
    $User->lname = 'Davis';
    $User->password = Hash::make('secret');
    $User->save();
     */

    /*
    $User = new User;
    $User->email = 'chad@earthboundtrading.com';
    $User->username = 'cdavis';
    $User->fname = 'Chad';
    $User->lname = 'Davis';
    $User->password = Hash::make('secret');
    $User->save();
    */



    /*
    echo "hi";
    $storeManager = new Role();
    $storeManager->name = 'Store Manager';
    $storeManager->save();
     */

});





Route::controller('/lsvc', 'LSvcController');


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
