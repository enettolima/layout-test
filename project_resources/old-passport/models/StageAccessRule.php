<?php
require_once('config_doctrine.php');

class StageAccessRule extends Doctrine_Record {
	public function setTableDefinition() {	
		$this->setTableName('stage_access_rules');
	
		$this->hasColumn('rule_id as id', 'integer', null, array('primary' => true, 'sequence' =>  'guid'));
		$this->hasColumn('user_id', 'integer', null);
		$this->hasColumn('order_stage', 'integer', null);
		$this->hasColumn('can_read', 'string', 1);
		$this->hasColumn('can_write', 'string', 1);
	}
	
	public function setUp() {
		$this->hasOne('Stage', array('local' => 'order_stage', 'foreign' => 'order_stage'));
		$this->hasOne('User', array('local' => 'user_id', 'foreign' => 'user_id'));
	}
	
	public static function deleteByUser($user_id) {
		$r = Doctrine_Query::create()
			->delete()
			->from('StageAccessRule sar')
			->where('sar.user_id = ?', $user_id)
			->execute();
			
		return $r;
	}
}