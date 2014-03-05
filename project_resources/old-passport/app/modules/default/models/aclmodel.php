<?php
/**
 * AclModel - Dynamically generated resources and actions from
 * Zend_Framework compatable controllers.
 *
 * 	This class provides methods to use available resources and
 * actions as they are added to code by populating the Acl using
 * reflection.  This is suplemented by several database tables
 * that allow users and user roles to have access to resources
 * and actions.
 * 
 * @package Passport
 * @author Jonathan McCaffrey
 * @version 0.1
 */
class AclModel extends OracleModel {
	private $resources;
	
	function __construct() {
		parent::__construct();
		
		$this->resources = unserialize(file_get_contents(APP_ROOT . '/app/etc/resources.dat'));
	}
	
	function getResources() {
		return array_keys($this->resources);
	}
	
	function getActions($resource) {
		return (isset($this->resources[$resource]) ? $this->resources[$resource] : false);
	}
	
	function getRoleTree()
	{
		$q = $this->getDB()->query(
			'SELECT ' .
			'  r1.ROLE_NAME CHILD, r2.ROLE_NAME PARENT ' .
			'FROM ' .
			'  Roles r1 LEFT JOIN ' .
			'  RoleParents rp ON (rp.CHILD_ID = r1.ROLE_ID) LEFT JOIN' .
			'  Roles r2 ON (rp.PARENT_ID = r2.ROLE_ID)');
			
		$roles = $q->fetchAll();
            
		$tree = array();
		AclModel::populateTree($tree, $roles);
		
		return $tree;
	}
	
	function getAccessRules()
	{
		$q = $this->getDB()->query(
			'SELECT 
			  ra.ALLOW,
			  r.ROLE_NAME,
			  ra.CONTROLLER,
			  ra.ACTION 
			FROM 
			  RoleAccess ra LEFT JOIN 
			  Roles r ON (ra.ROLE_ID = r.ROLE_ID)');
		
		$rules = array(
			'allow' => array(),
			'deny' => array()
		);
		
		while ($row = $q->fetch())
		{
			$target = &$rules[($row->ALLOW ? 'allow' : 'deny')];
			if (!array_key_exists($row->ROLE_NAME, $target))
				$target[$row->ROLE_NAME] = array();
				
			if (isset($row->CONTROLLER)) {
				if (!array_key_exists($row->CONTROLLER, $target[$row->ROLE_NAME]))
					$target[$row->ROLE_NAME][$row->CONTROLLER] = array();
					
				if (isset($row->ACTION))
					$target[$row->ROLE_NAME][$row->CONTROLLER][] = $row->ACTION;
			}
		}
		
		return $rules;
	}
	
	public function getRoles()
	{
		$q = $this->getDB()->query('SELECT ROLE_ID, ROLE_NAME FROM Roles');
		
		$roles = array();
		
		while ($row = $q->fetch())
			$roles[] = $row;
		
		return $roles;
	}
	
	public function getUserRoles($user_id)
	{
		$q = $this->getDB()->query('SELECT r.ROLE_ID, r.ROLE_NAME FROM UserRoles ur LEFT JOIN Roles r ON (ur.ROLE_ID = r.ROLE_ID) WHERE ur.USER_ID = :0', $user_id);
		
		$roles = array();
		
		while ($row = $q->fetch())
			array_push($roles, $row);
		
		return $roles;
	}
	
	public function deleteUserRoles($user_id)
	{
		try
		{
			$q = $this->getDB()->query('DELETE FROM UserRoles WHERE USER_ID = :0', $user_id);
		}
		catch(Exception $e)
		{
			return false;
		}
		
		return true;
	}
	
	public function addUserRoles($user_id, $role_ids)
	{
		
		try 
		{
			$q = $this->getDB()->prepare('INSERT INTO UserRoles(ROLE_ID, USER_ID) VALUES(:0, :1)');
			
			foreach ($role_ids as $role_id)
			{
				$q->bindValue(':0', $role_id);
				$q->bindValue(':1', $user_id);
				
				$q->execute();
			}
		}
		catch(Exception $e)
		{
			return false;
		}
		
		return true;
	}
	
	private static function populateTree(&$tree, &$roles, $parent = null)
	{
		
		foreach ($roles as $index => $role)
		{
			if ($role->PARENT == $parent)
			{
				if (!is_array($tree))
					$tree = array();
					
				$tree[$role->CHILD] = null;
				AclModel::populateTree($tree[$role->CHILD], $roles, $role->CHILD);
			}
		}
	}
}
?>