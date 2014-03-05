<?php
/* Define Constants */
define('APP_ROOT', dirname(getcwd()));

/* Configure PHP Environment */
error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('US/Central');
ini_set('error_log', APP_ROOT . "/logs/php/error.log");
ini_set('session.save_path', APP_ROOT . "/data/sessions");

$includes = array(
	"c:/web/include",
	".", 
	APP_ROOT . "/models",
	APP_ROOT . "/app",
	APP_ROOT . "/lib",
	APP_ROOT . "/vendor"
);
	
$modulesDir = dir(APP_ROOT . '/app/modules');

while ($dir = $modulesDir->read()) {
	if ($dir[0] == '.') {
		continue;
	}
		
	$includes[] = APP_ROOT . "/app/modules/{$dir}";
	$includes[] = APP_ROOT . "/app/modules/{$dir}/models";
}

$includes[] = get_include_path();
set_include_path(implode(PATH_SEPARATOR, $includes));

/* Include Zend Framework */
require_once('Zend/Loader.php');
Zend_Loader::registerAutoload();

/* Initialize Layout, and Front Controller */
Zend_Layout::startMvc(
	array(
		'layout'     => 'default',
    'layoutPath' => APP_ROOT . '/app/templates'
	)
);

$fc = Zend_Controller_Front::getInstance()
	->throwExceptions(true)
	->registerPlugin(new Passport_Auth_Plugin())
	->registerPlugin(new SSLCheckPlugin)
	->registerPlugin(new MagicForm_Zend_WandPlugin);
	
$fc->addModuleDirectory(APP_ROOT . '/app/modules');

//try {
	/* Process the request */
	$fc->dispatch();
//} catch(Exception $e) {
//	header('Location: /static/error.html');
//}