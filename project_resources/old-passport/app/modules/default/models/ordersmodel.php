<?php
class OrdersModel extends OracleModel
{
    function __construct() {
        parent::__construct();
    }

    function getOrderStage($orderId) {
    	$getCurStageSql =
            "SELECT max_stage FROM orders WHERE order_id={$orderId}";
    	
    	$curStageRow = $this->getDB()->fetchRow($getCurStageSql);

    	return (isset($curStageRow) ? $curStageRow->MAX_STAGE : false);
    }
    
    // TODO: refactor these to 1 query
    function getOrderStore($orderId) {
    	$getOrderStoreSql = 
    		"SELECT store_code FROM orders WHERE order_id={$orderId}";
    	
    	$row = $this->getDB()->fetchRow($getOrderStoreSql);
    	return (isset($row) ? $row->STORE_CODE : false);
    }
    
    function createOrder($store, $stage) {
    	if (!ctype_digit((string)$stage)) {
    		throw (new Exception("$stage is not a valid number"));
    		return;
    	}
    	
    	$createOrderSql = 
    		"INSERT INTO orders(store_code, locked, max_stage) VALUES($store, 0, $stage)";
    	
    	$createStmt = new Zend_Db_Statement_Oracle($this->getDB(), $createOrderSql);
    	$createStmt->execute();
    }
    
    function addItems($orderId, $items) {
    	$orderStage = $this->getOrderStage($orderId);
    	$storeCode = $this->getOrderStore($orderId);
    	
    	$insertItemSql = 
    		"INSERT INTO orderItems
    			(order_id, order_stage, item_no, item_qty) 
    		VALUES 
    			({$orderId}, {$orderStage}, :itemNo, :itemQty)";
    			
    	$getItemAccessSql = 
    		"SELECT 
    			1 order_blocked
    		FROM 
    			storeItemAccess 
    		WHERE 
    			store_code = {$storeCode} AND 
    			item_no = :itemNo AND
    			can_order = 'N'";
 	
    	$insertStmt = new Zend_Db_Statement_Oracle($this->getDB(), $insertItemSql);
        $accessStmt = new Zend_Db_Statement_Oracle($this->getDB(), $getItemAccessSql);
        $accessStmt->setFetchMode(Zend_Db::FETCH_OBJ);
        
        $messages = array();
    	foreach ($items as $itemNo => $qty) {
    		$accessStmt->execute(array('itemNo' => $itemNo));
    		$accessObj = $accessStmt->fetch();
    		
    		if ($accessObj && isset($accessObj->ORDER_BLOCKED)) {
    			$messages[] = "Item #{$itemNo} is not avaiable to Store #{$storeCode}";
    			continue;
    		}
    	
    		try {
	    		$insertStmt->execute(array(
	    			'itemNo' => $itemNo,
	    			'itemQty' => $qty));
    		} catch (Zend_Db_Statement_Oracle_Exception $e) {
    			$messages[] = "Item #{$itemNo} is already in order #{$orderId}";
    		}
    	}
    	
    	return $messages;
    }
            
    function deleteItems($orderId, $items) {
    	if (empty($items))
    		return false;
    	
    	$orderStage = $this->getOrderStage($orderId);
        $n = $this->getDB()->delete(
            'ORDERITEMS', 
            "order_id = $orderId AND order_stage = {$orderStage} AND item_no IN (" . implode(', ', $items) . ")");
                            
        return ($n > 0);
    }
            
    function updateItems($orderId, $items) {
    	$orderStage = $this->getOrderStage($orderId);
        
        $update_sql = "
        	UPDATE orderItems 
        		SET item_qty = :qty 
        	WHERE 
        		order_id = :orderId AND 
        		item_no = :itemNo AND 
        		order_stage = {$orderStage}";
        		
        $delete_sql = 
        	"DELETE FROM 
        		orderItems 
        	WHERE 
        		order_id = :orderId AND 
        		item_no = :itemNo AND 
        		order_stage = {$orderStage}";
                    
        $update_stmt = new Zend_Db_Statement_Oracle($this->getDB(), $update_sql);
        $delete_stmt = new Zend_Db_Statement_Oracle($this->getDB(), $delete_sql);
                    
        foreach($items as $item_no => $qty) {
            if ($qty > 0)
            	$update_stmt->execute(array('qty' => $qty, 'orderId' => $orderId, 'itemNo' => $item_no));
            else
            	$delete_stmt->execute(array('orderId' => $orderId, 'itemNo' => $item_no));
        }
                    
        return true;
    }
           
