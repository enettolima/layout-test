<?php

require ('empInfoHelper.class.php');

$e = new empInfoHelper();

require 'vendor/autoload.php';

$app = new \Slim\Slim();

$app->response->headers->set('Content-Type', 'application/json');

if ($_SERVER['HTTP_HOST'] == 'cdev.newpassport.com') {
	$db = new PDO('mysql:host=localhost;dbname=dbname;charset=utf8', 'dbuser', 'dbpass');
} elseif ($_SERVER['HTTP_HOST'] == 'ppdev.earthboundtrading.com') {
	$db = new PDO('mysql:host=localhost;dbname=dev_passport;charset=utf8', 'dev_dbuser', 'dev_dbpass');
} else {
	die ("need db specifics");
}

$logger = true;

$app->run();
