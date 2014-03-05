<?php
class AuthModel extends OracleModel {
	function __construct() {
		parent::__construct();
	}
	
	function getUserByLogin($username, $passhash) {		
		$loginSql =
			"SELECT
			  u.*, r.role_id, r.role_name
			FROM
			  users u
			  INNER JOIN userRoles ur ON(u.user_id = ur.user_id)
			  INNER JOIN roles r ON (ur.role_id = r.role_id)
			WHERE
			  u.username = :username AND u.passhash = :passhash";
		
		$loginStmt = new Zend_Db_Statement_Oracle($this->getDB(), $loginSql);
        $loginStmt->setFetchMode(Zend_Db::FETCH_OBJ);
        $loginStmt->execute(array('username' => $username, 'passhash' => $passhash));
        
		$rows = $loginStmt->fetchAll();
		if (!count($rows)) {
			file_put_contents(APP_ROOT . '/logs/login_fail.log', "{$username} could not login, the passhash sent was {$passhash}.\r\n", FILE_APPEND);
			return false;
		}
		
		$user = $rows[0];
		$user->ROLES = array();
		
		foreach ($rows as $row)
			$user->ROLES[$row->ROLE_ID] = $row->ROLE_NAME;	
			
		unset($user->ROLE_ID);
		unset($user->ROLE_NAME);
		
		return $user;
	}
}
?>