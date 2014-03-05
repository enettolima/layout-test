<?php
require_once('config_doctrine.php');

class Cart extends Doctrine_Record {
	public function setTableDefinition() {
		$this->setTableName('carts');
		
		$this->hasColumn('id', 'int', null, array('primary' => true, 'sequence' => 'guid'));
		$this->hasColumn('user_id', 'int', null);
		$this->hasColumn('created_on', 'datetime', null);
	}
	
	public function setUp() {
		$this->hasMany('CartItem', array('local' => 'id', 'foreign' => 'cart_id'));
	}
	
	public static function findByUser(User $user) {
		$q = Doctrine_Query::create()
			->from('Cart c')
			->leftJoin('c.CartItem ci')
			->leftJoin('ci.Item i')
			->where('c.user_id = ?', array($user->id));
		
		$results = $q->execute();
		return ($results->count() == 0 ? false : $results[0]);
	}
}