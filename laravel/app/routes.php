<?php

Route::get('/', array('before'=>'auth', function()
{
    return Redirect::to('/home');
}));

Route::get('/home', array('before'=>'auth', function()
{
    return View::make('pages.home.home');
}));

Route::controller('/admin', 'AdminController'); 

Route::controller('/dev', 'DevController');

Route::controller('/lsvc', 'LSvcController');

Route::controller('/users', 'UsersController');

Route::controller('/scheduler', 'SchedulerController');

Route::controller('/settings', 'SettingsController');
