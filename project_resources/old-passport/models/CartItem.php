<?php
require_once('config_doctrine.php');

class CartItem extends Doctrine_Record {
	public function setTableDefinition() {
		$this->setTableName('cart_items');
		
		$this->hasColumn('cart_id', 'int', null, array('primary' => true));
		$this->hasColumn('item_no', 'int', null, array('primary' => true));
		$this->hasColumn('item_qty as qty', 'int', null);
	}
	
	public function setUp() {
		$this->hasOne('Cart', array('local' => 'cart_id', 'foreign' => 'id'));
		$this->hasOne('Item', array('local' => 'item_no', 'foreign' => 'item_no'));
	}
	
	public static function findAll() {
		
	}
}