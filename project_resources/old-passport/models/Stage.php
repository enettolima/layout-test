<?php
require_once('config_doctrine.php');

class Stage extends Doctrine_Record {
	public function setTableDefinition() {	
		$this->setTableName('orderstages');
	
		$this->hasColumn('order_stage as id', 'integer', null, array('primary' => true));
		$this->hasColumn('stage_name as name', 'string', 64);
	}
	
	public function setUp() {
		$this->hasMany('StageAccessRule', array('local' => 'order_stage', 'foreign' => 'order_stage'));
		$this->hasMany('Order', array('local' => 'order_stage', 'foreign' => 'max_stage'));
	}
	
	public function __toString() {
		return $this->name;
	}
	
	public static function findAll() {
		return Doctrine_Query::create()
			->from('Stage s')
			->orderBy('s.id asc')
			->execute();
	}
}