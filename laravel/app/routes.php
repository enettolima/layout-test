<?php

Route::get('/', array('before'=>'auth', function()
{
    return Redirect::to('/home');
}));

Route::controller('/tools', 'ToolsController');

Route::controller('/reports', 'ReportsController');

Route::controller('/hb', 'HbController');

Route::controller('/home', 'HomeController');

Route::controller('/forms', 'FormsController'); 

Route::controller('/admin', 'AdminController'); 

Route::controller('/dev', 'DevController');

Route::controller('/lsvc', 'LSvcController');

Route::controller('/users', 'UsersController');

Route::controller('/scheduler', 'SchedulerController');

Route::controller('/preferences', 'PreferencesController');

Route::controller('/documents', 'DocumentsController');
