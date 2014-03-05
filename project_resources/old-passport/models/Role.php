<?php
require_once('config_doctrine.php');

class Role extends Doctrine_Record {
	public function setTableDefinition() {
		$this->setTableName('roles');
		
		$this->hasColumn('role_id as id', 'int', null, array('primary' => true, 'sequence' => 'guid'));
		$this->hasColumn('role_name as name', 'string', 32);
	}
	
	public function setUp() {
		$this->hasMany('User', array('local' => 'role_id', 'foreign' => 'role_id'));
		$this->hasMany('RoleAccessRule', array('local' => 'role_id', 'foreign' => 'role_id'));
	}
	
	public static function findAll() {
		return Doctrine_Query::create()
			->from('Role r')
			->orderBy('r.name asc')
			->execute();
	}
}