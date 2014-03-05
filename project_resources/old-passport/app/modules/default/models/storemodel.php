<?php
class StoreModel extends OracleModel {
	function __construct() {
		parent::__construct();
	}
	
	public function getStores() {
		$q = $this->getDB()->query("SELECT store_code, store_name FROM stores");
			
		$stores = array();
		while ($row = $q->fetch())
			$stores[$row->STORE_CODE] = $row->STORE_NAME;
			
		return $stores;
	}
	
	public function getAvailableStores($user_id)
	{
		$stores = array();
		$q = $this->getDB()->query(
			"SELECT 
			  s.store_code, s.store_name 
			FROM 
			  stores s INNER JOIN 
			  adminStoreAccess sa ON(sa.store_code = s.store_code) 
			WHERE 
			  sa.user_id = " . intval($user_id));
			
		while ($row = $q->fetch()) {
			$stores[$row->STORE_CODE] = $row->STORE_NAME;
		}
		
		return $stores;
	}
}
?>