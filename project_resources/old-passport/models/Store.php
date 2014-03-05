<?php
require_once('config_doctrine.php');

class Store extends Doctrine_Record {
	public function setTableDefinition() {
		$this->setTableName('stores');
		
		$this->hasColumn('store_code as id', 'string', 5, array('primary' => true));
		$this->hasColumn('store_name as name', 'string', 32);
	}
	
	public function setUp() {
		$this->hasMany('StoreAccessRule', array('local' => 'store_code', 'foreign' => 'store_code'));
	}
	
	public function __toString() {
		return "{$this->code} - {$this->name}";
	}
	
	public static function findAll() {
		return Doctrine_Query::create()
			->from('Store s')
			->orderBy('s.id asc')
			->execute();
	}
}