<?php
class UserErrorException extends Exception { }

class OracleModel
{
	private static $db = null;
	
	function __construct()
	{
		if (OracleModel::$db == null)
		{
			$appRoot = dirname(getcwd());
			$config = new Zend_Config_Ini("$appRoot/app/etc/oracle.ini", 'live');
			
			OracleModel::$db = Zend_Db::factory('Oracle', array(
				'username' => $config->database->user,
				'password' => $config->database->password,
				'dbname' => $config->database->tnsname));
				
			OracleModel::$db->setFetchMode(Zend_Db::FETCH_OBJ);
		}
	}
	
	function getDB()
	{
		return OracleModel::$db;
	}
};
?>