    function getItemsByPage($orderId, $page, $pageSize, $orderCol, $order) {
        $getItemsSql =
            "SELECT 
			  i.*,
			  oi.item_qty,
			  cp.price cost,
			  100 - round((cp.price / rp.price) * 100, 2) markdown,
			  rp.price retail_price,
			  rp.price * i.case_qty * oi.item_qty retail_total,
			  cp.price * i.case_qty * oi.item_qty cost_total,
			  oc.item_comment,
			  row_number() OVER (ORDER BY i.d_name, 1 - (cp.price / rp.price) DESC) rno
			FROM
			  orders o 
			  INNER JOIN orderItems oi ON (oi.order_id = o.order_id AND oi.order_stage = o.max_stage)
			  INNER JOIN stores s ON (o.store_code = s.store_code)
			  INNER JOIN storeTypes st ON (s.store_type = st.store_type) 
			  INNER JOIN simpleInventory i ON (i.item_no = oi.item_no) 
			  LEFT JOIN (SELECT item_sid, price, price_lvl FROM cms.invn_sbs_price WHERE sbs_no = 1) rp ON (i.item_sid = rp.item_sid AND st.retail_price_level = rp.price_lvl)
			  LEFT JOIN (SELECT item_sid, price, price_lvl FROM cms.invn_sbs_price WHERE sbs_no = 1) cp ON (i.item_sid = cp.item_sid AND st.cost_price_level = cp.price_lvl)
			  LEFT JOIN orderComments oc ON(o.order_id = oc.order_id AND i.item_no = oc.item_no)
			WHERE
			  o.order_id = :orderId
			ORDER BY RNO";

        // Page Size of 0 means all rows in a single page
        if ($pageSize > 0) {
            $start = $page * $pageSize;
            $stop = ($page + 1) * $pageSize;
                            
            $getItemsSql = "SELECT * FROM({$getItemsSql}) WHERE rno BETWEEN $start AND $stop";
        }
        
        $orderStmt = new Zend_Db_Statement_Oracle($this->getDB(), $getItemsSql);
        $orderStmt->setFetchMode(Zend_Db::FETCH_OBJ);
        $orderStmt->execute(array('orderId' => $orderId));
        
        $items = array();
       	while($row = $orderStmt->fetch()) {
            if (!array_key_exists($row->D_NAME, $items))
            	$items[$row->D_NAME] = array();
                                    
            $items[$row->D_NAME][] = $row;
        }
                    
        return $items;
    }
            
    function getOrder($orderId) {
        $getOrderSql = 
            "SELECT
			  o.order_id,
			  os.order_stage,
			  os.stage_name,
			  o.store_code,
			  o.locked,
			  s.store_name,
			  st.type_name,
			  st.store_type,
			  count(oip.item_no) line_item_count,
			  sum(oip.retail_price * oip.case_qty * oip.item_qty) retail_total,
			  sum(oip.cost * oip.case_qty * oip.item_qty) cost_total
			FROM 
			  orders o 
			  INNER JOIN orderStages os ON (o.max_stage = os.order_stage) 
			  INNER JOIN stores s ON (s.store_code = o.store_code) 
			  INNER JOIN storeTypes st ON (s.store_type = st.store_type) 
			  LEFT JOIN orderItemsPricing oip ON(oip.order_id = o.order_id AND oip.order_stage = o.max_stage)
			WHERE
			  o.ORDER_ID = :orderId
			GROUP BY
			  o.order_id,
			  os.order_stage,
			  os.stage_name,
			  o.store_code,
			  o.locked,
			  s.store_name,
			  st.type_name,
			  st.store_type";
						      
        $orderStmt = new Zend_Db_Statement_Oracle($this->getDB(), $getOrderSql);
        $orderStmt->execute(array('orderId' => $orderId));
        
        try {
        	return $orderStmt->fetchObject(); 
        } catch (Exeception $e) {
        	return false;
        }
    }
    
