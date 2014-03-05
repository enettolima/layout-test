<?php
require_once('config_doctrine.php');

class DCS extends Doctrine_Record {
	public function setTableDefinition() {
		$this->setTableName('cms_dcs_v');
		
		$this->hasColumn('dcs_code as id', 'string', 9, array('primary' => true));
		$this->hasColumn('d_name as department', 'string', 20);
		$this->hasColumn('c_name as category', 'string', 20);
		$this->hasColumn('s_name as subcategory', 'string', 20);
	}
	
	public function setUp() {
		$this->hasOne('Item', array('local' => 'store_code', 'foreign' => 'store_code'));
	}
	
	public static function findTree() {
		$dcsList = Doctrine_Query::create()
			->from('DCS d')
			->orderby('department, category, subcategory')
			->execute()
			->toArray();
			
		$tree = array();
		foreach ($dcsList as $dcs) {
			$top = &$tree;
			foreach (array('department', 'category', 'subcategory', false) as $field) {
				if ($field == false || is_null($dcs[$field])) {
					$top = $dcs['id'];
					break;
				}
					
				if (!isset($top[$dcs[$field]]))
					$top[$dcs[$field]] = array();
				
				$top = &$top[$dcs[$field]];
			}
		}

		return $tree;
	}
}