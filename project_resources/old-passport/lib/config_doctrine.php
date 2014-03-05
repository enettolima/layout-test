<?php
require_once('Doctrine.php');

// Configure the connection
Doctrine_Manager::connection(new PDO('oci:dbname=//flash/rproods', 'reportuser', 'report'), 'rproods');

// Configure loading behavior
Doctrine_Manager::getInstance()->setAttribute('model_loading', 'conservative');

/*
//if (LOG_SQL == true) {
	// Configure query logging
	require_once('SQLLog.php');
	Doctrine_Manager::getInstance()->getConnection('rproods')->addListener(new SQLLog);
//}
*/
Doctrine::loadModels(APP_ROOT . '/models'); // This call will not require the found .php files