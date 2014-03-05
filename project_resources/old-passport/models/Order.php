<?php
require_once('config_doctrine.php');

class Order extends Doctrine_Record {
	public function setTableDefinition() {
		$this->setTableName('orders');
		
		$this->hasColumn('order_id as id', 'int', null, array('primary' => true, 'sequence' => 'guid'));
		$this->hasColumn('store_code', 'string', 5);
		$this->hasColumn('locked', 'int', null);
		$this->hasColumn('slip_no', 'int', null);
		$this->hasColumn('max_stage', 'int', null);
		$this->hasColumn('order_type', 'int', null);
		$this->hasColumn('created_on', 'datetime', null);
	}
	
	public function setUp() {
		$this->hasMany('OrderItem', array('local' => 'order_id', 'foreign' => 'order_id'));
		$this->hasOne('Store', array('local' => 'store_code', 'foreign' => 'store_code'));
		$this->hasOne('OrderType', array('local' => 'order_type', 'foreign' => 'order_type'));
		$this->hasOne('Stage', array('local' => 'max_stage', 'foreign' => 'order_stage'));
		$this->hasMany('StoreAccessRule', array('local' => 'store_code', 'foreign' => 'store_code'));
	}
	
	public function canWrite(User $user) {
		return true;		
	}
	
	public static function findByWeek($week, $year) {
		$start = date('d-M-Y', strtotime(sprintf('%dW%02d0', $year, $week)));
		$end = date('d-M-Y', strtotime(sprintf('%dW%02d7', $year, $week)));
		
		return Doctrine_Query::create()
			->select('o.id, o.store_code, o.locked, o.max_stage, os.id, os.name, s.id, s.name, ot.id, ot.name, count(oi.item_no) item_count')
			->from('Order o')
			->innerJoin('o.Stage os')
			->innerJoin('o.OrderType ot')
			->innerJoin('o.Store s')
			->leftJoin('o.OrderItem oi')
			->where('o.created_on between ? and ?', array($start, $end))
			->groupby('o.id, o.store_code, o.locked, o.max_stage, os.id, os.name, s.id, s.name, ot.id, ot.name')
			->orderby('o.max_stage, o.id')
			->execute();
	}
	
	public static function countReadable(User $user) {	
		$q = Doctrine_Query::create()
			->from('Order o');
		
		$where = Order::generateExceptions($user, true);
		
		if ($where === false)
			return 0;

		if (!empty($where))
			$q->where(implode(' and ', $where));
		
		return $q->count();
	}
	
	public static function countWritable(User $user) {
		$q = Doctrine_Query::create()
			->from('Order o');
		
		$where = Order::generateExceptions($user, false);
		
		if ($where === false)
			return 0;

		if (!empty($where))
			$q->where(implode(' and ', $where));
		
		return $q->count();
	}
	
	public static function findReadable(User $user) {
		return Order::findReadableByWeek($user);
	}
	
	public static function findWritable(User $user) {
		return Order::findWritableByWeek($user);
	}
	
	public static function findMaxWeek() {
		$q = Doctrine_Query::create()
			->select('max(o.created_on) max_date')
			->from('Order o')
			->execute();
		
		$date = $q[0]->max_date;
		list($week, $month, $year) = explode(' ', date('W m Y', strtotime($date)));
		
		if ($month == 12 && $week == 1)
			$year++;
			
		return array($week, $year);
	}
	
	public static function findReadableByWeek(User $user, $week = null, $year = null) {	
		$q = Doctrine_Query::create()
			->select('o.id, o.store_code, o.locked, o.max_stage, o.created_on, os.id, os.name, s.id, s.name, ot.id, ot.name, count(oi.item_no) item_count')
			->from('Order o')
			->innerJoin('o.Stage os')
			->innerJoin('o.OrderType ot')
			->innerJoin('o.Store s')
			->leftJoin('o.OrderItem oi')
			->groupby('o.id, o.store_code, o.locked, o.max_stage, o.created_on, os.id, os.name, s.id, s.name, ot.id, ot.name')
			->orderby('o.created_on desc, o.max_stage, o.id');
		
		$where = Order::generateExceptions($user, true);
		
		if ($where === false)
			return array();
		
		if (isset($week) && isset($year)) {
			$start = date('d-M-Y', strtotime(sprintf('%d-W%02d-1', $year, $week)));
			$end = date('d-M-Y', strtotime(sprintf('%d-W%02d-7', $year, $week)));
			
			$where[] = "trunc(o.created_on) >= '{$start}' and trunc(o.created_on) <= '{$end}'";
		}

		if (!empty($where))
			$q->where(implode(' and ', $where));
		
		return $q->execute();
	}
	
	public static function findWritableByWeek(User $user, $week = null, $year = null) {
		$q = Doctrine_Query::create()
			->select('o.id, o.store_code, o.locked, o.max_stage, o.created_on, os.id, os.name, s.id, s.name, ot.id, ot.name, count(oi.item_no) item_count')
			->from('Order o')
			->innerJoin('o.Stage os')
			->innerJoin('o.OrderType ot')
			->innerJoin('o.Store s')
			->leftJoin('o.OrderItem oi')
			->groupby('o.id, o.store_code, o.locked, o.max_stage, o.created_on, os.id, os.name, s.id, s.name, ot.id, ot.name')
			->orderby('o.created_on desc, o.max_stage, o.id');
		
		$where = Order::generateExceptions($user, false);
		
		if ($where === false)
			return array();
		
		if (isset($week) && isset($year)) {
			$start = date('d-M-Y', strtotime(sprintf('%d-W%02-1', $year, $week)));
			$end = date('d-M-Y', strtotime(sprintf('%d-W%02-7', $year, $week)));
			
			$where[] = "o.created_on >= '{$start}' and o.created_on <= '{$end}'";
		}

		if (!empty($where))
			$q->where(implode(' and ', $where));
		
		return $q->execute();
	}
	
	private static function generateExceptions(User $user, $readable) {
		$field_name = $readable ? 'can_read' : 'can_write';
		
		$include_stores = array();
		$exclude_stores = array();
		$all_stores = null;
		
		foreach ($user->StoreAccessRule as $rule) {
		if (is_null($rule->store_code)) {
				$all_stores = $rule->{$field_name} == 'Y';
				break;
			}
			
			if ($rule->{$field_name} == 'Y')
				$include_stores[] = $rule->store_code;
			else
				$exclude_stores[] = $rule->store_code;
		}
		
		$include_stages = array();
		$exclude_stages = array();
		$all_stages = null;
		
		foreach ($user->StageAccessRule as $rule) {
			if (is_null($rule->order_stage)) {
				$all_stages = $rule->{$field_name} == 'Y';
				break;
			}
			
			if ($rule->{$field_name} == 'Y')
				$include_stages[] = $rule->order_stage;
			else
				$exclude_stages[] = $rule->order_stage;
		}
		
		if ($all_stores === false || $all_stages === false) {
			return false;
		}
		
		$where = array();
		if ($all_stores !== true) {
			if (!empty($include_stores))
				$where[] = "o.store_code in (" . implode(', ', $include_stores) . ")";
			
			if (!empty($exclude_stores))
				$where[] = "o.store_code not in (" . implode(', ', $exclude_stores) . ")";
		}
		
		if ($all_stages !== true) {
			if (!empty($include_stages))
				$where[] = "o.max_stage in (" . implode(', ', $include_stages) . ")";
			
			if (!empty($exclude_stages))
				$where[] = "o.max_stage not in (" . implode(', ', $exclude_stages) . ")";
		}
			
		return $where;
	}
}