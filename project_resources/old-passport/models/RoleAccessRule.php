<?php
require_once('config_doctrine.php');

class RoleAccessRule extends Doctrine_Record {
	public function setTableDefinition() {
		$this->setTableName('role_access_rules');
		
		$this->hasColumn('rule_id as id', 'int', null, array('primary' => true, 'sequence' => 'guid'));
		$this->hasColumn('role_id', 'int', null);
		$this->hasColumn('allow', 'string', 1);
		$this->hasColumn('module', 'int', 32);
		$this->hasColumn('controller', 'int', 32);
		$this->hasColumn('action', 'string', 32);	
	}
	
	public function setUp() {
		$this->hasOne('Role', array('local' => 'role_id', 'foreign' => 'role_id'));
	}
	
	public static function findByRole($role_id) {
		return Doctrine_Query::create()
			->from('RoleAccessRule rar')
			->where('rar.role_id = ?', $role_id)
			->orderBy('rar.id asc')
			->execute();
	}
}