	function getAccess($userId, $orderId) {
		$getAccessSql = 
			"SELECT
			  decode(uoa.can_read, 'Y', 1, 0) can_read,
			  bitand(decode(uoa.can_write, 'Y', 1, 0),
			    bitor(bitand(o.locked, decode(u.edit_locked, 'Y', 1, 0)), decode(o.locked, 1, 0, 1))) can_write
			FROM
			  orders o LEFT JOIN
			  userOrderAccess uoa ON (o.order_id = uoa.order_id) LEFT JOIN
			  users u ON (uoa.user_id = u.user_id)
			WHERE
			  u.user_id = :userId AND o.order_id = :orderId";
		
		$accessStmt = new Zend_Db_Statement_Oracle($this->getDB(), $getAccessSql);
        $accessStmt->execute(array('orderId' => $orderId, 'userId' => $userId));
        $accessRow = (object)$accessStmt->fetch();
		
		return (object)array(
			'read' => ($accessRow->CAN_READ == 1),
			'write' => ($accessRow->CAN_WRITE == 1));
	}
	
    function getAccessibleOrders($userId, $order = "order_id desc") {
        $getOrdersSql = 
			"SELECT 
			  o.max_stage order_stage,
			  o.order_id,
			  o.locked, 
			  ot.type_name order_type,
			  s.store_name,
			  st.type_name store_type,
			  count(oip.item_no) item_count,
			  sum(oip.retail_price * oip.case_qty * oip.item_qty) retail_total,
			  sum(oip.cost * oip.case_qty * oip.item_qty) cost_total 
			FROM
			  orders o
			  INNER JOIN orderTypes ot ON (o.order_type = ot.order_type) 
			  INNER JOIN stores s ON (o.store_code = s.store_code) 
			  INNER JOIN storeTypes st ON (s.store_type = st.store_type)
			  INNER JOIN userOrderAccess uoa ON (o.order_id = uoa.order_id)
			  LEFT JOIN orderItemsPricing oip ON (o.order_id = oip.order_id AND o.max_stage = oip.order_stage)
			WHERE
			  uoa.user_id = :userId AND o.max_stage > 0 
			GROUP BY
			  o.max_stage,
			  o.order_id,
			  o.locked,
			  ot.type_name, 
			  s.store_name,
  			  st.type_name
  			ORDER BY {$order}";
        
        $getStagesSql = 
        	'SELECT order_stage, stage_name FROM orderStages WHERE order_stage > 0 ORDER BY order_stage ASC';
        
        $stagesStmt = new Zend_Db_Statement_Oracle($this->getDB(), $getStagesSql);
        $stagesStmt->setFetchMode(Zend_Db::FETCH_OBJ);
        $stagesStmt->execute();
			        
        $ordersStmt = new Zend_Db_Statement_Oracle($this->getDB(), $getOrdersSql);
        $ordersStmt->setFetchMode(Zend_Db::FETCH_OBJ);
        $ordersStmt->execute(array('userId' => $userId));
        
        $stages = array();
        $orders = array();
        while($row = $stagesStmt->fetch()) {
        	$stages[$row->ORDER_STAGE] = $row->STAGE_NAME;
        	$orders[$row->STAGE_NAME] = array();
        }
        
        while($row = $ordersStmt->fetch()) {
        	$stage = $stages[$row->ORDER_STAGE];
        	unset($row->ORDER_STAGE);
        	
        	if (isset($row->ORDER_ID))
        		$orders[$stage][] = $row;
        }

		return (empty($orders) ? false : $orders);
    }
    
