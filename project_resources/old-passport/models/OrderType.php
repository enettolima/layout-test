<?php
require_once('config_doctrine.php');

class OrderType extends Doctrine_Record {
	public function setTableDefinition() {
		$this->setTableName('ordertypes');
		
		$this->hasColumn('order_type as id', 'int', null, array('primary' => true));
		$this->hasColumn('type_name as name', 'string', 32);
	}
	
	public function setUp() {
		$this->hasMany('Order', array('local' => 'order_type', 'foreign' => 'order_type'));
	}
}