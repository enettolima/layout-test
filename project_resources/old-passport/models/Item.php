<?php
require_once('config_doctrine.php');

class Item extends Doctrine_Record {
	public function setTableDefinition() {
		$this->setTableName('simpleinventory');
		
		$this->hasColumn('item_no as id', 'int', null, array('primary' => true));
		$this->hasColumn('alu', 'string', 32);
		$this->hasColumn('description', 'string', 256);
		$this->hasColumn('case_qty', 'int', null);
		$this->hasColumn('dcs_code', 'string', 32);
		$this->hasColumn('has_image', 'int', null);
		$this->hasColumn('is_available', 'int', null);
	}
	
	public function setUp() {
		$this->hasOne('Order', array('local' => 'order_id', 'foreign' => 'order_id'));
		$this->hasOne('Item', array('local' => 'item_no', 'foreign' => 'item_no'));
		$this->hasOne('DCS', array('local' => 'dcs_code', 'foreign' => 'dcs_code'));
	}
	
	public static function findBySearch($query) {
	    $index = Zend_Search_Lucene::open(APP_ROOT . '/data/lucene/test_index');
		Zend_Search_Lucene_Analysis_Analyzer::setDefault(
     		new Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive()); 
        
    	Zend_Search_Lucene::setResultSetLimit(100);
		$hits = $index->find($query);
		
		$ret = array();
		foreach ($hits as $hit) {
			//$doc = $hit->getDocument();	
	        $ret[] = $hit->item_no;
		}
		
		if (empty($ret))
			return false;
		else
			return Item::findByIds($ret);
	}
	
	public static function findByIds($ids) {
		$q = Doctrine_Query::create()
			->from('Item i')
			->innerJoin('i.DCS d')
			//->innerJoin('i.ItemPricing P')
			->where('i.id in (' . implode(', ', $ids) . ')');
			
		return $q->execute();
	}
	
	public static function findByDCS($dcs_code) {
		$q = Doctrine_Query::create()
			->from('Item i')
			->innerJoin('i.DCS d')
			//->innerJoin('i.ItemPricing P')
			->where('i.dcs_code = ? and is_available = 1', $dcs_code);
			
		return $q->execute();
	}
}