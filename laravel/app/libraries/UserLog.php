<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class UserLog
{

    /*
     * What I want:
     * Failed password for invalid user 'passed user' from IP
     * Failed password for user 'passed user' from IP
     * Successful login for user 'passed user' from IP
     */

    protected static $infoArray = array();

    public static function logSuccess($user)
    {
        self::$infoArray['user'] = $user;
        self::$infoArray['success'] = true;
        self::createLog();
    }

    public static function logFailure($user)
    {
        self::$infoArray['user'] = $user;
        self::$infoArray['success'] = false;
        self::$infoArray['reason'] = "Failed login";
        self::createLog();
    }

	protected static function createLog ()
	{
		$log = new Logger('UserLog');

		$log->pushHandler(new StreamHandler(storage_path().'/logs/userlog.log'));

        if (! $ip = getenv("REMOTE_ADDR")) {
            $ip = "(REMOTE_ADDR NOT SET)";
        }

        switch (self::$infoArray['success']) {
            case true:
                $message = "Successful login for user " . self::$infoArray['user'] . " from " . $ip;
                break;
            case false:
                $message = "Failed login for user " . self::$infoArray['user'] . " from " . $ip;
                break;
            default:
                $message = "Bad UserLog usage.";
                break;
        }

        $log->addInfo($message);
	}
}
