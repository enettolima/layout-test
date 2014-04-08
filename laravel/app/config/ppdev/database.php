<?php

return array(

	'connections' => array(

		'mysql' => array(
			'driver'    => 'mysql',
			'host'      => 'localhost',
			'database'  => 'dev_passport',
			'username'  => 'dev_dbuser',
			'password'  => 'dev_dbpass',
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => '',
		),

		'sqlsrv' => array(
			'driver'   => 'sqlsrv',
			'host'     => 'superman',
			'database' => 'EBTGOOGLE',
			'username' => 'sa',
			'password' => 'report',
			'prefix'   => '',
		),
	),
);
