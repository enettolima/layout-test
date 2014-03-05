<?php
require_once('config_doctrine.php');

class OrderItem extends Doctrine_Record {
	public function setTableDefinition() {
		$this->setTableName('orderitems');
		
		$this->hasColumn('order_id', 'int', null, array('primary' => true));
		$this->hasColumn('item_no', 'int', null, array('primary' => true));
		$this->hasColumn('order_stage', 'int', null, array('primary' => true));
		$this->hasColumn('item_qty', 'int', null);
	}
	
	public function setUp() {
		$this->hasOne('Order', array('local' => 'order_id', 'foreign' => 'order_id'));
		$this->hasOne('Item', array('local' => 'item_no', 'foreign' => 'item_no'));
	}
}