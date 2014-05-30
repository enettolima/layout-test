<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Clog
{
	public static function log ($thing)
	{
		Log::info(__METHOD__);

		$log = new Logger('clog');

		$log->pushHandler(new StreamHandler(storage_path().'/logs/clog.log'));//, Logger::INFO));

		if (Config::get('app.debug')) {

			// Spit it out to ChromePhp too
			ChromePhp::log($thing);

			switch (gettype($thing))
			{
				case 'string':
					$log->addInfo($thing);
					break;
				case 'array':
					$log->addInfo('array', $thing);
					break;
				case 'object':
					ob_start();
					print_r($thing);
					$string = ob_get_clean();
					$log->addInfo("object: $string");
					break;
			}
		}
	}
}