    function getOrderStages($orderId) {
    	$orderStage = $this->getOrderStage($orderId);
    	
        $sql = 'SELECT oi.*';
        for($stage = 0; $stage <= $orderStage; $stage++)
        	$sql .= ", nvl(s{$stage}.ITEM_QTY, 0) STAGE_{$stage}_QTY";
         
        $sql .= " FROM (SELECT DISTINCT oi.ITEM_NO, si.ALU, si.D_NAME, si.DESCRIPTION, si.CASE_QTY FROM OrderItems oi LEFT JOIN SimpleInventory si ON(oi.ITEM_NO = si.ITEM_NO) WHERE ORDER_ID = $orderId) oi";
        
        for($stage = 0; $stage <= $orderStage; $stage++)
			$sql .= " LEFT JOIN (
					SELECT 
					  ITEM_NO, ITEM_QTY 
					FROM OrderItems WHERE ORDER_ID = $orderId AND ORDER_STAGE = $stage
				  ) s$stage ON (s{$stage}.ITEM_NO = oi.ITEM_NO)";
				
		$sql .= " ORDER BY D_NAME ASC, oi.item_no DESC";
		
        $rows = $this->getDB()->fetchAll($sql);
		$dcsRows = array();
        foreach ($rows as $row) {
            $stages = array();
            $prev = 0;
            for ($i = 0; $i <= $orderStage; $i++) {
                $stage = new stdClass();
               
                $stage->qty = $row->{"STAGE_{$i}_QTY"};
                $stage->diff = $stage->qty - $prev;
                $stage->change = ($stage->diff > 0 ? '+' : ($stage->diff < 0 ? '-' : '='));
                $stage->diff = abs($stage->diff);
               
                $stages[$i] = $stage;
                $prev = $stage->qty;
            }
           
            $row->stages = $stages;
			if (!array_key_exists($row->D_NAME, $dcsRows))
				$dcsRows[$row->D_NAME] = array();
				
			$dcsRows[$row->D_NAME][] = $row;
        }
       
		return $dcsRows;
    }
    
    function setLock($orderId, $locked) {
    	$locked = intval($locked);
        $lockOrderSql = "UPDATE orders SET locked = $locked WHERE order_Id = :orderId";
        
        try {
            $lockOrder = new Zend_Db_Statement_Oracle($this->getDB(), $lockOrderSql);
            $lockOrder->execute(array('orderId' => $orderId));
            
            return true;
        }catch (Exception $e) {
            return false;
        }
    }
    
    function getItemComment($orderId, $itemNo) {
    	$getCommentSql = 
    		"SELECT 
    			* 
    		FROM 
    			orderComments 
    		WHERE 
    			order_id = :orderId AND
    			item_no = :itemNo";
    	
    	$commentStmt = new Zend_Db_Statement_Oracle($this->getDB(), $getCommentSql);
        $commentStmt->execute(array('orderId' => $orderId, 'itemNo' => $itemNo));
        
        $comment = $commentStmt->fetchObject();
        return $comment;
    }
    
    function saveItemComment($orderId, $itemNo, $comment) {
    	$updateCommentSql = 
    		"UPDATE 
    			orderComments 
    		SET 
    			item_comment = :itemComment 
    		WHERE 
    			order_id = :orderId AND
    			item_no = :itemNo";
    	
    	$insertCommentSql = 
    		"INSERT INTO orderComments(order_id, item_no, item_comment) 
    		VALUES (:orderId, :itemNo, :itemComment)";
    	
        try {
	        $insertStmt = new Zend_Db_Statement_Oracle($this->getDB(), $insertCommentSql);
	        $insertStmt->execute(array(
		        'itemComment' => $comment, 
		        'orderId' => $orderId, 
		        'itemNo' => $itemNo));
        } catch (Exception $e) {
        	$updateStmt = new Zend_Db_Statement_Oracle($this->getDB(), $updateCommentSql);
	        $updateStmt->execute(array(
	        	'itemComment' => $comment, 
	        	'orderId' => $orderId, 
	        	'itemNo' => $itemNo));
        }
        
        return true;
    }
    
    function deleteItemComment($orderId, $itemNo) {
    	$deleteCommentSql =
    		"DELETE FROM 
    			orderComments 
    		WHERE 
    			order_id = :orderId AND
    			item_no = :itemNo";
		
    	$deleteStmt = new Zend_Db_Statement_Oracle($this->getDB(), $deleteCommentSql);
    	$numRows = $deleteStmt->execute(array(
        	'orderId' => $orderId, 
        	'itemNo' => $itemNo));
    	
    	return ($numRows == 1);
    }
}