<?php

Route::get('/', array('before'=>'auth', function()
{
    return Redirect::to('/home');
}));

Route::controller('/pims', 'PIMSController');

Route::controller('/tools', 'ToolsController');

Route::controller('/reports', 'ReportsController');

Route::controller('/hb', 'HbController');

Route::controller('/home', 'HomeController');

Route::controller('/forms', 'FormsController');

Route::controller('/admin', 'AdminController');

Route::controller('/dev', 'DevController');

Route::controller('/lsvc', 'LSvcController');

Route::controller('/sa', 'SaController');

Route::controller('/users', 'UsersController');

Route::controller('/leases', 'LeasesController');

Route::controller('/uploader', 'UploaderController');

Route::controller('/scheduler', 'SchedulerController');

Route::controller('/preferences', 'PreferencesController');

Route::controller('/documents', 'DocumentsController');

Route::controller('/restock', 'RestockController');

Route::get('/testapi', function () {
  $response = Artisan::call('api:test', array('--show'=> 'error'));
  echo "OK - ".$response;
});

Route::post('/testapi', function () {
  $response = Artisan::call('api:test', array('--show'=> 'all'));
  echo "OK - ".$response;
});
