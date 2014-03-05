<?php
require_once('OracleModel.php');

// TODO: Integrate search into this model
class ItemsModel extends OracleModel
{
	private $cache;
	
	function ItemsModel()
	{
		$frontendOptions = array(
		   'lifetime' => 7200, // cache lifetime of 2 hours
		   'automatic_serialization' => true
		);
	
		$backendOptions = array(
		    'cache_dir' => APP_ROOT . "/data/cache" // Directory where to put the cache files
		);
		
		// getting a Zend_Cache_Core object
		$this->cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
	}
	
	function getItemPricing($items, $userId) {
		$itemPricingSql = 
			"SELECT 
			  ip.item_no, ip.store_type, ut.type_name, ip.cost, ip.retail_price
			FROM
			  (SELECT
			    distinct s.store_type, 
			    st.type_name
			  FROM
			    adminStoreAccess sa LEFT JOIN
			    stores s ON (sa.store_code = s.store_code) LEFT JOIN 
			    storeTypes st ON (s.store_type = st.store_type)
			  WHERE
			    sa.user_id = {$userId}) ut INNER JOIN
			  itemPrices ip ON (ut.store_type = ip.store_type) 
			WHERE ";
		
		if (is_array($items) && count($items) > 0)
			$itemPricingSql .= 'ip.item_no IN (' . implode(', ', $items). ')';
		else if (intval($items) !== false)
			$itemPricingSql .= "ip.item_no = $items";
		else
			return false;
			  
		$pricingStmt = new Zend_Db_Statement_Oracle($this->getDB(), $itemPricingSql);
        $pricingStmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$pricingStmt->execute();
        
        $priceLevels = array();
        if (is_array($items)) {
	        while ($row = $pricingStmt->fetch()) {
	        	$itemNo = $row->ITEM_NO;
	        	unset($row->ITEM_NO);
	        	
	        	if (!isset($priceLevels[$itemNo]))
	        		$priceLevels[$itemNo] = array();
	        	
				$priceLevels[$itemNo][] = $row;
	        }
        } else {
        	while ($row = $pricingStmt->fetch()) {
        		unset($row->ITEM_NO);
				$priceLevels[] = $row;
        	}
        }
        
        return empty($priceLevels) ? false : $priceLevels;
	}
	
	function getItemInfo($item_no) {
		$getItemSql = "SELECT i.* FROM simpleInventory i WHERE i.item_no = :itemNo";
		
		$getItemStmt = new Zend_Db_Statement_Oracle($this->getDB(), $getItemSql);
		$getItemStmt->execute(array('itemNo' => $item_no));
	
		return $getItemStmt->fetchObject();
	}
	
	function getDCSTree() {	
		$getItemsSql = 
			"SELECT 
			  DISTINCT i.D_NAME, i.C_NAME, i.S_NAME
			FROM 
			  simpleInventory i INNER JOIN
			  webAvailableItems wi ON (i.item_no = wi.item_no)";
		
		$preprocess = $this->getDB()->fetchAll($getItemsSql);
		
		$tree = array();
		foreach ($preprocess as $row) {		
			if (!isset($tree[$row->D_NAME])) {
				$tree[$row->D_NAME] = array();
			}
			
			if (!isset($tree[$row->D_NAME][$row->C_NAME]))
				$tree[$row->D_NAME][$row->C_NAME] = array();
			
			if (isset($row->S_NAME)) {
				$tree[$row->D_NAME][$row->C_NAME][$row->S_NAME] = array();
			}
		}
		
		return $tree;
	}
    
    function getItemsByDCS($dname, $cname, $sname) {
        $dcsArray = array(
            'D_NAME' => $dname,
            'C_NAME' => $cname,
            'S_NAME' => $sname);
            
        $sql = 'SELECT * FROM webAvailableItems wi INNER JOIN simpleInventory si ON(si.item_no = wi.item_no) WHERE ';
            
        $whereTerms = array();
        foreach ($dcsArray as $field => $value) {
            if (!empty($value))
                $whereTerms[] = $this->getDB()->quoteInto("si.{$field}=?", $value);
        }

        $sql .= implode(' AND ', $whereTerms);
        $sql .= ' ORDER BY si.item_no ASC';
        
        $preprocess = $this->getDB()->fetchAll($sql);
        foreach ($preprocess as &$row) {
            $row->image = $row->HAS_IMAGE;
            unset($row->HAS_IMAGE);
        }
        
        return $preprocess;
    }
    
    function getDailyItems($count, $storeType) {
    	if ($results = $this->cache->load('daily_items_' . $storeType))
			return $results;
    	
		$getItemsSql =
			"SELECT * FROM (
			  SELECT
			    si.*,
			    ip.cost, ip.retail_price,
			    100 - round((ip.cost / ip.retail_price) * 100, 2) markdown,
			    row_number() OVER (ORDER BY dbms_random.value) rno
			  FROM 
			    webAvailableItems wi 
			    INNER JOIN simpleInventory si ON(si.item_no = wi.item_no)
			    INNER JOIN (SELECT * FROM itemPrices WHERE store_type = :storeType) ip ON(si.item_no = ip.item_no)
			  WHERE si.HAS_IMAGE = 1 AND (:storeType = 0 OR ip.cost IS NOT NULL) AND ip.retail_price IS NOT NULL) 
			WHERE rno <= :count ORDER BY markdown desc";
    	
    	$getItemsStmt = new Zend_Db_Statement_Oracle($this->getDB(), $getItemsSql);
        $getItemsStmt->setFetchMode(Zend_Db::FETCH_OBJ);
        $getItemsStmt->execute(array('count' => $count, 'storeType' => $storeType));
        
     	$items = array();
       	while($row = $getItemsStmt->fetch())
        	$items[] = $row;

    	if (!empty($items))
    		$this->cache->save($items, 'daily_items_' . $storeType);
    	else
    		return false;
    		
    	return $items;
    }
    
    function getItemsByBestMargin($count, $storeType) {
    	$getItemsSql =
	    	"SELECT * FROM (
			  SELECT
			    si.*,
			    ip.cost, ip.retail_price,
			    100 - round((ip.cost / ip.retail_price) * 100, 2) markdown,
			    row_number() OVER (ORDER BY 1 - (ip.cost / ip.retail_price) DESC) rno
			  FROM 
			    webAvailableItems wi 
			    INNER JOIN simpleInventory si ON(si.item_no = wi.item_no) 
			    INNER JOIN (SELECT * FROM itemPrices WHERE store_type = :storeType) ip ON(si.item_no = ip.item_no)
			  WHERE
			    (:storeType = 0 OR ip.cost IS NOT NULL) AND ip.retail_price IS NOT NULL
			) WHERE rno <= :count";
    	
    	$getItemsStmt = new Zend_Db_Statement_Oracle($this->getDB(), $getItemsSql);
        $getItemsStmt->setFetchMode(Zend_Db::FETCH_OBJ);
        $getItemsStmt->execute(array('count' => $count, 'storeType' => $storeType));
        
     	$items = array();
       	while($row = $getItemsStmt->fetch()) {
       		$items[] = $row;
       	}

    	return (empty($items) ? false : $items);
    }
}