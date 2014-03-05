<?php
	require_once('OracleModel.php');

	class UsersModel extends OracleModel
	{
		function __construct()
		{
			parent::__construct();
		}
		
		function getUserById($guid){
			try {
				return $this->getDB()->fetchRow('SELECT * FROM Users WHERE USER_ID = :0', $guid);
			} catch (Exception $e) {
				return false;
			}
		}
		
		function updateUser($guid, $user)
		{
			try
			{
				$this->getDB()->update('USERS', $user, "USER_ID = $guid");
				
				return true;
			}
			catch (Exception  $e)
			{
				return false;
			}
		}
		
		function createUser($user) {
			try {
				$this->getDB()->insert('USERS', $user);
				return $this->getDB()->lastInsertId('GUID');
			} catch (Exception $e) {
				return false;
			}
		}
		
		function getUsersByPage($page_no, $page_size)
		{
			
			return $this->getDB()->fetchAll(
				'SELECT * FROM (SELECT t1.*, ROWNUM row_num FROM (SELECT * FROM USERS) t1) t2 WHERE row_num BETWEEN :0 AND :1',
				array(($page_no - 1) * $page_size, $page_no * $page_size));
		}
		
		function getStoreAccess($userId) {
			$getStoreAccessSql = 
				"SELECT * FROM storeAccess WHERE user_id = :0";
		
			$rules = $this->getDB()->fetchAll($getStoreAccessSql, $userId);
			return (empty($rules) ? false : $rules);
		}
	}
?>