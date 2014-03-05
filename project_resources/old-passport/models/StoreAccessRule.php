<?php
require_once('config_doctrine.php');

class StoreAccessRule extends Doctrine_Record {
	public function setTableDefinition() {	
		$this->setTableName('store_access_rules');
	
		$this->hasColumn('rule_id as id', 'integer', null, array('primary' => true, 'sequence' =>  'guid'));
		$this->hasColumn('user_id', 'integer', null);
		$this->hasColumn('store_code', 'string', 5);
		$this->hasColumn('can_read', 'string', 1);
		$this->hasColumn('can_write', 'string', 1);
	}
	
	public function setUp() {
		$this->hasOne('Store', array('local' => 'store_code', 'foreign' => 'store_code'));
		$this->hasOne('User', array('local' => 'user_id', 'foreign' => 'user_id'));
	}
	
	public static function deleteByUser($user_id) {
		$r = Doctrine_Query::create()
			->delete()
			->from('StoreAccessRule sar')
			->where('sar.user_id = ?', $user_id)
			->execute();
			
		return $r;
	}
}